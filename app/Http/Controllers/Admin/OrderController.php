<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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

    public function index(Request $request)
    {
        $search = trim((string) $request->input('search', $request->input('keyword')));
        $keyword = $search;
        $statusFilter = $request->input('status', '');
        $paymentFilter = $request->input('payment_status', '');
        $sort      = $request->input('sort', 'created_at');
        $direction = in_array($request->input('direction'), ['asc', 'desc'], true)
            ? $request->input('direction') : 'desc';
        $perPage = in_array((int) $request->input('per_page'), [10, 25, 50], true)
            ? (int) $request->input('per_page')
            : 10;

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

        return view('admin.orders.index', array_merge($tableData, [
            'keyword'       => $keyword,
            'statusFilter'  => $statusFilter,
            'paymentFilter' => $paymentFilter,
            'perPage'       => $perPage,
        ]));
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
