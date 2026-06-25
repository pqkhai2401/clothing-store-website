<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

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
        $statusFilter  = $request->input('status');
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

        if ($statusFilter) {
            $query->where('status', $statusFilter);
        }

        $orders = $query->paginate($perPage)->appends($request->except('page'));

        return view('admin.orders.list', [
            'orders'              => $orders,
            'keyword'             => $keyword,
            'statusFilter'        => $statusFilter,
            'perPage'             => $perPage,
            'statusLabels'        => self::STATUS_LABELS,
            'paymentStatusLabels' => self::PAYMENT_STATUS_LABELS,
            'statusBadge'         => self::STATUS_BADGE,
        ]);
    }

    public function show(string $id)
    {
        $order = Order::with(['user', 'address', 'paymentMethod', 'orderItems.productVariant.product', 'orderItems.productVariant.color', 'orderItems.productVariant.size'])
            ->findOrFail($id);

        return view('admin.orders.show', [
            'order'               => $order,
            'statusLabels'        => self::STATUS_LABELS,
            'paymentStatusLabels' => self::PAYMENT_STATUS_LABELS,
            'statusBadge'         => self::STATUS_BADGE,
        ]);
    }
}
