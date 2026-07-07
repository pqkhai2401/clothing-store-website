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
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
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
     * Các khoảng thời gian mẫu (hôm nay/tuần/tháng/quý/năm) dùng để gợi ý chip lọc nhanh
     * và để suy ra nhãn hiển thị cho thẻ thống kê dựa trên date_from/date_to hiện tại.
     */
    private function periodRanges(): array
    {
        $now = now();

        return [
            'today'   => [$now->copy()->startOfDay(), $now->copy()->endOfDay(), 'hôm nay'],
            'week'    => [$now->copy()->startOfWeek(), $now->copy()->endOfWeek(), 'tuần này'],
            'month'   => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth(), 'tháng này'],
            'quarter' => [$now->copy()->startOfQuarter(), $now->copy()->endOfQuarter(), 'quý này'],
            'year'    => [$now->copy()->startOfYear(), $now->copy()->endOfYear(), 'năm này'],
        ];
    }

    /**
     * Suy ra khoảng ngày + nhãn hiển thị cho thẻ thống kê từ date_from/date_to đang áp dụng.
     * Nếu không lọc gì cả, mặc định thống kê theo "hôm nay". Nếu khoảng ngày khớp đúng một
     * kỳ mẫu (tuần/tháng/quý/năm hiện tại) thì dùng nhãn tương ứng, ngược lại là "khoảng đã chọn".
     */
    private function resolveStatsRange(string $dateFrom, string $dateTo): array
    {
        $ranges = $this->periodRanges();

        if ($dateFrom === '' && $dateTo === '') {
            [$from, $to, $label] = $ranges['today'];
            return [$from->toDateString(), $to->toDateString(), $label];
        }

        foreach ($ranges as [$from, $to, $label]) {
            if ($dateFrom === $from->toDateString() && $dateTo === $to->toDateString()) {
                return [$dateFrom, $dateTo, $label];
            }
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
            'periodRevenue'   => $rangeQuery()->where('status', '!=', 'cancelled')->sum('total_money'),
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

        $query = Order::with(['user', 'paymentMethod']);

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

        $updated = Order::whereIn('id', $validated['ids'])->update(['status' => $validated['status']]);

        $message = $validated['status'] === 'cancelled'
            ? "Đã hủy {$updated} đơn hàng."
            : "Đã duyệt {$updated} đơn hàng sang trạng thái Đang xử lý.";

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
            ->with(['product:id,name,price,discount', 'color:id,name', 'size:id,name'])
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
                'unit_price'  => $variant->product->final_price,
            ]);

        return response()->json(['variants' => $variants]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id'           => ['required', 'integer', Rule::exists('users', 'id')],
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
            'user_id.required' => 'Vui lòng chọn khách hàng.',
            'new_address.city.required_without' => 'Vui lòng nhập tỉnh/thành phố cho địa chỉ mới.',
            'new_address.ward.required_without' => 'Vui lòng nhập phường/xã cho địa chỉ mới.',
            'new_address.apartment_number.required_without' => 'Vui lòng nhập địa chỉ cụ thể cho địa chỉ mới.',
            'phone.required' => 'Vui lòng nhập số điện thoại.',
            'payment_method_id.required' => 'Vui lòng chọn phương thức thanh toán.',
            'items.required' => 'Vui lòng thêm ít nhất một sản phẩm vào đơn hàng.',
            'items.*.quantity.min' => 'Số lượng sản phẩm phải lớn hơn 0.',
        ]);

        $order = DB::transaction(function () use ($validated) {
            $address = ($validated['address_id'] ?? null)
                ? Address::where('id', $validated['address_id'])->where('user_id', $validated['user_id'])->firstOrFail()
                : Address::create([
                    'user_id'          => $validated['user_id'],
                    'city'             => $validated['new_address']['city'],
                    'district'         => $validated['new_address']['district'] ?? null,
                    'ward'             => $validated['new_address']['ward'],
                    'apartment_number' => $validated['new_address']['apartment_number'],
                ]);

            do {
                $orderCode = 'ORD-' . now()->format('Ymd') . '-' . str_pad((string) random_int(1, 99999), 5, '0', STR_PAD_LEFT);
            } while (Order::where('order_code', $orderCode)->exists());

            $variants = ProductVariant::with('product')
                ->whereIn('id', collect($validated['items'])->pluck('product_variant_id'))
                ->get()
                ->keyBy('id');

            $totalMoney = 0;
            foreach ($validated['items'] as $item) {
                $variant = $variants[$item['product_variant_id']];
                $totalMoney += $variant->product->final_price * $item['quantity'];
            }
            $totalMoney += (float) $validated['shipping_fee'];

            $order = Order::create([
                'user_id'           => $validated['user_id'],
                'address_id'        => $address->id,
                'payment_method_id' => $validated['payment_method_id'],
                'order_code'        => $orderCode,
                'phone'             => $validated['phone'],
                'note'              => $validated['note'] ?? null,
                'total_money'       => $totalMoney,
                'shipping_fee'      => $validated['shipping_fee'],
                'status'            => $validated['status'],
                'payment_status'    => $validated['payment_status'],
            ]);

            foreach ($validated['items'] as $item) {
                $variant = $variants[$item['product_variant_id']];
                OrderItem::create([
                    'order_id'           => $order->id,
                    'product_variant_id' => $variant->id,
                    'unit_price'         => $variant->product->final_price,
                    'quantity'           => $item['quantity'],
                ]);
            }

            return $order;
        });

        return redirect()->route('admin.orders.detail', $order->id)
            ->with('success', "Đã tạo đơn hàng \"{$order->order_code}\" thành công.");
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

    public function update(Request $request, string $id)
    {
        $order = Order::findOrFail($id);

        $request->validate([
            'status' => ['required', Rule::in(array_keys(self::STATUS_LABELS))],
            'payment_status' => ['required', Rule::in(array_keys(self::PAYMENT_STATUS_LABELS))],
            'note' => ['nullable', 'string', 'max:1000'],
        ], [
            'status.required' => 'Vui lòng chọn trạng thái đơn hàng.',
            'status.in' => 'Trạng thái đơn hàng không hợp lệ.',
            'payment_status.required' => 'Vui lòng chọn trạng thái thanh toán.',
            'payment_status.in' => 'Trạng thái thanh toán không hợp lệ.',
            'note.max' => 'Ghi chú không được quá 1000 ký tự.',
        ]);

        $order->update([
            'status' => $request->input('status'),
            'payment_status' => $request->input('payment_status'),
            'note' => $request->input('note'),
        ]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('admin.orders.detail', $id)
            ->with('success', 'Cập nhật đơn hàng thành công.');
    }
}
