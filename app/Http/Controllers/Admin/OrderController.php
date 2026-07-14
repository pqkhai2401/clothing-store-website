<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Exports\OrdersExport;
use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentMethod;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;

class OrderController extends Controller
{
    public const STATUS_LABELS = [
        'pending' => 'Chờ xác nhận',
        'processing' => 'Đang xử lý',
        'shipping' => 'Đang giao',
        'completed' => 'Hoàn thành',
        'cancelled' => 'Đã hủy',
    ];

    public const PAYMENT_STATUS_LABELS = [
        'unpaid' => 'Chưa thanh toán',
        'paid' => 'Đã thanh toán',
    ];

    public const STATUS_BADGE = [
        'pending' => 'text-bg-warning',
        'processing' => 'text-bg-primary',
        'shipping' => 'text-bg-info',
        'completed' => 'text-bg-success',
        'cancelled' => 'text-bg-secondary',
    ];

    /**
     * Các bước chuyển trạng thái hợp lệ theo vòng đời đơn hàng. Đơn đã "Hoàn thành" hoặc
     * "Đã hủy" là trạng thái cuối (terminal), không cho chuyển tiếp/nhảy cóc/lùi lại.
     */
    public const STATUS_TRANSITIONS = [
        'pending'    => ['processing', 'cancelled'],
        'processing' => ['shipping', 'cancelled'],
        'shipping'   => ['completed'],
        'completed'  => [],
        'cancelled'  => [],
    ];

    /**
     * Danh sách trạng thái được phép chọn cho một đơn đang ở trạng thái hiện tại
     * (luôn gồm chính trạng thái hiện tại — coi như lựa chọn "không đổi").
     * $blockedByPayment=true khi đơn dùng cổng thanh toán online (PayOS/MoMo) và chưa được
     * xác nhận thanh toán — lúc đó chỉ được giữ nguyên hoặc hủy, không được xử lý tiếp.
     */
    public static function allowedStatusOptions(string $currentStatus, bool $blockedByPayment = false): array
    {
        $allowedTransitions = self::STATUS_TRANSITIONS[$currentStatus] ?? [];

        if ($blockedByPayment) {
            $allowedTransitions = array_intersect($allowedTransitions, ['cancelled']);
        }

        $allowedKeys = array_merge([$currentStatus], $allowedTransitions);

        return array_intersect_key(self::STATUS_LABELS, array_flip($allowedKeys));
    }

    private static function isValidStatusTransition(string $from, string $to): bool
    {
        return $to === $from || in_array($to, self::STATUS_TRANSITIONS[$from] ?? [], true);
    }

    /**
     * Mô hình "thanh toán trước" cho đơn dùng cổng online (PayOS/MoMo): payment_status chỉ do
     * webhook/IPN xác nhận, nên phải paid rồi mới cho admin xử lý tiếp (processing trở đi).
     * Đơn COD/thủ công không bị ràng buộc (thu tiền khi giao). Giữ nguyên trạng thái hoặc hủy
     * thì luôn được phép, không phụ thuộc thanh toán.
     */
    private static function canAdvanceOnlineOrder(Order $order, string $to): bool
    {
        if ($to === $order->status || $to === 'cancelled') {
            return true;
        }

        $isOnlineGateway = $order->paymentMethod?->isOnlineGateway() ?? false;

        return !$isOnlineGateway || $order->payment_status === 'paid';
    }

    /**
     * Phạm vi được phép sửa NỘI DUNG đơn hàng (địa chỉ/SĐT/sản phẩm/phí ship/ghi chú) — khác với
     * việc đổi status/payment_status ở update(). Trả về:
     * - 'full': sửa được tất cả (địa chỉ, SĐT, sản phẩm, phí ship, ghi chú) — chỉ khi đơn còn
     *   "Chờ xác nhận" VÀ (COD/thủ công, hoặc online nhưng CHƯA từng tạo link thanh toán).
     * - 'limited': chỉ sửa được địa chỉ/SĐT/ghi chú — đơn "Đang xử lý" (đã trừ kho/xuất kho nên
     *   khóa sản phẩm+tiền), hoặc đơn online đã tạo link thanh toán (payos_payload/momo_payload
     *   đã "đóng băng" số tiền phía cổng, dù đơn vẫn đang "Chờ xác nhận").
     * - 'none': không sửa gì — đơn "Đang giao"/"Hoàn thành"/"Đã hủy".
     */
    public static function editableScope(Order $order): string
    {
        if (! in_array($order->status, ['pending', 'processing'], true)) {
            return 'none';
        }

        if ($order->status === 'processing') {
            return 'limited';
        }

        $hasOnlinePaymentLink = filled($order->payos_payload) || filled($order->momo_payload);

        return $hasOnlinePaymentLink ? 'limited' : 'full';
    }

    /**
     * Panel "Sửa đơn hàng" giờ gộp cả đổi trạng thái lẫn sửa nội dung — nên phải mở được bất cứ
     * khi nào có MỘT trong hai việc để làm: còn nội dung để sửa (editableScope !== 'none'), HOẶC
     * còn bước chuyển trạng thái hợp lệ (vd đơn "Đang giao" không sửa được gì nhưng vẫn cần
     * chuyển sang "Hoàn thành"). Chỉ đơn "Hoàn thành"/"Đã hủy" (cả 2 đều true) mới ẩn hẳn nút.
     */
    public static function canOpenEditPanel(Order $order): bool
    {
        return self::editableScope($order) !== 'none' || ! empty(self::STATUS_TRANSITIONS[$order->status] ?? []);
    }

    /**
     * Suy ra khoảng ngày + nhãn hiển thị cho thẻ thống kê từ date_from/date_to đang áp dụng.
     * Nếu không lọc gì cả, mặc định thống kê theo năm hiện tại. Nếu khoảng ngày khớp đúng
     * trọn 1 năm/quý/tháng (của bất kỳ năm nào) thì hiển thị nhãn tương ứng (vd "Năm 2025",
     * "Quý 2/2025", "Tháng 6/2025"), ngược lại là "khoảng đã chọn".
     */
    private function resolveStatsRange(string $dateFrom, string $dateTo): array
    {
        if ($dateFrom === '' && $dateTo === '') {
            $from = now()->startOfYear();
            $to   = now()->endOfYear();

            return [$from->toDateString(), $to->toDateString(), 'năm ' . $from->year];
        }

        try {
            $from = \Carbon\Carbon::parse($dateFrom)->startOfDay();
            $to   = \Carbon\Carbon::parse($dateTo)->startOfDay();
        } catch (\Throwable) {
            return [$dateFrom, $dateTo, 'khoảng đã chọn'];
        }

        if ($from->year === $to->year
            && $from->isSameDay($from->copy()->startOfYear())
            && $to->isSameDay($to->copy()->endOfYear())
        ) {
            return [$dateFrom, $dateTo, 'năm ' . $from->year];
        }

        if ($from->year === $to->year
            && $from->quarter === $to->quarter
            && $from->isSameDay($from->copy()->startOfQuarter())
            && $to->isSameDay($to->copy()->endOfQuarter())
        ) {
            return [$dateFrom, $dateTo, 'quý ' . $from->quarter . '/' . $from->year];
        }

        if ($from->year === $to->year
            && $from->month === $to->month
            && $from->isSameDay($from->copy()->startOfMonth())
            && $to->isSameDay($to->copy()->endOfMonth())
        ) {
            return [$dateFrom, $dateTo, 'tháng ' . $from->month . '/' . $from->year];
        }

        return [$dateFrom, $dateTo, 'khoảng đã chọn'];
    }

    private function buildStatsData(string $dateFrom, string $dateTo): array
    {
        [$statsFrom, $statsTo, $periodLabel] = $this->resolveStatsRange($dateFrom, $dateTo);

        $rangeQuery = fn () => Order::query()
            ->when($statsFrom !== '', fn ($q) => $q->whereDate('created_at', '>=', $statsFrom))
            ->when($statsTo !== '', fn ($q) => $q->whereDate('created_at', '<=', $statsTo));

        return [
            'periodLabel'     => $periodLabel,
            'periodOrders'    => $rangeQuery()->count(),
            'periodRevenue'   => $rangeQuery()
                ->where('status', '!=', 'cancelled')
                ->where(fn ($q) => $q->where('status', 'completed')
                                     ->orWhere('payment_status', 'paid'))
                ->sum('total_money'),
            'cancelledOrders' => $rangeQuery()->where('status', 'cancelled')->count(),
            'pendingOrders'   => Order::where('status', 'pending')->count(),
        ];
    }

    /**
     * Xây dựng query đơn hàng theo các bộ lọc dùng chung cho danh sách (index) và xuất Excel (export),
     * đảm bảo file xuất ra luôn khớp đúng bộ lọc đang áp dụng trên giao diện.
     */
    public function buildFilteredQuery(Request $request): Builder
    {
        $search        = trim((string) $request->input('search', $request->input('keyword')));
        $statusFilter  = $request->input('status', '');
        $paymentFilter = $request->input('payment_status', '');
        $dateFrom      = $request->input('date_from');
        $dateTo        = $request->input('date_to');

        // Ẩn đơn online (PayOS/MoMo) chưa thanh toán khỏi danh sách xử lý (và file xuất): đây là
        // đơn "đang chờ thanh toán", chưa chốt — admin không xử lý được (canAdvanceOnlineOrder chặn)
        // và sẽ tự supersede/hết hạn. Đơn online đã thanh toán / COD / chuyển khoản vẫn hiển thị.
        $query = Order::with(['user', 'paymentMethod'])->excludingUnpaidOnline();

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('order_code', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($u) => $u->where('username', 'like', "%{$search}%"));
            });
        }

        if ($statusFilter !== '') {
            $query->where('status', $statusFilter);
        }

        if ($paymentFilter !== '') {
            $query->where('payment_status', $paymentFilter);
        }

        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        return $query;
    }

    public function index(Request $request)
    {
        $keyword       = trim((string) $request->input('search', $request->input('keyword')));
        $statusFilter  = $request->input('status', '');
        $paymentFilter = $request->input('payment_status', '');
        $dateFrom      = $request->input('date_from', '');
        $dateTo        = $request->input('date_to', '');
        $sort      = $request->input('sort', 'created_at');
        $direction = in_array($request->input('direction'), ['asc', 'desc'], true)
            ? $request->input('direction') : 'desc';
        $perPage = in_array((int) $request->input('per_page'), [10, 25, 50], true)
            ? (int) $request->input('per_page')
            : 10;

        $query = $this->buildFilteredQuery($request);

        match ($sort) {
            'total'   => $query->orderBy('total_money', $direction),
            'fee'     => $query->orderBy('shipping_fee', $direction),
            'status'  => $query->orderBy('status', $direction),
            'payment' => $query->orderBy('payment_status', $direction),
            default   => $query->orderBy('created_at', $direction),
        };

        $orders = $query->paginate($perPage)->withQueryString();

        $tableData = [
            'orders'              => $orders,
            'statusLabels'        => self::STATUS_LABELS,
            'paymentStatusLabels' => self::PAYMENT_STATUS_LABELS,
            'statusBadge'         => self::STATUS_BADGE,
            'sort'                => $sort,
            'direction'           => $direction,
        ];

        $statsData = $this->buildStatsData($dateFrom, $dateTo);

        if ($request->ajax()) {
            return response()->json([
                'html'  => view('admin.orders.partials.table', $tableData)->render(),
                'stats' => view('admin.orders.partials.stats', $statsData)->render(),
            ]);
        }

        return view('admin.orders.index', array_merge($tableData, $statsData, [
            'keyword'       => $keyword,
            'statusFilter'  => $statusFilter,
            'paymentFilter' => $paymentFilter,
            'dateFrom'      => $dateFrom,
            'dateTo'        => $dateTo,
            'perPage'       => $perPage,
        ]));
    }

    public function export(Request $request)
    {
        return Excel::download(new OrdersExport($request), 'don-hang-' . now()->format('Ymd-His') . '.xlsx');
    }

    public function bulkUpdateStatus(Request $request)
    {
        $validated = $request->validate([
            'ids'    => ['required', 'array', 'min:1'],
            'ids.*'  => ['integer'],
            'status' => ['required', Rule::in(['processing', 'cancelled'])],
        ], [
            'ids.required' => 'Vui lòng chọn ít nhất một đơn hàng.',
            'status.in'    => 'Trạng thái không hợp lệ.',
        ]);

        $eligibleOrders = Order::with('paymentMethod')
            ->whereIn('id', $validated['ids'])
            ->get()
            ->filter(fn (Order $order) => self::isValidStatusTransition($order->status, $validated['status'])
                && self::canAdvanceOnlineOrder($order, $validated['status']));

        $updated = 0;
        foreach ($eligibleOrders as $order) {
            try {
                DB::transaction(function () use ($order, $validated) {
                    $order->update(['status' => $validated['status']]);
                    if (in_array($validated['status'], ['processing', 'shipping'], true)) {
                        $this->autoGenerateStockIssueForOrder($order);
                    } elseif ($validated['status'] === 'cancelled') {
                        $this->restoreStockForCancelledOrder($order);
                    }
                });
                $updated++;
            } catch (\Throwable $e) {
                // Bỏ qua đơn xử lý lỗi (vd không đủ tồn kho) và tiếp tục các đơn còn lại;
                // mỗi đơn nằm trong transaction riêng nên chỉ đơn lỗi bị rollback.
                continue;
            }
        }

        $skipped = count($validated['ids']) - $updated;

        $message = $validated['status'] === 'cancelled'
            ? "Đã hủy {$updated} đơn hàng."
            : "Đã duyệt {$updated} đơn hàng sang trạng thái Đang xử lý.";

        if ($skipped > 0) {
            $message .= " ({$skipped} đơn hàng không hợp lệ để chuyển trạng thái này hoặc không đủ tồn kho đã bị bỏ qua.)";
        }

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => $message]);
        }

        return back()->with('success', $message);
    }

    public function create()
    {
        return view('admin.orders.create', [
            'paymentMethods' => PaymentMethod::where('status', true)->orderBy('name')->get(),
            'statusLabels'   => self::STATUS_LABELS,
            'paymentStatusLabels' => self::PAYMENT_STATUS_LABELS,
        ]);
    }

    public function searchCustomers(Request $request)
    {
        $q = trim((string) $request->input('q'));

        $customers = User::role(UserRole::CUSTOMER->value)
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($w) use ($q) {
                    $w->where('username', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%")
                        ->orWhere('phone_number', 'like', "%{$q}%");
                });
            })
            ->orderBy('username')
            ->limit(15)
            ->get(['id', 'username', 'email', 'phone_number']);

        return response()->json(['customers' => $customers]);
    }

    public function customerAddresses(User $user)
    {
        return response()->json([
            'addresses' => $user->addresses()->orderByDesc('is_default')->orderByDesc('id')->get(),
        ]);
    }

    public function searchVariants(Request $request)
    {
        $q = trim((string) $request->input('q'));

        $variants = ProductVariant::query()
            ->with(['product:id,name', 'color:id,name', 'size:id,name'])
            ->whereHas('product', fn ($p) => $p->where('status', true))
            ->where('stock', '>', 0)
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($w) use ($q) {
                    $w->where('sku', 'like', "%{$q}%")
                        ->orWhereHas('product', fn ($p) => $p->where('name', 'like', "%{$q}%"));
                });
            })
            ->orderBy('id', 'desc')
            ->limit(20)
            ->get()
            ->map(fn (ProductVariant $variant) => [
                'id'          => $variant->id,
                'product_name' => $variant->product->name,
                'color'       => $variant->color->name ?? '',
                'size'        => $variant->size->name ?? '',
                'sku'         => $variant->sku,
                'stock'       => $variant->stock,
                'unit_price'  => $variant->final_price,
            ]);

        return response()->json(['variants' => $variants]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id'           => ['nullable', 'integer', Rule::exists('users', 'id')],
            'customer_name'     => ['required_without:user_id', 'nullable', 'string', 'max:255'],
            'address_id'        => ['nullable', 'integer', Rule::exists('addresses', 'id')],
            'new_address.city'              => ['required_without:address_id', 'nullable', 'string', 'max:255'],
            'new_address.district'          => ['nullable', 'string', 'max:255'],
            'new_address.ward'              => ['required_without:address_id', 'nullable', 'string', 'max:255'],
            'new_address.apartment_number'  => ['required_without:address_id', 'nullable', 'string', 'max:255'],
            'phone'              => ['required', 'string', 'max:20'],
            'payment_method_id' => ['required', 'integer', Rule::exists('payment_methods', 'id')],
            'status'             => ['required', Rule::in(array_keys(self::STATUS_LABELS))],
            'payment_status'     => ['required', Rule::in(array_keys(self::PAYMENT_STATUS_LABELS))],
            'shipping_fee'       => ['required', 'numeric', 'min:0'],
            'note'               => ['nullable', 'string', 'max:1000'],
            'items'              => ['required', 'array', 'min:1'],
            'items.*.product_variant_id' => ['required', 'integer', Rule::exists('product_variants', 'id')],
            'items.*.quantity'   => ['required', 'integer', 'min:1'],
        ], [
            'customer_name.required_without' => 'Vui lòng nhập tên khách hàng.',
            'new_address.city.required_without' => 'Vui lòng nhập tỉnh/thành phố cho địa chỉ mới.',
            'new_address.ward.required_without' => 'Vui lòng nhập phường/xã cho địa chỉ mới.',
            'new_address.apartment_number.required_without' => 'Vui lòng nhập địa chỉ cụ thể cho địa chỉ mới.',
            'phone.required' => 'Vui lòng nhập số điện thoại.',
            'payment_method_id.required' => 'Vui lòng chọn phương thức thanh toán.',
            'items.required' => 'Vui lòng thêm ít nhất một sản phẩm vào đơn hàng.',
            'items.*.quantity.min' => 'Số lượng sản phẩm phải lớn hơn 0.',
        ]);

        $order = DB::transaction(function () use ($validated) {
            // Không chọn khách có sẵn (user_id trống) → tự tạo khách hàng mới từ Tên + SĐT đã nhập.
            // Mật khẩu mặc định = SĐT, email placeholder duy nhất (khách này chưa từng đăng ký).
            $userId = $validated['user_id'] ?? null;
            if (! $userId) {
                $newCustomer = User::create([
                    'username'     => $validated['customer_name'],
                    'email'        => $this->generatePlaceholderCustomerEmail($validated['phone']),
                    'phone_number' => $validated['phone'],
                    'password'     => Hash::make($validated['phone']),
                    'is_active'    => true,
                    'is_protected' => false,
                ]);
                $newCustomer->assignRole(UserRole::CUSTOMER->value);
                $userId = $newCustomer->id;
            }

            $address = ($validated['address_id'] ?? null)
                ? Address::where('id', $validated['address_id'])->where('user_id', $userId)->firstOrFail()
                : Address::create([
                    'user_id'          => $userId,
                    'city'             => $validated['new_address']['city'],
                    'district'         => $validated['new_address']['district'] ?? null,
                    'ward'             => $validated['new_address']['ward'],
                    'apartment_number' => $validated['new_address']['apartment_number'],
                ]);

            $orderCode = app(\App\Services\DocumentSequenceService::class)->generateOrderCode();

            $variants = ProductVariant::with('product')
                ->whereIn('id', collect($validated['items'])->pluck('product_variant_id'))
                ->get()
                ->keyBy('id');

            $totalMoney = 0;
            foreach ($validated['items'] as $item) {
                $variant = $variants[$item['product_variant_id']];
                $totalMoney += $variant->final_price * $item['quantity'];
            }
            $totalMoney += (float) $validated['shipping_fee'];

            $order = Order::create([
                'user_id'           => $userId,
                'address_id'        => $address->id,
                'payment_method_id' => $validated['payment_method_id'],
                'order_code'        => $orderCode,
                'phone'             => $validated['phone'],
                'note'              => $validated['note'] ?? null,
                'total_money'       => $totalMoney,
                'shipping_fee'      => $validated['shipping_fee'],
                'status'            => $validated['status'],
                // Đơn tạo thẳng ở trạng thái "Hoàn thành" coi như đã thu tiền → tự đánh dấu đã thanh toán.
                'payment_status'    => $validated['status'] === 'completed' ? 'paid' : $validated['payment_status'],
            ]);

            foreach ($validated['items'] as $item) {
                $variant = $variants[$item['product_variant_id']];
                OrderItem::create([
                    'order_id'           => $order->id,
                    'product_variant_id' => $variant->id,
                    'unit_price'         => $variant->final_price,
                    'quantity'           => $item['quantity'],
                ]);
            }

            // Nếu tạo đơn ở trạng thái đã "xuất hàng" thì trừ kho ngay (giống luồng update).
            // autoGenerateStockIssueForOrder tự khóa + kiểm tra tồn từng biến thể và ném
            // ValidationException nếu thiếu → nằm trong transaction nên rollback cả đơn.
            if (in_array($order->status, ['processing', 'shipping', 'completed'], true)) {
                $this->autoGenerateStockIssueForOrder($order);
            }

            return $order;
        });

        return redirect()->route('admin.orders.detail', $order->id)
            ->with('success', "Đã tạo đơn hàng \"{$order->order_code}\" thành công.");
    }

    /**
     * Sinh email placeholder duy nhất cho khách hàng tạo nhanh ngay lúc lập đơn (chỉ có Tên + SĐT,
     * không có email thật). Lặp thêm hậu tố số nếu trùng để không vi phạm unique constraint.
     */
    private function generatePlaceholderCustomerEmail(string $phone): string
    {
        $base = "khach.{$phone}@khachhang.local";

        if (! User::where('email', $base)->exists()) {
            return $base;
        }

        $suffix = 2;
        while (User::where('email', "khach.{$phone}.{$suffix}@khachhang.local")->exists()) {
            $suffix++;
        }

        return "khach.{$phone}.{$suffix}@khachhang.local";
    }

    public function detail(string $id)
    {
        $order = Order::with([
            'user',
            'address',
            'paymentMethod',
            'orderItems.productVariant.product',
            'orderItems.productVariant.color',
            'orderItems.productVariant.size',
        ])->findOrFail($id);

        return view('admin.orders.detail', [
            'order' => $order,
            'statusLabels' => self::STATUS_LABELS,
            'paymentStatusLabels' => self::PAYMENT_STATUS_LABELS,
            'statusBadge' => self::STATUS_BADGE,
        ]);
    }

    /**
     * Trả form "Sửa đơn hàng" — gộp cả đổi trạng thái giao hàng/thanh toán VÀ sửa nội dung
     * (địa chỉ/SĐT/sản phẩm/phí ship/ghi chú) trong CÙNG một panel trượt phải (AJAX), dùng ở cả
     * danh sách lẫn trang chi tiết. Không còn modal "Cập nhật trạng thái" riêng.
     */
    public function editContent(Request $request, string $id)
    {
        $order = Order::with([
            'user',
            'paymentMethod',
            'orderItems.productVariant.product',
            'orderItems.productVariant.color',
            'orderItems.productVariant.size',
        ])->findOrFail($id);

        if (! self::canOpenEditPanel($order)) {
            $message = 'Đơn hàng ở trạng thái này không thể chỉnh sửa.';

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['message' => $message], 403);
            }

            abort(403, $message);
        }

        $data = $this->editFormData($order, self::editableScope($order));

        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.orders.partials.edit-content', $data)->render(),
            ]);
        }

        return redirect()->route('admin.orders.detail', $id);
    }

    private function editFormData(Order $order, string $scope): array
    {
        $addresses = $order->user->addresses()->orderByDesc('is_default')->orderByDesc('id')->get();

        $items = $order->orderItems->map(fn (OrderItem $item) => [
            'product_variant_id' => $item->product_variant_id,
            'product_name'       => $item->productVariant->product->name ?? '—',
            'color'              => $item->productVariant->color->name ?? '',
            'size'               => $item->productVariant->size->name ?? '',
            'sku'                => $item->productVariant->sku ?? '',
            'unit_price'         => (float) $item->unit_price,
            'quantity'           => $item->quantity,
            'stock'              => $item->productVariant->stock ?? 0,
        ])->values();

        $isOnlineGateway  = $order->paymentMethod?->isOnlineGateway() ?? false;
        $blockedByPayment = $isOnlineGateway && $order->payment_status !== 'paid';

        return [
            'order'               => $order,
            'scope'               => $scope,
            'addresses'           => $addresses,
            'items'               => $items,
            'allowedStatuses'     => self::allowedStatusOptions($order->status, $blockedByPayment),
            'paymentStatusLabels' => self::PAYMENT_STATUS_LABELS,
            'isOnlineGateway'     => $isOnlineGateway,
        ];
    }

    /**
     * Lưu form "Sửa đơn hàng" — validate + áp dụng cả đổi trạng thái (giữ nguyên toàn bộ quy tắc
     * chuyển trạng thái/khóa thanh toán online trước đây ở update()) lẫn sửa nội dung theo đúng
     * editableScope() hiện tại (không tin UI đã ẩn field — field nào không thuộc scope thì rule
     * validate tương ứng không tồn tại nên input đó bị bỏ qua hoàn toàn).
     */
    public function updateContent(Request $request, string $id)
    {
        $order = Order::with('paymentMethod')->findOrFail($id);

        if (! self::canOpenEditPanel($order)) {
            $message = 'Đơn hàng ở trạng thái này không thể chỉnh sửa.';

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['message' => $message], 403);
            }

            return back()->withErrors(['status' => $message]);
        }

        $scope = self::editableScope($order);

        $rules = [
            'status'         => ['required', Rule::in(array_keys(self::STATUS_LABELS))],
            'payment_status' => ['required', Rule::in(array_keys(self::PAYMENT_STATUS_LABELS))],
            'note'           => ['nullable', 'string', 'max:1000'],
        ];

        if ($scope !== 'none') {
            $rules['address_id']                   = ['nullable', 'integer', Rule::exists('addresses', 'id')];
            $rules['new_address.city']              = ['required_without:address_id', 'nullable', 'string', 'max:255'];
            $rules['new_address.district']          = ['nullable', 'string', 'max:255'];
            $rules['new_address.ward']              = ['required_without:address_id', 'nullable', 'string', 'max:255'];
            $rules['new_address.apartment_number']  = ['required_without:address_id', 'nullable', 'string', 'max:255'];
            $rules['phone']                         = ['required', 'string', 'max:20'];
        }

        if ($scope === 'full') {
            $rules['shipping_fee']               = ['required', 'numeric', 'min:0'];
            $rules['items']                      = ['required', 'array', 'min:1'];
            $rules['items.*.product_variant_id'] = ['required', 'integer', Rule::exists('product_variants', 'id')];
            $rules['items.*.quantity']           = ['required', 'integer', 'min:1'];
        }

        $validator = Validator::make($request->all(), $rules, [
            'status.required' => 'Vui lòng chọn trạng thái đơn hàng.',
            'payment_status.required' => 'Vui lòng chọn trạng thái thanh toán.',
            'new_address.city.required_without' => 'Vui lòng nhập tỉnh/thành phố cho địa chỉ mới.',
            'new_address.ward.required_without' => 'Vui lòng nhập phường/xã cho địa chỉ mới.',
            'new_address.apartment_number.required_without' => 'Vui lòng nhập địa chỉ cụ thể cho địa chỉ mới.',
            'phone.required' => 'Vui lòng nhập số điện thoại.',
            'items.required' => 'Vui lòng thêm ít nhất một sản phẩm vào đơn hàng.',
            'items.*.quantity.min' => 'Số lượng sản phẩm phải lớn hơn 0.',
        ]);

        $rerender = fn () => $this->editFormData(
            $order->fresh(['user', 'paymentMethod', 'orderItems.productVariant.product', 'orderItems.productVariant.color', 'orderItems.productVariant.size']),
            $scope
        );

        if ($validator->fails()) {
            if ($request->ajax() || $request->wantsJson()) {
                $errorBag = (new \Illuminate\Support\ViewErrorBag())->put('default', $validator->errors());
                $html = view('admin.orders.partials.edit-content', $rerender())->with('errors', $errorBag)->render();

                return response()->json(['message' => 'Dữ liệu chưa hợp lệ.', 'html' => $html], 422);
            }

            return back()->withErrors($validator)->withInput();
        }

        $validated = $validator->validated();
        $newStatus = $validated['status'];

        if (! self::isValidStatusTransition($order->status, $newStatus)) {
            $message = "Không thể chuyển đơn hàng từ \"" . (self::STATUS_LABELS[$order->status] ?? $order->status)
                . "\" sang \"" . (self::STATUS_LABELS[$newStatus] ?? $newStatus) . "\".";

            if ($request->ajax() || $request->wantsJson()) {
                $html = view('admin.orders.partials.edit-content', $rerender())->render();

                return response()->json(['message' => $message, 'html' => $html], 422);
            }

            return back()->withErrors(['status' => $message]);
        }

        if (! self::canAdvanceOnlineOrder($order, $newStatus)) {
            $message = "Đơn hàng thanh toán online (PayOS/MoMo) chưa được xác nhận thanh toán, "
                . "không thể chuyển sang \"" . (self::STATUS_LABELS[$newStatus] ?? $newStatus) . "\".";

            if ($request->ajax() || $request->wantsJson()) {
                $html = view('admin.orders.partials.edit-content', $rerender())->render();

                return response()->json(['message' => $message, 'html' => $html], 422);
            }

            return back()->withErrors(['status' => $message]);
        }

        DB::transaction(function () use ($order, $validated, $scope, $newStatus) {
            $oldStatus = $order->status;

            // Đơn online-gateway (PayOS/MoMo): payment_status chỉ do webhook/IPN đồng bộ, admin
            // không sửa tay được — bỏ qua giá trị form gửi lên, giữ nguyên giá trị hiện tại.
            // Đơn COD/thủ công: admin toàn quyền, và "Hoàn thành" coi như đã thu tiền (COD thu
            // khi giao xong) → tự đánh dấu đã thanh toán.
            $isOnlineGateway = $order->paymentMethod?->isOnlineGateway() ?? false;
            $paymentStatus = match (true) {
                $isOnlineGateway => $order->payment_status,
                $newStatus === 'completed' => 'paid',
                default => $validated['payment_status'],
            };

            $updateData = [
                'status'         => $newStatus,
                'payment_status' => $paymentStatus,
                'note'           => $validated['note'] ?? null,
            ];

            if ($scope !== 'none') {
                $address = ($validated['address_id'] ?? null)
                    ? Address::where('id', $validated['address_id'])->where('user_id', $order->user_id)->firstOrFail()
                    : Address::create([
                        'user_id'          => $order->user_id,
                        'city'             => $validated['new_address']['city'],
                        'district'         => $validated['new_address']['district'] ?? null,
                        'ward'             => $validated['new_address']['ward'],
                        'apartment_number' => $validated['new_address']['apartment_number'],
                    ]);

                $updateData['address_id'] = $address->id;
                $updateData['phone']      = $validated['phone'];
            }

            if ($scope === 'full') {
                $variants = ProductVariant::with('product')
                    ->whereIn('id', collect($validated['items'])->pluck('product_variant_id'))
                    ->get()
                    ->keyBy('id');

                $itemsTotal = 0;
                foreach ($validated['items'] as $item) {
                    $variant = $variants[$item['product_variant_id']];
                    $itemsTotal += $variant->final_price * $item['quantity'];
                }

                $order->orderItems()->delete();
                foreach ($validated['items'] as $item) {
                    $variant = $variants[$item['product_variant_id']];
                    OrderItem::create([
                        'order_id'           => $order->id,
                        'product_variant_id' => $variant->id,
                        'unit_price'         => $variant->final_price,
                        'quantity'           => $item['quantity'],
                    ]);
                }

                $updateData['shipping_fee'] = $validated['shipping_fee'];
                $updateData['total_money']  = $itemsTotal + (float) $validated['shipping_fee'] - (float) $order->discount_amount;
            }

            $order->update($updateData);

            if (in_array($newStatus, ['processing', 'shipping'], true) && !in_array($oldStatus, ['processing', 'shipping'], true)) {
                $this->autoGenerateStockIssueForOrder($order);
            } elseif ($newStatus === 'cancelled') {
                $this->restoreStockForCancelledOrder($order);
            }
        });

        $message = 'Cập nhật đơn hàng thành công.';

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => $message]);
        }

        return redirect()->route('admin.orders.detail', $id)->with('success', $message);
    }

    /**
     * Chỉ cho xóa đơn hàng còn ở trạng thái "Chờ xác nhận" (chưa từng trừ kho/xuất kho).
     * Đơn đã được xử lý (processing trở đi) phải hủy (cancelled) để hoàn kho đúng quy trình,
     * không xóa thẳng vì sẽ làm lệch sổ kho/báo cáo doanh thu.
     */
    public function destroy(Request $request, string $id)
    {
        $order = Order::findOrFail($id);

        if ($order->status !== 'pending') {
            $message = 'Chỉ có thể xóa đơn hàng đang ở trạng thái "Chờ xác nhận". '
                . 'Vui lòng hủy đơn hàng nếu đơn đã được xử lý.';

            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $message], 422);
            }

            return back()->withErrors(['status' => $message]);
        }

        $orderLabel = $order->order_code ?? '#' . $order->id;
        $order->delete();

        $message = "Đã xóa đơn hàng \"{$orderLabel}\" thành công.";

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => $message]);
        }

        return redirect()->route('admin.orders.list')->with('success', $message);
    }

    public function trash(Request $request)
    {
        $keyword = trim((string) $request->input('search', $request->input('keyword')));
        $perPage = in_array((int) $request->input('per_page'), [10, 25, 50], true)
            ? (int) $request->input('per_page') : 10;

        $query = Order::onlyTrashed()
            ->with(['user', 'paymentMethod'])
            ->orderBy('deleted_at', 'desc');

        if ($keyword !== '') {
            $query->where(function ($q) use ($keyword) {
                $q->where('order_code', 'like', "%{$keyword}%")
                    ->orWhere('phone', 'like', "%{$keyword}%")
                    ->orWhereHas('user', fn ($u) => $u->where('username', 'like', "%{$keyword}%"));
            });
        }

        $orders = $query->paginate($perPage)->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.orders.partials.trash-table', compact('orders'))->render(),
            ]);
        }

        return view('admin.orders.trash', compact('orders', 'keyword', 'perPage'));
    }

    public function restore(string $id)
    {
        $order = Order::onlyTrashed()->findOrFail($id);
        $order->restore();

        return redirect()->route('admin.orders.trash')
            ->with('success', "Đã khôi phục đơn hàng \"{$order->order_code}\" thành công.");
    }

    public function forceDelete(string $id)
    {
        $order = Order::onlyTrashed()->findOrFail($id);
        $orderLabel = $order->order_code ?? '#' . $order->id;
        $order->forceDelete();

        return redirect()->route('admin.orders.trash')
            ->with('success', "Đã xóa vĩnh viễn đơn hàng \"{$orderLabel}\".");
    }

    public function bulkRestore(Request $request)
    {
        $ids = array_filter((array) $request->input('ids', []), 'is_numeric');
        if (empty($ids)) {
            return back()->with('error', 'Vui lòng chọn ít nhất một đơn hàng.');
        }

        $restored = Order::onlyTrashed()->whereIn('id', $ids)->restore();

        return back()->with('success', "Đã khôi phục {$restored} đơn hàng thành công.");
    }

    public function bulkForceDelete(Request $request)
    {
        $ids = array_filter((array) $request->input('ids', []), 'is_numeric');
        if (empty($ids)) {
            return back()->with('error', 'Vui lòng chọn ít nhất một đơn hàng.');
        }

        $count = 0;
        foreach (Order::onlyTrashed()->whereIn('id', $ids)->get() as $order) {
            $count += (int) $order->forceDelete();
        }

        return back()->with('success', "Đã xóa vĩnh viễn {$count} đơn hàng.");
    }

    private function autoGenerateStockIssueForOrder(Order $order): void
    {
        $exists = \App\Models\StockIssue::where('order_id', $order->id)
            ->where('status', \App\Models\StockIssue::STATUS_COMPLETED)
            ->exists();
        if ($exists) {
            return;
        }

        $orderItems = $order->orderItems()->with('productVariant.product')->get();
        if ($orderItems->isEmpty()) {
            return;
        }

        $warehouse = \App\Models\Warehouse::where('is_default', true)->where('status', true)->first()
            ?? \App\Models\Warehouse::where('status', true)->first();
        if (!$warehouse) {
            throw new \Exception("Không tìm thấy kho hàng hoạt động nào trong hệ thống.");
        }

        // Mã phiếu xuất kho: dùng DocumentSequenceService (có lock chống trùng) để đồng nhất
        // định dạng với phiếu tạo tay ở StockIssueController — không tự chế generator.
        $code = app(\App\Services\DocumentSequenceService::class)->generateStockIssueCode();

        $totalQty = $orderItems->sum('quantity');
        $totalCost = $orderItems->sum(fn ($item) => $item->quantity * ($item->productVariant->cost_price ?? 0));
        $totalSale = $orderItems->sum(fn ($item) => $item->quantity * $item->unit_price);

        $stockIssue = \App\Models\StockIssue::create([
            'code' => $code,
            'issue_type' => \App\Models\StockIssue::ISSUE_TYPE_SALE,
            'warehouse_id' => $warehouse->id,
            'order_id' => $order->id,
            'reason' => "Xuất kho tự động cho đơn hàng #" . ($order->order_code ?? $order->id),
            'note' => $order->note,
            'status' => \App\Models\StockIssue::STATUS_DRAFT,
            'total_quantity' => $totalQty,
            'total_cost_amount' => $totalCost,
            'total_sale_amount' => $totalSale,
            'total_amount' => $totalSale,
            'created_by' => Auth::id() ?? 1, // Fallback if CLI/system
        ]);

        foreach ($orderItems as $item) {
            $variant = $item->productVariant;
            if (!$variant) continue;

            $lockedVariant = \App\Models\ProductVariant::lockForUpdate()->find($variant->id);
            if ($lockedVariant->stock < $item->quantity) {
                $variantName = ($lockedVariant->product->name ?? 'Sản phẩm') 
                    . ' - ' . ($lockedVariant->color->name ?? 'Không màu') 
                    . ' - Size ' . ($lockedVariant->size->name ?? 'Không size') 
                    . ' - SKU ' . ($lockedVariant->sku ?? 'N/A');
                throw ValidationException::withMessages([
                    'status' => ["Không đủ tồn kho để xuất. Sản phẩm [{$variantName}] hiện chỉ còn [{$lockedVariant->stock}] sản phẩm."]
                ]);
            }

            $stockIssue->items()->create([
                'product_id' => $lockedVariant->product_id,
                'product_variant_id' => $lockedVariant->id,
                'quantity' => $item->quantity,
                'cost_price' => $lockedVariant->cost_price,
                'sale_price' => $item->unit_price,
                'total_cost' => $lockedVariant->cost_price * $item->quantity,
                'total_sale' => $item->unit_price * $item->quantity,
            ]);

            // Trừ tồn theo FIFO qua các lô — service ghi sổ cái + đồng bộ cache tồn.
            app(\App\Services\InventoryBatchService::class)->consumeFifo(
                $lockedVariant,
                (int) $item->quantity,
                'stock_issue',
                $stockIssue->id,
                Auth::id()
            );
        }

        $stockIssue->update([
            'status' => \App\Models\StockIssue::STATUS_COMPLETED,
            'confirmed_by' => Auth::id() ?? 1,
            'confirmed_at' => now(),
            'issued_at' => now(),
        ]);

        \App\Models\StockIssueLog::create([
            'stock_issue_id' => $stockIssue->id,
            'user_id' => Auth::id() ?? 1,
            'action' => 'created',
            'message' => 'Tạo phiếu xuất kho tự động từ đơn hàng #' . ($order->order_code ?? $order->id),
        ]);

        \App\Models\StockIssueLog::create([
            'stock_issue_id' => $stockIssue->id,
            'user_id' => Auth::id() ?? 1,
            'action' => 'confirmed',
            'message' => 'Hoàn tất xuất kho tự động',
        ]);
    }

    /**
     * Hoàn kho khi hủy đơn đã trừ kho: đảo ngược phiếu xuất kho tự động (issue_type = sale,
     * status = completed) gắn với đơn — cộng lại tồn, ghi StockMovement 'import' và đánh dấu
     * phiếu xuất kho là đã hủy. Nếu đơn chưa từng trừ kho (vd pending → cancelled) thì bỏ qua.
     */
    private function restoreStockForCancelledOrder(Order $order): void
    {
        $stockIssue = \App\Models\StockIssue::with('items')
            ->where('order_id', $order->id)
            ->where('issue_type', \App\Models\StockIssue::ISSUE_TYPE_SALE)
            ->where('status', \App\Models\StockIssue::STATUS_COMPLETED)
            ->first();
        if (!$stockIssue) {
            return;
        }

        // Hoàn trả về ĐÚNG lô đã trừ (đọc ngược các bút toán export của phiếu xuất) — giá vốn không méo.
        app(\App\Services\InventoryBatchService::class)->restoreByReference(
            'stock_issue',
            $stockIssue->id,
            'stock_issue',
            $stockIssue->id,
            Auth::id()
        );

        $stockIssue->update([
            'status' => \App\Models\StockIssue::STATUS_CANCELLED,
            'cancelled_by' => Auth::id() ?? 1,
            'cancelled_at' => now(),
        ]);

        \App\Models\StockIssueLog::create([
            'stock_issue_id' => $stockIssue->id,
            'user_id' => Auth::id() ?? 1,
            'action' => 'cancelled',
            'message' => 'Hoàn kho tự động do hủy đơn hàng #' . ($order->order_code ?? $order->id),
        ]);
    }
}