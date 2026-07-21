<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OrderController extends Controller
{
    const STATUS_TABS = [
        ''           => 'Tất cả',
        'pending'    => 'Chờ xử lý',
        'processing' => 'Đã xác nhận',
        'shipping'   => 'Đang giao',
        'completed'  => 'Đã giao',
        'cancelled'  => 'Đã huỷ',
    ];

    public function index(Request $request): View
    {
        $status = $request->query('status', '');

        $query = Order::where('user_id', $request->user()->id)
            // Ẩn đơn online (PayOS/MoMo) chưa thanh toán — đây là đơn "đang chờ thanh toán",
            // trạng thái sống ở giỏ hàng; chỉ khi thanh toán/COD mới coi là đơn đã chốt.
            ->excludingUnpaidOnline()
            ->with([
                'paymentMethod',
                // Để biết đơn nào đã gửi yêu cầu hủy đang chờ duyệt (tránh N+1 khi render danh sách).
                'pendingCancelRequest',
                'orderItems.productVariant.product',
                'orderItems.productVariant.color',
                'orderItems.productVariant.size',
            ])
            ->latest();

        if ($status !== '' && array_key_exists($status, self::STATUS_TABS)) {
            $query->where('status', $status);
        }

        $orders     = $query->paginate(10)->withQueryString();
        $statusTabs = self::STATUS_TABS;
        $activeTab  = $status;

        // Các cặp "đơn hàng - sản phẩm" mà user ĐÃ đánh giá -> dùng để ẩn/hiện nút
        // "Đánh giá" trên từng dòng sản phẩm. Khóa theo đơn (order_id_product_id)
        // để khi mua lại (đơn mới) vẫn hiện nút đánh giá cho đơn đó.
        $reviewedKeys = Review::where('user_id', $request->user()->id)
            ->whereNotNull('order_id')
            ->get(['order_id', 'product_id'])
            ->map(fn ($r) => $r->order_id . '_' . $r->product_id)
            ->all();

        return view('user.orders.index', compact('orders', 'statusTabs', 'activeTab', 'reviewedKeys'));
    }

    public function show(Request $request, int $id): View
    {
        $order = Order::where('user_id', $request->user()->id)
            ->with([
                'paymentMethod',
                'address',
                'orderItems.productVariant.product',
                'orderItems.productVariant.color',
                'orderItems.productVariant.size',
            ])
            ->findOrFail($id);

        return view('user.orders.show', compact('order'));
    }

    /**
     * Khách hàng hủy đơn hàng.
     * Chỉ cho phép khi đơn ở trạng thái 'pending' VÀ chưa thanh toán ('unpaid').
     * Sau khi hủy: hoàn lại tồn kho cho từng biến thể sản phẩm trong đơn.
     */
    /**
     * Khách GỬI YÊU CẦU HỦY cho đơn đã được xác nhận / đang giao.
     *
     * Không tự hủy được vì hủy các đơn này kéo theo hoàn kho và (nếu đã trả tiền) HOÀN TIỀN —
     * phải để admin duyệt. Mỗi đơn chỉ có tối đa 1 yêu cầu đang chờ.
     */
    public function requestCancel(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ], [
            'reason.required' => 'Vui lòng nhập lý do muốn hủy đơn.',
            'reason.max'      => 'Lý do không được quá 500 ký tự.',
        ]);

        $order = Order::where('user_id', $request->user()->id)->findOrFail($id);

        if (! in_array($order->status, \App\Models\OrderCancelRequest::REQUESTABLE_ORDER_STATUSES, true)) {
            return response()->json([
                'message' => 'Đơn hàng này không thể gửi yêu cầu hủy (chỉ áp dụng cho đơn đã xác nhận hoặc đang giao).',
            ], 403);
        }

        if ($order->cancelRequests()->pending()->exists()) {
            return response()->json([
                'message' => 'Bạn đã gửi yêu cầu hủy cho đơn này rồi, vui lòng chờ cửa hàng xử lý.',
            ], 409);
        }

        \App\Models\OrderCancelRequest::create([
            'order_id' => $order->id,
            'user_id'  => $request->user()->id,
            'reason'   => $validated['reason'],
            'status'   => \App\Models\OrderCancelRequest::STATUS_PENDING,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Đã gửi yêu cầu hủy đơn. Cửa hàng sẽ xem xét và phản hồi sớm nhất.',
        ]);
    }

    public function cancelOrder(Request $request, int $id): JsonResponse
    {
        // Lấy đơn hàng, chỉ cho phép đúng chủ sở hữu
        $order = Order::where('user_id', $request->user()->id)
            ->with(['orderItems.productVariant'])
            ->findOrFail($id);

        // ── Kiểm tra điều kiện nghiệp vụ (chống bypass bằng Postman) ──
        if ($order->status !== 'pending' || $order->payment_status !== 'unpaid') {
            return response()->json([
                'message' => 'Không thể hủy đơn hàng này. Đơn đã được xử lý hoặc đã thanh toán.',
            ], 403);
        }

        try {
            // Dưới mô hình "Hold & Release", đơn pending ĐANG GIỮ KHO (đã trừ FIFO từ lúc
            // checkout) nên hủy phải nhả kho về đúng lô + nhả voucher. Service xử lý atomic
            // và idempotent (khóa đơn, kiểm tra lại trạng thái) → không lo hoàn kho 2 lần.
            $cancelled = app(\App\Services\OrderCancellationService::class)
                ->cancelPendingUnpaid($order, 'Khách hàng tự hủy đơn');

            if (! $cancelled) {
                return response()->json([
                    'message' => 'Không thể hủy đơn hàng này. Đơn vừa được xử lý hoặc đã thanh toán.',
                ], 403);
            }

            return response()->json([
                'success' => true,
                'message' => 'Đơn hàng đã được hủy thành công.',
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function detail(Request $request, int $id): \Illuminate\Http\JsonResponse
    {
        $order = Order::where('user_id', $request->user()->id)
            ->with([
                'paymentMethod',
                'address',
                'orderItems.productVariant.product',
                'orderItems.productVariant.color',
                'orderItems.productVariant.size',
            ])
            ->findOrFail($id);

        $statusLabels = [
            'pending'    => 'Chờ xử lý',
            'processing' => 'Đã xác nhận',
            'shipping'   => 'Đang giao',
            'completed'  => 'Đã giao',
            'cancelled'  => 'Đã huỷ',
        ];

        $items = $order->orderItems->map(function ($item) {
            $variant = $item->productVariant;
            $product = $variant?->product;
            return [
                'name'       => $product?->name ?? 'Sản phẩm đã xoá',
                'image'      => $product?->thumbnail ? asset($product->thumbnail) : null,
                'color'      => $variant?->color?->name,
                'size'       => $variant?->size?->name,
                'unit_price' => $item->unit_price,
                'quantity'   => $item->quantity,
                'subtotal'   => $item->unit_price * $item->quantity,
            ];
        });

        $subtotal = $items->sum('subtotal');

        // Khách chỉ được hủy khi pending + unpaid
        $canCancel = $order->status === 'pending' && $order->payment_status === 'unpaid';
        // Cho phép tiếp tục thanh toán khi đơn cổng online (PayOS/MoMo) còn pending + chưa thanh toán
        $canPay = $canCancel && (bool) $order->paymentMethod?->isOnlineGateway();

        return response()->json([
            'id'             => $order->id,
            'can_cancel'     => $canCancel,
            'can_pay'        => $canPay,
            'pay_url'        => $order->paymentResumeUrl(),
            'order_code'     => $order->order_code,
            'status'         => $order->status,
            'status_label'   => $statusLabels[$order->status] ?? $order->status,
            'payment_status' => $order->payment_status,
            'payment_method' => $order->paymentMethod?->name,
            'created_at'     => $order->created_at->format('H:i d/m/Y'),
            'note'           => $order->note,
            'phone'          => $order->phone,
            'address'        => $order->address ? collect([
                $order->address->apartment_number,
                $order->address->ward,
                $order->address->city,
            ])->filter()->join(', ') : null,
            'shipping_fee'   => $order->shipping_fee,
            'total_money'    => $order->total_money,
            'subtotal'       => $subtotal,
            'items'          => $items,
        ]);
    }
}
