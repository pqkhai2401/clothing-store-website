<?php

namespace App\Http\Controllers\User;

use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\MomoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MomoController extends Controller
{
    /** resultCode của query MoMo được coi là "đang chờ" (chưa terminal). */
    private const PENDING_CODES = [1000, 7000, 7002, 9000];

    public function __construct(private readonly MomoService $momo)
    {
    }

    /**
     * Tạo giao dịch MoMo rồi chuyển hướng người dùng sang cổng thanh toán hosted của MoMo
     * (payUrl = https://.../v2/gateway/pay?t=...&s=...). Người dùng thanh toán trên trang MoMo,
     * sau đó MoMo redirect về route return để đối soát kết quả.
     */
    public function show(Request $request, Order $order): RedirectResponse
    {
        $this->authorizeOrder($request, $order);

        if ($order->payment_status === PaymentStatus::PAID->value) {
            return redirect()->route('orders.show', $order->id)
                ->with('success', 'Đơn hàng đã được thanh toán.');
        }

        // Tái sử dụng giao dịch cũ nếu còn đang chờ, thay vì tạo mới mỗi lần bấm lại (việc tạo mới
        // sẽ ghi đè momo_order_id -> return/IPN của giao dịch cũ tra không thấy đơn -> đơn kẹt).
        if ($order->momo_order_id && $order->momo_payload) {
            $info       = $this->momo->queryPayment($order->momo_order_id);
            $resultCode = $info !== null ? (int) ($info['resultCode'] ?? -1) : null;

            if ($resultCode === 0) {
                $this->markPaid($order);

                return redirect()->route('orders.show', $order->id)
                    ->with('success', 'Đơn hàng đã được thanh toán.');
            }

            $existingPayUrl = $order->momo_payload['payUrl'] ?? null;
            if ($existingPayUrl && $resultCode !== null && in_array($resultCode, self::PENDING_CODES, true)) {
                return redirect()->away($existingPayUrl);
            }
        }

        $momoOrderId = $this->generateMomoOrderId($order);

        try {
            $data = $this->momo->createPayment($order, $momoOrderId);
        } catch (\Throwable $e) {
            Log::error('MoMo create payment failed', ['order_id' => $order->id, 'error' => $e->getMessage()]);

            return redirect()->route('orders.show', $order->id)
                ->with('error', 'Không tạo được thanh toán MoMo. Vui lòng thử lại hoặc chọn phương thức khác.');
        }

        $order->update([
            'momo_order_id' => $momoOrderId,
            'momo_payload'  => $data,
        ]);

        $payUrl = $data['payUrl'] ?? null;

        if (! $payUrl) {
            Log::error('MoMo payUrl missing', ['order_id' => $order->id, 'response' => $data]);

            return redirect()->route('orders.show', $order->id)
                ->with('error', 'Không nhận được liên kết thanh toán MoMo. Vui lòng thử lại.');
        }

        return redirect()->away($payUrl);
    }

    /**
     * Endpoint poll (AJAX): trả về trạng thái thanh toán hiện tại của đơn.
     */
    public function status(Request $request, Order $order): JsonResponse
    {
        $this->authorizeOrder($request, $order);

        if ($order->payment_status === PaymentStatus::PAID->value) {
            return response()->json(['status' => 'paid', 'redirect' => route('orders.show', $order->id)]);
        }

        if ($order->momo_order_id) {
            $info       = $this->momo->queryPayment($order->momo_order_id);
            $resultCode = $info !== null ? (int) ($info['resultCode'] ?? -1) : null;

            if ($resultCode === 0) {
                $this->markPaid($order);

                return response()->json(['status' => 'paid', 'redirect' => route('orders.show', $order->id)]);
            }

            if ($resultCode !== null && ! in_array($resultCode, self::PENDING_CODES, true)) {
                return response()->json(['status' => 'cancelled']);
            }
        }

        return response()->json(['status' => 'pending']);
    }

    /**
     * MoMo redirect người dùng về sau khi thanh toán trên app/trang hosted (fallback).
     */
    public function return(Request $request): RedirectResponse
    {
        $order = $this->findOrderByMomoId($request);

        if (! $order) {
            return redirect()->route('orders.index');
        }

        // Xác thực chữ ký MoMo trước khi tin kết quả.
        if ($this->momo->verifyIpn($request->all()) && (int) $request->query('resultCode', -1) === 0) {
            $this->markPaid($order);
        }

        if ($order->payment_status === PaymentStatus::PAID->value) {
            return redirect()->route('orders.show', $order->id)
                ->with('success', 'Thanh toán thành công! Cảm ơn bạn đã đặt hàng.');
        }

        return redirect()->route('checkout.momo.show', $order->id)
            ->with('warning', 'Chưa nhận được thanh toán. Vui lòng thử lại.');
    }

    /**
     * Webhook server-to-server (IPN) từ MoMo (không auth, không CSRF — nằm ở routes/api.php).
     */
    public function ipn(Request $request): JsonResponse
    {
        $payload = $request->all();

        if (! $this->momo->verifyIpn($payload)) {
            return response()->json(['message' => 'Invalid signature'], 400);
        }

        $momoOrderId = $payload['orderId'] ?? null;

        if ($momoOrderId) {
            $order = Order::where('momo_order_id', $momoOrderId)->first();

            if ($order && (int) ($payload['resultCode'] ?? -1) === 0
                && $order->payment_status !== PaymentStatus::PAID->value) {
                $this->markPaid($order);
            }
        }

        // MoMo yêu cầu phản hồi 204 No Content khi đã nhận IPN.
        return response()->json([], 204);
    }

    private function markPaid(Order $order): void
    {
        // Chỉ cập nhật trạng thái thanh toán; việc xử lý đơn/tồn kho do admin thực hiện.
        $order->update(['payment_status' => PaymentStatus::PAID->value]);
    }

    private function authorizeOrder(Request $request, Order $order): void
    {
        abort_if($order->user_id !== $request->user()->id, 403);
    }

    private function findOrderByMomoId(Request $request): ?Order
    {
        $momoOrderId = (string) $request->query('orderId');

        if ($momoOrderId === '') {
            return null;
        }

        $order = Order::where('momo_order_id', $momoOrderId)->first();

        if (! $order || $order->user_id !== $request->user()->id) {
            return null;
        }

        return $order;
    }

    private function generateMomoOrderId(Order $order): string
    {
        // Duy nhất qua nhiều lần tạo lại QR: id đơn + timestamp + hậu tố ngẫu nhiên.
        return $order->id.'-'.now()->timestamp.random_int(100, 999);
    }
}
