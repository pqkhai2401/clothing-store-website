<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    public const STATUS_LABELS = [
        'pending'   => 'Chờ xác nhận',
        'confirmed' => 'Đã xác nhận',
        'shipping'  => 'Đang giao',
        'delivered' => 'Đã giao',
        'cancelled' => 'Đã hủy',
    ];

    public const PAYMENT_STATUS_LABELS = [
        'unpaid' => 'Chưa thanh toán',
        'paid'   => 'Đã thanh toán',
    ];

    public const STATUS_BADGE = [
        'pending'   => 'text-bg-warning',
        'confirmed' => 'text-bg-primary',
        'shipping'  => 'text-bg-info',
        'delivered' => 'text-bg-success',
        'cancelled' => 'text-bg-secondary',
    ];

    public function index(Request $request)
    {
        $keyword       = trim((string) $request->input('keyword'));
        $statusFilter  = $request->input('status', '');
        $paymentFilter = $request->input('payment_status', '');
        $perPage       = in_array((int) $request->input('per_page'), [10, 25, 50], true)
            ? (int) $request->input('per_page')
            : 10;

        $query = Order::with(['user', 'paymentMethod'])
            ->orderBy('created_at', 'desc');

        if ($keyword !== '') {
            $query->where(function ($q) use ($keyword) {
                $q->where('order_code', 'like', "%{$keyword}%")
                  ->orWhere('phone', 'like', "%{$keyword}%")
                  ->orWhereHas('user', fn ($u) => $u->where('username', 'like', "%{$keyword}%"));
            });
        }

        if ($statusFilter !== '') {
            $query->where('status', $statusFilter);
        }

        if ($paymentFilter !== '') {
            $query->where('payment_status', $paymentFilter);
        }

        $orders = $query->paginate($perPage)->appends($request->except('page'));

        return view('admin.orders.list', [
            'orders'              => $orders,
            'keyword'             => $keyword,
            'statusFilter'        => $statusFilter,
            'paymentFilter'       => $paymentFilter,
            'perPage'             => $perPage,
            'statusLabels'        => self::STATUS_LABELS,
            'paymentStatusLabels' => self::PAYMENT_STATUS_LABELS,
            'statusBadge'         => self::STATUS_BADGE,
        ]);
    }

    public function show(string $id)
    {
        $order = Order::with([
            'user', 'address', 'paymentMethod',
            'orderItems.productVariant.product',
            'orderItems.productVariant.color',
            'orderItems.productVariant.size',
        ])->findOrFail($id);

        return view('admin.orders.show', [
            'order'               => $order,
            'statusLabels'        => self::STATUS_LABELS,
            'paymentStatusLabels' => self::PAYMENT_STATUS_LABELS,
            'statusBadge'         => self::STATUS_BADGE,
        ]);
    }

    public function update(Request $request, string $id)
    {
        $order = Order::findOrFail($id);

        $request->validate([
            'status'         => ['required', Rule::in(array_keys(self::STATUS_LABELS))],
            'payment_status' => ['required', Rule::in(array_keys(self::PAYMENT_STATUS_LABELS))],
            'note'           => ['nullable', 'string', 'max:1000'],
        ], [
            'status.required'         => 'Vui lòng chọn trạng thái đơn hàng.',
            'status.in'               => 'Trạng thái đơn hàng không hợp lệ.',
            'payment_status.required' => 'Vui lòng chọn trạng thái thanh toán.',
            'payment_status.in'       => 'Trạng thái thanh toán không hợp lệ.',
            'note.max'                => 'Ghi chú không được quá 1000 ký tự.',
        ]);

        $order->update([
            'status'         => $request->input('status'),
            'payment_status' => $request->input('payment_status'),
            'note'           => $request->input('note'),
        ]);

        return redirect()->route('admin.orders.show', $id)
            ->with('success', 'Cập nhật đơn hàng thành công.');
    }
}
