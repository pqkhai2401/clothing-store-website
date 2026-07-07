<?php

namespace App\Http\Controllers\Admin;

use App\Exports\OrdersExport;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
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

        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.orders.partials.table', $tableData)->render(),
            ]);
        }

        $todayRevenue    = Order::whereDate('created_at', today())->where('status', '!=', 'cancelled')->sum('total_money');
        $pendingOrders   = Order::where('status', 'pending')->count();
        $cancelledOrders = Order::where('status', 'cancelled')->count();

        return view('admin.orders.index', array_merge($tableData, [
            'keyword'         => $keyword,
            'statusFilter'    => $statusFilter,
            'paymentFilter'   => $paymentFilter,
            'dateFrom'        => $dateFrom,
            'dateTo'          => $dateTo,
            'perPage'         => $perPage,
            'todayRevenue'    => $todayRevenue,
            'pendingOrders'   => $pendingOrders,
            'cancelledOrders' => $cancelledOrders,
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
