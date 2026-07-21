<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderSource;
use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Exports\OrdersExport;
use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentMethod;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\VoucherService;
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
        'refunded' => 'Đã hoàn tiền',
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
        'shipping'   => ['completed', 'cancelled'],
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
            // Đơn ĐÃ THU TIỀN nhưng bị hủy → còn nợ khách tiền, admin phải hoàn thủ công.
            // Suy ra trực tiếp từ trạng thái, không cần lưu thêm cột/bảng nào.
            'refundPending'       => Order::where('status', 'cancelled')->where('payment_status', 'paid')->count(),
            'refundPendingAmount' => (float) Order::where('status', 'cancelled')->where('payment_status', 'paid')->sum('total_money'),
            // Yêu cầu hủy đơn do khách gửi, đang chờ admin duyệt.
            'cancelRequests'      => \App\Models\OrderCancelRequest::pending()->count(),
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
        // pendingCancelRequest: để bảng hiện nhãn "Khách xin hủy" ngay trên dòng (tránh N+1).
        $query = Order::with(['user', 'paymentMethod', 'pendingCancelRequest'])->excludingUnpaidOnline();

        // Lọc đúng các đơn KHÁCH ĐANG XIN HỦY (không phải mọi đơn "đang xử lý"): yêu cầu có thể
        // nằm ở đơn 'processing' lẫn 'shipping' nên phải lọc theo chính bản ghi yêu cầu.
        if ($request->boolean('cancel_request')) {
            $query->whereHas('cancelRequests', fn ($q) => $q->pending());
        }

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
            // Đang lọc "khách xin hủy" → hiện chip để admin biết và tắt được.
            'cancelRequestFilter' => $request->boolean('cancel_request'),
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
                    // Khóa + kiểm tra lại trong transaction: đơn có thể vừa bị luồng khác đổi
                    // trạng thái sau bước lọc eligibleOrders (đọc không khóa) ở trên.
                    $locked = Order::with('paymentMethod')->whereKey($order->id)->lockForUpdate()->firstOrFail();

                    if (! self::isValidStatusTransition($locked->status, $validated['status'])
                        || ! self::canAdvanceOnlineOrder($locked, $validated['status'])) {
                        throw new \App\Exceptions\StaleOrderStateException();
                    }

                    $locked->update(['status' => $validated['status']]);
                    if (in_array($validated['status'], ['processing', 'shipping'], true)) {
                        $this->autoGenerateStockIssueForOrder($locked);
                    } elseif ($validated['status'] === 'cancelled') {
                        $this->restoreStockForCancelledOrder($locked);
                        \App\Services\VoucherService::releaseUsage($locked->id);
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
            // Khi validate() ở store() thất bại (vd. trùng SĐT), Laravel redirect back() +
            // withInput() về chính trang này — nhưng danh sách sản phẩm đã thêm chỉ tồn tại
            // trong biến JS `items` phía client, KHÔNG có input <select multiple> nào giữ lại
            // nên reload xong sẽ mất trắng. Dựng lại đầy đủ dữ liệu hiển thị (tên, ảnh, giá...)
            // từ old('items') (chỉ có product_variant_id + quantity) để JS bootstrap lại bảng.
            'oldItems'       => $this->buildOldItemsForDisplay(old('items', [])),
        ]);
    }

    /**
     * Dựng lại dữ liệu hiển thị đầy đủ (tên, ảnh, màu, size, giá, tồn) cho các sản phẩm đã
     * chọn trước đó từ old('items') (chỉ có product_variant_id + quantity) — dùng khi trang
     * "Thêm đơn hàng" reload lại sau một lần submit thất bại, để bảng sản phẩm không bị mất.
     */
    private function buildOldItemsForDisplay(array $oldItemsRaw): array
    {
        if (empty($oldItemsRaw)) {
            return [];
        }

        $variantIds = collect($oldItemsRaw)->pluck('product_variant_id')->filter()->all();
        $variants = ProductVariant::with(['product:id,name,thumbnail', 'color:id,name,hex_code', 'size:id,name'])
            ->whereIn('id', $variantIds)
            ->get()
            ->keyBy('id');

        return collect($oldItemsRaw)
            ->map(function ($item) use ($variants) {
                $variant = $variants[$item['product_variant_id'] ?? null] ?? null;
                if (! $variant) {
                    return null;
                }

                return [
                    'id'         => $variant->id,
                    'name'       => $variant->product->name ?? '',
                    'thumbnail'  => $variant->product->thumbnail ?? null,
                    'color'      => $variant->color->name ?? '',
                    'color_hex'  => $variant->color?->display_hex_code ?? '#ccc',
                    'size'       => $variant->size->name ?? '',
                    'unit_price' => (float) $variant->final_price,
                    'stock'      => $variant->stock,
                    'quantity'   => (int) ($item['quantity'] ?? 1),
                ];
            })
            ->filter()
            ->values()
            ->all();
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
            ->with(['product:id,name,thumbnail', 'color:id,name,hex_code', 'size:id,name'])
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
                'thumbnail'   => $variant->product->thumbnail,
                'color'       => $variant->color->name ?? '',
                'color_hex'   => $variant->color?->display_hex_code ?? '#ccc',
                'size'        => $variant->size->name ?? '',
                'sku'         => $variant->sku,
                'stock'       => $variant->stock,
                'unit_price'  => $variant->final_price,
            ]);

        return response()->json(['variants' => $variants]);
    }

    /**
     * Xem trước mã giảm giá khi admin đang nhập ở trang Thêm đơn hàng — chỉ để hiển thị số
     * tiền được giảm ngay lúc gõ, KHÔNG khóa dòng/ghi lượt dùng (việc đó chỉ xảy ra thật sự
     * lúc store() tạo đơn, xem VoucherService::lockAndRecordUsage()).
     */
    public function checkVoucher(Request $request)
    {
        $request->validate([
            'code'     => ['required', 'string', 'max:255'],
            'subtotal' => ['required', 'numeric', 'min:0'],
            'user_id'  => ['nullable', 'integer'],
        ]);

        $subtotal = (float) $request->input('subtotal');

        try {
            $voucher = VoucherService::validate(
                $request->input('code'),
                $subtotal,
                $request->input('user_id') ? (int) $request->input('user_id') : null
            );
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->errors()['voucher_code'][0] ?? 'Mã giảm giá không hợp lệ.',
            ], 422);
        }

        $discountAmount = VoucherService::calculateDiscount($voucher, $subtotal);

        return response()->json([
            'success'         => true,
            'discount_amount' => round($discountAmount, 2),
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            // Chỉ kiểm tra user_id tồn tại VÀ có role customer — tránh gán nhầm đơn cho một
            // tài khoản nhân sự/admin nếu request bị chỉnh sửa tay (id đó vẫn "tồn tại" nhưng
            // không phải khách hàng). Dùng closure vì scope role() cần Eloquent builder, không
            // gọi được qua Rule::exists() (chỉ có query builder thô).
            'user_id'           => ['nullable', 'integer', function ($attribute, $value, $fail) {
                if ($value && ! User::role(UserRole::CUSTOMER->value)->where('id', $value)->exists()) {
                    $fail('Khách hàng đã chọn không hợp lệ.');
                }
            }],
            'customer_name'     => ['required_without:user_id', 'nullable', 'string', 'max:255'],
            // Không tự ràng buộc address_id thuộc đúng user_id ở đây được (Rule::exists không so
            // sánh chéo cột từ input khác) — việc đó nằm ở closure after() bên dưới, để trả lỗi rõ
            // ràng thay vì để Address::firstOrFail() trong transaction ném 404 xấu.
            'address_id'        => ['nullable', 'integer', Rule::exists('addresses', 'id')],
            'new_address.city'              => ['required_without:address_id', 'nullable', 'string', 'max:255'],
            'new_address.ward'              => ['required_without:address_id', 'nullable', 'string', 'max:255'],
            'new_address.apartment_number'  => ['required_without:address_id', 'nullable', 'string', 'max:255'],
            // Khi tạo khách hàng mới (user_id trống), SĐT sẽ được dùng làm phone_number của tài
            // khoản mới → phải duy nhất, nếu không sẽ vỡ unique constraint ở tầng DB (500 lỗi xấu
            // thay vì thông báo rõ ràng cho admin). ignore(user_id) để khi ĐÃ chọn khách có sẵn thì
            // không tự chặn nhầm chính SĐT của họ.
            'phone'              => ['required', 'string', 'max:20', Rule::unique('users', 'phone_number')->ignore($request->input('user_id'))],
            'payment_method_id' => ['required', 'integer', Rule::exists('payment_methods', 'id')],
            'status'             => ['required', Rule::in(array_keys(self::STATUS_LABELS))],
            'payment_status'     => ['required', Rule::in(array_keys(self::PAYMENT_STATUS_LABELS))],
            'shipping_fee'       => ['required', 'numeric', 'min:0'],
            'voucher_code'       => ['nullable', 'string', 'max:255'],
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
            'phone.unique' => 'Số điện thoại này đã được dùng bởi một khách hàng khác — vui lòng tìm và chọn khách hàng đó thay vì tạo mới.',
            'payment_method_id.required' => 'Vui lòng chọn phương thức thanh toán.',
            'items.required' => 'Vui lòng thêm ít nhất một sản phẩm vào đơn hàng.',
            'items.*.quantity.min' => 'Số lượng sản phẩm phải lớn hơn 0.',
        ]);

        $validator->after(function ($validator) use ($request) {
            $userId    = $request->input('user_id');
            $addressId = $request->input('address_id');

            // address_id phải thuộc đúng khách hàng đã chọn — nếu không, đó là dữ liệu bị
            // lệch (customer đã đổi mà address cũ chưa được reset) chứ không phải lỗi hệ thống.
            if ($addressId) {
                if (! $userId) {
                    $validator->errors()->add('address_id', 'Không thể chọn địa chỉ đã lưu khi chưa chọn khách hàng có sẵn.');
                } elseif (! Address::where('id', $addressId)->where('user_id', $userId)->exists()) {
                    $validator->errors()->add('address_id', 'Địa chỉ đã chọn không thuộc về khách hàng này.');
                }
            }

            // Chặn đặt số lượng vượt tồn kho ngay lúc tạo đơn — trước đây chỉ được kiểm ở
            // autoGenerateStockIssueForOrder() nên đơn "Chờ xác nhận"/"Đã hủy" vẫn lưu được số
            // lượng vô lý (vượt tồn thực tế), chỉ vỡ ra khi ai đó chuyển đơn sang "Đang xử lý".
            // Giới hạn max=stock trên input chỉ ở phía client, dễ bị qua mặt nếu gửi thẳng request.
            $items = $request->input('items', []);
            if (is_array($items)) {
                $variantIds = collect($items)->pluck('product_variant_id')->filter()->all();
                $variants = ProductVariant::with('product:id,name')->whereIn('id', $variantIds)->get()->keyBy('id');

                foreach ($items as $idx => $item) {
                    $variant = $variants[$item['product_variant_id'] ?? null] ?? null;
                    if (! $variant) continue;

                    if ((int) ($item['quantity'] ?? 0) > $variant->stock) {
                        $productName = $variant->product->name ?? 'Sản phẩm';
                        // Bảng sản phẩm ở giao diện được JS dựng lại từ mảng rỗng mỗi lần tải trang
                        // (không phục hồi lại danh sách cũ khi validate lỗi), nên nêu rõ tên sản
                        // phẩm trong lỗi — nếu không admin sẽ không biết items.{idx} nào bị sai.
                        $validator->errors()->add(
                            "items.{$idx}.quantity",
                            "Số lượng sản phẩm \"{$productName}\" ({$item['quantity']}) vượt quá tồn kho hiện có ({$variant->stock})."
                        );
                    }
                }
            }
        });

        $validated = $validator->validate();

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
                    'ward'             => $validated['new_address']['ward'],
                    'apartment_number' => $validated['new_address']['apartment_number'],
                ]);

            $orderCode = app(\App\Services\DocumentSequenceService::class)->generateOrderCode();

            $variants = ProductVariant::with(['product', 'color', 'size'])
                ->whereIn('id', collect($validated['items'])->pluck('product_variant_id'))
                ->get()
                ->keyBy('id');

            $subtotal = 0;
            foreach ($validated['items'] as $item) {
                $variant = $variants[$item['product_variant_id']];
                $subtotal += $variant->final_price * $item['quantity'];
            }

            // Voucher: validate lại đúng luật dùng chung với checkout khách hàng (VoucherService) —
            // không tin discount từ client. $userId ở đây có thể là khách vừa tạo mới trong chính
            // transaction này nên không thể pre-check trước transaction như checkout (nơi user đã
            // đăng nhập sẵn) — validate ngay tại đây, và lockAndRecordUsage() bên dưới mới là bước
            // chống race-condition thật sự (2 request cùng dùng 1 mã cùng lúc).
            $voucher = null;
            $discountAmount = 0.0;
            if (! empty($validated['voucher_code'])) {
                $voucher = VoucherService::validate($validated['voucher_code'], $subtotal, $userId);
                $discountAmount = VoucherService::calculateDiscount($voucher, $subtotal);
            }

            $totalMoney = $subtotal - $discountAmount + (float) $validated['shipping_fee'];

            $order = Order::create([
                'user_id'           => $userId,
                'address_id'        => $address->id,
                'payment_method_id' => $validated['payment_method_id'],
                // Phân biệt với đơn khách tự đặt qua website (mặc định 'online' theo cột DB) —
                // ghi lại luôn nhân sự đã tạo để tra soát khi cần.
                'source'            => OrderSource::ADMIN->value,
                'created_by'        => Auth::id(),
                'order_code'        => $orderCode,
                'phone'             => $validated['phone'],
                'note'              => $validated['note'] ?? null,
                'total_money'       => $totalMoney,
                'shipping_fee'      => $validated['shipping_fee'],
                'voucher_id'        => $voucher?->id,
                'discount_amount'   => $discountAmount,
                'status'            => $validated['status'],
                // Đơn tạo thẳng ở trạng thái "Hoàn thành" coi như đã thu tiền → tự đánh dấu đã thanh toán.
                'payment_status'    => $validated['status'] === 'completed' ? 'paid' : $validated['payment_status'],
                // Mốc ghi nhận doanh thu (xem báo cáo Thống kê doanh thu) — chỉ set khi đơn thực
                // sự hoàn tất ngay từ lúc tạo, không set cho các trạng thái khác.
                'completed_at'      => $validated['status'] === 'completed' ? now() : null,
            ]);

            // "status" cho phép tạo thẳng đơn ở trạng thái "Đã hủy" ngay từ đầu — đơn này chưa từng
            // thật sự tồn tại/giao dịch nên KHÔNG được tính là một lượt dùng mã (nếu không, huỷ nó
            // sau đó không giải phóng được lượt dùng vì không có bước chuyển trạng thái nào chạy
            // qua releaseUsage(), khiến voucher bị "mất" một lượt vĩnh viễn oan uổng).
            if ($voucher && $validated['status'] !== 'cancelled') {
                VoucherService::lockAndRecordUsage($voucher->id, $userId, $order->id);
            }

            foreach ($validated['items'] as $item) {
                $variant = $variants[$item['product_variant_id']];
                OrderItem::create([
                    'order_id'           => $order->id,
                    'product_variant_id' => $variant->id,
                    // Snapshot thông tin sản phẩm lúc tạo đơn (M4).
                    'product_name'       => $variant->product?->name,
                    'variant_sku'        => $variant->sku,
                    'color_name'         => $variant->color?->name,
                    'size_name'          => $variant->size?->name,
                    'unit_price'         => $variant->final_price,
                    'quantity'           => $item['quantity'],
                ]);

                // HOLD: giữ hàng ngay khi tạo đơn (mọi trạng thái, giống luồng khách checkout).
                // consumeFifo tự khóa lô + ném ValidationException nếu thiếu → nằm trong
                // transaction nên rollback cả đơn. Đơn tạo thẳng ở trạng thái "Đã hủy" thì bỏ qua.
                if ($order->status !== 'cancelled') {
                    app(\App\Services\InventoryBatchService::class)->consumeFifo(
                        \App\Models\ProductVariant::lockForUpdate()->find($variant->id),
                        (int) $item['quantity'],
                        'order',
                        $order->id,
                        $userId
                    );
                }
            }

            // Đơn tạo thẳng ở trạng thái đã "xuất hàng" thì sinh luôn phiếu xuất kho (chứng từ).
            // Kho đã được giữ ở trên nên bước này KHÔNG trừ lại.
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

    public function detail(Request $request, string $id)
    {
        $order = Order::with([
            'user',
            'address',
            'paymentMethod',
            'createdBy',
            'orderItems.productVariant.product',
            'orderItems.productVariant.color',
            'orderItems.productVariant.size',
        ])->findOrFail($id);

        $data = [
            'order' => $order,
            'statusLabels' => self::STATUS_LABELS,
            'paymentStatusLabels' => self::PAYMENT_STATUS_LABELS,
            'statusBadge' => self::STATUS_BADGE,
        ];

        // Danh sách đơn mở popup "chỉ xem" qua AJAX; truy cập trực tiếp URL vẫn render trang đầy đủ.
        if ($request->ajax()) {
            return response()->json([
                'html'         => view('admin.orders.partials.detail-content', $data)->render(),
                'code'         => $order->order_code ?? ('#' . $order->id),
                'status'       => $order->status,
                'status_label' => self::STATUS_LABELS[$order->status] ?? $order->status,
                'status_css'   => self::STATUS_BADGE[$order->status] ?? '',
                'invoice_url'  => route('admin.orders.invoice', $order->id),
            ]);
        }

        return view('admin.orders.detail', $data);
    }

    /**
     * Tra cứu thông tin để HOÀN TIỀN cho một đơn đã thu tiền nhưng bị hủy.
     *
     * Hệ thống không tự chuyển tiền. Việc admin cần là biết "hoàn cho ai, vào đâu":
     *  - Đơn thanh toán online THẬT (có payos_order_code/momo_order_id) → hỏi cổng để lấy
     *    thông tin giao dịch. PayOS trả về tài khoản NGƯỜI CHUYỂN (counterAccount*) → đủ để
     *    chuyển khoản lại. MoMo là ví nên chỉ có mã giao dịch, phải hoàn qua dashboard MoMo.
     *  - Đơn được đánh dấu "đã thanh toán" thủ công / COD → KHÔNG có giao dịch để tra,
     *    chỉ còn cách liên hệ khách qua SĐT/email trên đơn.
     */
    public function refundInfo(string $id)
    {
        $order = Order::with(['user', 'paymentMethod'])->findOrFail($id);

        $payload = [
            'order_code' => $order->order_code,
            'amount'     => (float) $order->total_money,
            'gateway'    => null,
            'reference'  => null,
            'payers'     => [],
            'customer'   => [
                'name'  => $order->user?->username,
                'phone' => collect([$order->phone, $order->user?->phone_number])
                    ->first(fn ($p) => $p && $p !== '0'),
                'email' => $order->user?->email,
            ],
            'message'    => null,
        ];

        if ($order->payos_order_code) {
            $payload['gateway']   = 'PayOS';
            $payload['reference'] = (string) $order->payos_order_code;

            try {
                $info = app(\App\Services\PayosService::class)->getPaymentInfo((int) $order->payos_order_code);

                foreach (($info['transactions'] ?? []) as $tx) {
                    $payload['payers'][] = [
                        'name'      => $tx['counterAccountName'] ?? null,
                        'account'   => $tx['counterAccountNumber'] ?? null,
                        'bank'      => $tx['counterAccountBankId'] ?? ($tx['counterAccountBankName'] ?? null),
                        'amount'    => $tx['amount'] ?? null,
                        'paid_at'   => $tx['transactionDateTime'] ?? null,
                        'reference' => $tx['reference'] ?? null,
                    ];
                }

                if (empty($payload['payers'])) {
                    $payload['message'] = $info
                        ? 'Cổng không trả về giao dịch nào cho mã này — hãy tra thủ công trong dashboard PayOS.'
                        : 'Không tra cứu được từ PayOS (mã không tồn tại hoặc kết nối lỗi).';
                }
            } catch (\Throwable $e) {
                $payload['message'] = 'Lỗi khi gọi PayOS: ' . $e->getMessage();
            }
        } elseif ($order->momo_order_id) {
            $payload['gateway']   = 'MoMo';
            $payload['reference'] = (string) $order->momo_order_id;

            try {
                $info = app(\App\Services\MomoService::class)->queryPayment((string) $order->momo_order_id);
                $payload['message'] = $info
                    ? 'MoMo là ví điện tử nên không trả về số tài khoản người trả. Dùng mã giao dịch '
                      . ($info['transId'] ?? $order->momo_order_id) . ' để hoàn tiền trong dashboard MoMo.'
                    : 'Không tra cứu được từ MoMo (mã không tồn tại hoặc kết nối lỗi).';
            } catch (\Throwable $e) {
                $payload['message'] = 'Lỗi khi gọi MoMo: ' . $e->getMessage();
            }
        } else {
            $payload['message'] = 'Đơn này KHÔNG có giao dịch qua cổng thanh toán (được đánh dấu '
                . 'đã thanh toán thủ công hoặc thu tiền mặt) — không thể tra cứu tự động. '
                . 'Vui lòng liên hệ khách theo SĐT/email bên dưới để lấy thông tin nhận tiền.';
        }

        return response()->json($payload);
    }

    /**
     * Đánh dấu ĐÃ HOÀN TIỀN cho đơn đã thu tiền nhưng bị hủy.
     *
     * Đi đường riêng (không qua form cập nhật đơn) vì applyStatusChange() cố tình KHÓA
     * payment_status với đơn dùng cổng online — chỉ webhook mới được đổi. Việc hoàn tiền là
     * thao tác tay của admin nên cần một lối vào tường minh, có ghi vết ai làm/lúc nào.
     *
     * Sau khi đổi sang 'refunded': cảnh báo "Cần hoàn tiền" tự tắt (điều kiện là cancelled+paid),
     * và đơn không còn được tính vào doanh thu.
     */
    public function markRefunded(Request $request, string $id)
    {
        $order = Order::with('paymentMethod')->findOrFail($id);

        $validated = $request->validate([
            'note'          => ['nullable', 'string', 'max:500'],
            'refund_amount' => ['nullable', 'numeric', 'gt:0', 'max:' . $order->total_money],
        ], [
            'note.max'              => 'Ghi chú hoàn tiền không được quá 500 ký tự.',
            'refund_amount.numeric' => 'Số tiền hoàn không hợp lệ.',
            'refund_amount.gt'      => 'Số tiền hoàn phải lớn hơn 0.',
            'refund_amount.max'     => 'Số tiền hoàn không được vượt quá tổng tiền đơn (' . number_format($order->total_money, 0, ',', '.') . '₫).',
        ]);

        // Chỉ đơn ĐÃ HỦY + ĐÃ THU TIỀN mới có tiền để hoàn. Khóa dòng + kiểm tra lại để 2 admin
        // bấm cùng lúc không ghi đè nhau.
        $done = DB::transaction(function () use ($order, $validated) {
            $fresh = Order::whereKey($order->id)->lockForUpdate()->first();

            if (! $fresh
                || $fresh->status !== 'cancelled'
                || $fresh->payment_status !== PaymentStatus::PAID->value
            ) {
                return false;
            }

            // Mặc định hoàn đủ total_money — admin chỉ cần nhập số khác khi hoàn 1 phần
            // (ví dụ trừ phí giao dịch của cổng thanh toán).
            $refundAmount = $validated['refund_amount'] ?? $fresh->total_money;

            $fresh->update([
                'payment_status' => PaymentStatus::REFUNDED->value,
                'refund_amount'  => $refundAmount,
                'refunded_at'    => now(),
            ]);

            app(\App\Services\PaymentReconciliationService::class)
                ->logRefundCompleted($fresh, $validated['note'] ?? null);

            return true;
        });

        if (! $done) {
            $message = 'Đơn này không ở trạng thái cần hoàn tiền (phải là đơn đã hủy và đã thanh toán).';

            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => $message], 422)
                : back()->with('error', $message);
        }

        $message = 'Đã ghi nhận hoàn tiền cho đơn ' . ($order->order_code ?? '#' . $order->id) . '.';

        return $request->expectsJson()
            ? response()->json(['success' => true, 'message' => $message])
            : back()->with('success', $message);
    }

    /**
     * Admin DUYỆT hoặc TỪ CHỐI yêu cầu hủy đơn do khách gửi.
     *
     * Duyệt = hủy đơn theo đúng luồng chuẩn: nhả kho về đúng lô (releaseHold), nhả voucher, và
     * nếu đơn đã thu tiền thì tự bật cờ "Cần hoàn tiền" để admin hoàn lại cho khách.
     */
    public function processCancelRequest(Request $request, string $id)
    {
        $validated = $request->validate([
            'action'     => ['required', Rule::in(['approve', 'reject'])],
            'admin_note' => ['nullable', 'string', 'max:500'],
        ], [
            'action.required' => 'Thiếu hành động cần thực hiện.',
            'admin_note.max'  => 'Ghi chú không được quá 500 ký tự.',
        ]);

        $cancelRequest = \App\Models\OrderCancelRequest::with('order')->findOrFail($id);

        $done = DB::transaction(function () use ($cancelRequest, $validated) {
            // Khóa để 2 admin bấm cùng lúc không xử lý trùng.
            $fresh = \App\Models\OrderCancelRequest::whereKey($cancelRequest->id)->lockForUpdate()->first();
            if (! $fresh || ! $fresh->isPending()) {
                return false;
            }

            if ($validated['action'] === 'approve') {
                $order = Order::whereKey($fresh->order_id)->lockForUpdate()->first();

                // Đơn có thể đã bị hủy/hoàn tất bởi luồng khác trong lúc chờ duyệt.
                if (! $order || ! in_array($order->status, \App\Models\OrderCancelRequest::REQUESTABLE_ORDER_STATUSES, true)) {
                    return false;
                }

                $order->update(['status' => 'cancelled']);

                // Nhả kho về đúng lô + bật cờ "Cần hoàn tiền" nếu đơn đã thu tiền.
                app(\App\Services\OrderCancellationService::class)->releaseHold($order->fresh());
                VoucherService::releaseUsage($order->id);
            }

            $fresh->update([
                'status'       => $validated['action'] === 'approve'
                    ? \App\Models\OrderCancelRequest::STATUS_APPROVED
                    : \App\Models\OrderCancelRequest::STATUS_REJECTED,
                'admin_note'   => $validated['admin_note'] ?? null,
                'processed_by' => Auth::id(),
                'processed_at' => now(),
            ]);

            return true;
        });

        if (! $done) {
            $message = 'Yêu cầu này đã được xử lý trước đó hoặc đơn hàng không còn ở trạng thái có thể hủy.';

            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => $message], 422)
                : back()->with('error', $message);
        }

        $message = $validated['action'] === 'approve'
            ? 'Đã duyệt yêu cầu hủy — đơn đã được hủy và hoàn kho.'
            : 'Đã từ chối yêu cầu hủy đơn.';

        return $request->expectsJson()
            ? response()->json(['success' => true, 'message' => $message])
            : back()->with('success', $message);
    }

    /**
     * Hóa đơn bán hàng dạng in (khổ A4, HTML + window.print()). Số hiệu dùng lại order_code.
     * Trả về trang in độc lập (không dùng layout admin) — đơn đã hủy vẫn in được (đóng dấu "ĐÃ HỦY").
     */
    public function invoice(string $id)
    {
        $order = Order::with([
            'user',
            'address',
            'paymentMethod',
            'voucher',
            'orderItems.productVariant.product',
            'orderItems.productVariant.color',
            'orderItems.productVariant.size',
        ])->findOrFail($id);

        return view('admin.orders.invoice', [
            'order'               => $order,
            'setting'             => \App\Models\Setting::current(),
            'statusLabels'        => self::STATUS_LABELS,
            'paymentStatusLabels' => self::PAYMENT_STATUS_LABELS,
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
            'thumbnail'          => $item->productVariant->product->thumbnail ?? null,
            'color'              => $item->productVariant->color->name ?? '',
            'color_hex'          => $item->productVariant->color?->display_hex_code ?? '#ccc',
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

        try {
            DB::transaction(function () use ($order, $validated, $scope, $newStatus) {
            // Khóa + kiểm tra lại trong transaction (xem quickUpdateStatus): chống 2 admin/2 tab
            // sửa đồng thời gây ghi đè trạng thái, và đảm bảo phạm vi sửa nội dung (editableScope)
            // vẫn đúng với thực tế đơn — nếu đơn vừa được luồng khác đẩy sang trạng thái khác thì
            // dừng để không xử lý kho/sản phẩm sai.
            $order = Order::with('paymentMethod')->whereKey($order->id)->lockForUpdate()->firstOrFail();

            if (! self::isValidStatusTransition($order->status, $newStatus)
                || ! self::canAdvanceOnlineOrder($order, $newStatus)
                || self::editableScope($order) !== $scope) {
                throw new \App\Exceptions\StaleOrderStateException();
            }

            $order->note = $validated['note'] ?? null;

            if ($scope !== 'none') {
                $address = ($validated['address_id'] ?? null)
                    ? Address::where('id', $validated['address_id'])->where('user_id', $order->user_id)->firstOrFail()
                    : Address::create([
                        'user_id'          => $order->user_id,
                        'city'             => $validated['new_address']['city'],
                        'ward'             => $validated['new_address']['ward'],
                        'apartment_number' => $validated['new_address']['apartment_number'],
                    ]);

                $order->address_id = $address->id;
                $order->phone      = $validated['phone'];
            }

            if ($scope === 'full') {
                $variants = ProductVariant::with(['product', 'color', 'size'])
                    ->whereIn('id', collect($validated['items'])->pluck('product_variant_id'))
                    ->get()
                    ->keyBy('id');

                $itemsTotal = 0;
                foreach ($validated['items'] as $item) {
                    $variant = $variants[$item['product_variant_id']];
                    $itemsTotal += $variant->final_price * $item['quantity'];
                }

                // Đơn đang "Chờ xác nhận" ĐANG GIỮ KHO (hold FIFO từ lúc đặt). Sửa danh sách sản
                // phẩm ⇒ phải nhả hold cũ rồi giữ lại theo danh sách mới, nếu không kho sẽ lệch
                // (hàng cũ bị giữ vĩnh viễn, hàng mới không được giữ).
                $batchService = app(\App\Services\InventoryBatchService::class);
                $batchService->restoreOutstandingByReference('order', $order->id, Auth::id());

                $order->orderItems()->delete();
                foreach ($validated['items'] as $item) {
                    $variant = $variants[$item['product_variant_id']];
                    OrderItem::create([
                        'order_id'           => $order->id,
                        'product_variant_id' => $variant->id,
                        // Snapshot thông tin sản phẩm lúc sửa đơn (M4).
                        'product_name'       => $variant->product?->name,
                        'variant_sku'        => $variant->sku,
                        'color_name'         => $variant->color?->name,
                        'size_name'          => $variant->size?->name,
                        'unit_price'         => $variant->final_price,
                        'quantity'           => $item['quantity'],
                    ]);

                    // Giữ lại kho theo số lượng mới (ném ValidationException nếu thiếu → rollback).
                    $batchService->consumeFifo(
                        ProductVariant::lockForUpdate()->find($variant->id),
                        (int) $item['quantity'],
                        'order',
                        $order->id,
                        Auth::id()
                    );
                }

                $order->shipping_fee = $validated['shipping_fee'];
                $order->total_money  = $itemsTotal + (float) $validated['shipping_fee'] - (float) $order->discount_amount;
            }

            $this->applyStatusChange($order, $newStatus, $validated['payment_status']);
            });
        } catch (\App\Exceptions\StaleOrderStateException) {
            $message = 'Đơn hàng vừa được cập nhật bởi thao tác khác. Vui lòng tải lại trang.';

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['message' => $message], 409);
            }

            return back()->withErrors(['status' => $message]);
        }

        $message = 'Cập nhật đơn hàng thành công.';

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => $message]);
        }

        return redirect()->route('admin.orders.detail', $id)->with('success', $message);
    }

    /**
     * Đổi nhanh trạng thái giao hàng/thanh toán ngay từ dropdown trong bảng danh sách — không đụng
     * tới địa chỉ/SĐT/sản phẩm (khác với updateContent()). Dùng chung các quy tắc chuyển trạng thái
     * với updateContent() qua applyStatusChange().
     */
    public function quickUpdateStatus(Request $request, string $id)
    {
        $order = Order::with('paymentMethod')->findOrFail($id);

        $validated = $request->validate([
            'status'         => ['required', Rule::in(array_keys(self::STATUS_LABELS))],
            'payment_status' => ['required', Rule::in(array_keys(self::PAYMENT_STATUS_LABELS))],
        ], [
            'status.required' => 'Vui lòng chọn trạng thái đơn hàng.',
            'payment_status.required' => 'Vui lòng chọn trạng thái thanh toán.',
        ]);

        $newStatus = $validated['status'];

        if (! self::isValidStatusTransition($order->status, $newStatus)) {
            $message = "Không thể chuyển đơn hàng từ \"" . (self::STATUS_LABELS[$order->status] ?? $order->status)
                . "\" sang \"" . (self::STATUS_LABELS[$newStatus] ?? $newStatus) . "\".";

            return response()->json(['success' => false, 'message' => $message], 422);
        }

        if (! self::canAdvanceOnlineOrder($order, $newStatus)) {
            $message = "Đơn hàng thanh toán online (PayOS/MoMo) chưa được xác nhận thanh toán, "
                . "không thể chuyển sang \"" . (self::STATUS_LABELS[$newStatus] ?? $newStatus) . "\".";

            return response()->json(['success' => false, 'message' => $message], 422);
        }

        try {
            DB::transaction(function () use ($order, $validated, $newStatus) {
                // Khóa dòng đơn rồi KIỂM TRA LẠI trong transaction: một luồng khác (admin khác,
                // 2 tab, webhook) có thể đã đổi trạng thái sau lần đọc ở trên. Nếu vậy thì dừng
                // để không ghi đè lên thay đổi đó (tránh oversell / trạng thái lệch với hiệu ứng kho).
                $locked = Order::with('paymentMethod')->whereKey($order->id)->lockForUpdate()->firstOrFail();

                if (! self::isValidStatusTransition($locked->status, $newStatus)
                    || ! self::canAdvanceOnlineOrder($locked, $newStatus)) {
                    throw new \App\Exceptions\StaleOrderStateException();
                }

                $this->applyStatusChange($locked, $newStatus, $validated['payment_status']);
            });
        } catch (\App\Exceptions\StaleOrderStateException) {
            return response()->json([
                'success' => false,
                'message' => 'Trạng thái đơn hàng vừa được cập nhật bởi thao tác khác. Vui lòng tải lại trang.',
            ], 409);
        }

        return response()->json(['success' => true, 'message' => 'Cập nhật trạng thái đơn hàng thành công.']);
    }

    /**
     * Áp dụng đổi status + tính lại payment_status theo đúng quy tắc (khóa thanh toán online,
     * "Hoàn thành" tự đánh dấu đã trả cho COD) + hiệu ứng kho (trừ/hoàn) — dùng chung bởi
     * updateContent() (đổi kèm nội dung) và quickUpdateStatus() (đổi nhanh từ danh sách).
     */
    private function applyStatusChange(Order $order, string $newStatus, string $submittedPaymentStatus): void
    {
        $oldStatus = $order->status;

        $isOnlineGateway = $order->paymentMethod?->isOnlineGateway() ?? false;
        $order->payment_status = match (true) {
            $isOnlineGateway => $order->payment_status,
            $newStatus === 'completed' => 'paid',
            default => $submittedPaymentStatus,
        };

        $order->status = $newStatus;
        // Mốc ghi nhận doanh thu: chỉ set lần đầu đơn chuyển sang "Hoàn thành" (completed là trạng
        // thái cuối nên không có chuyện đơn quay lại rồi hoàn tất lần 2).
        $order->completed_at = $newStatus === 'completed' ? now() : $order->completed_at;

        $order->save();

        if (in_array($newStatus, ['processing', 'shipping'], true) && ! in_array($oldStatus, ['processing', 'shipping'], true)) {
            $this->autoGenerateStockIssueForOrder($order);
        } elseif ($newStatus === 'cancelled') {
            $this->restoreStockForCancelledOrder($order);
            \App\Services\VoucherService::releaseUsage($order->id);
        }
    }

    /**
     * Chỉ cho xóa đơn hàng còn ở trạng thái "Chờ xác nhận". Đơn "pending" vẫn ĐANG GIỮ KHO
     * (hold FIFO từ lúc checkout, xem OrderCancellationService) và có thể đã ghi nhận lượt dùng
     * voucher, nên phải nhả kho + nhả voucher giống hệt luồng hủy đơn trước khi xóa mềm, nếu
     * không hàng sẽ bị giữ vĩnh viễn và voucher mất lượt oan (đơn đã xử lý processing trở đi
     * vẫn phải hủy (cancelled) trước, không xóa thẳng, vì sẽ làm lệch sổ kho/báo cáo doanh thu).
     */
    public function destroy(Request $request, string $id)
    {
        $orderLabel = null;

        $deleted = DB::transaction(function () use ($id, &$orderLabel) {
            $order = Order::whereKey($id)->lockForUpdate()->firstOrFail();

            if ($order->status !== 'pending') {
                return false;
            }

            $orderLabel = $order->order_code ?? '#' . $order->id;

            app(\App\Services\OrderCancellationService::class)->releaseHold($order);
            \App\Services\VoucherService::releaseUsage($order->id);

            $order->delete();

            return true;
        });

        if (! $deleted) {
            $message = 'Chỉ có thể xóa đơn hàng đang ở trạng thái "Chờ xác nhận". '
                . 'Vui lòng hủy đơn hàng nếu đơn đã được xử lý.';

            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $message], 422);
            }

            return back()->withErrors(['status' => $message]);
        }

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

    /**
     * Sinh phiếu xuất kho cho đơn khi đơn được xử lý.
     *
     * KHÔNG trừ kho ở đây: từ khi áp dụng "Hold & Release", hàng đã được giữ theo FIFO
     * ngay lúc đặt đơn (CheckoutController::store / store() của admin). Phiếu xuất kho
     * chỉ là chứng từ — trừ lại sẽ thành trừ HAI LẦN.
     */
    private function autoGenerateStockIssueForOrder(Order $order): void
    {
        app(\App\Services\OrderFulfillmentService::class)->generateSaleStockIssue($order);
    }

    /**
     * Nhả kho đang giữ khi hủy đơn: cộng trả về ĐÚNG lô đã trừ, đúng giá vốn gốc,
     * và đánh dấu phiếu xuất kho (nếu có) là đã hủy.
     */
    private function restoreStockForCancelledOrder(Order $order): void
    {
        app(\App\Services\OrderCancellationService::class)->releaseHold($order);
    }
}