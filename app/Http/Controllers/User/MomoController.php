<?php

namespace App\Http\Controllers\User;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\MomoService;
use App\Services\OrderFulfillmentService;
use Illuminate\Support\Facades\DB;
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

        // Khóa dòng đơn khi quyết định "tái dùng giao dịch cũ hay tạo mới" + ghi momo_order_id:
        // double-click hoặc mở 2 tab có thể cùng tạo 2 giao dịch rồi ghi đè momo_order_id nhau;
        // id bị ghi đè khiến return/IPN của giao dịch cũ tra không thấy đơn → khách trả tiền
        // nhưng đơn kẹt. createPayment/queryPayment là gọi HTTP ra MoMo — chấp nhận giữ khóa
        // trong lúc gọi vì 2 request chỉ cách nhau vài ms và lưu lượng thấp.
        $alreadyPaid = false;

        try {
            $payUrl = DB::transaction(function () use ($order, &$alreadyPaid) {
                $locked = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();

                if ($locked->payment_status === PaymentStatus::PAID->value) {
                    $alreadyPaid = true;

                    return null;
                }

                if ($locked->momo_order_id && $locked->momo_payload) {
                    $info       = $this->momo->queryPayment($locked->momo_order_id);
                    $resultCode = $info !== null ? (int) ($info['resultCode'] ?? -1) : null;

                    if ($resultCode === 0) {
                        $alreadyPaid = true;

                        return null;
                    }

                    $existingPayUrl = $locked->momo_payload['payUrl'] ?? null;
                    if ($existingPayUrl && $resultCode !== null && in_array($resultCode, self::PENDING_CODES, true)) {
                        return $existingPayUrl; // tái dùng giao dịch cũ còn đang chờ
                    }
                }

                // Chưa có giao dịch hợp lệ → tạo mới, ghi ngay khi còn giữ khóa.
                $momoOrderId = $this->generateMomoOrderId($locked);
                $data = $this->momo->createPayment($locked, $momoOrderId);

                $locked->update([
                    'momo_order_id' => $momoOrderId,
                    'momo_payload'  => $data,
                ]);

                $payUrl = $data['payUrl'] ?? null;
                if (! $payUrl) {
                    Log::error('MoMo payUrl missing', ['order_id' => $locked->id, 'response' => $data]);
                    throw new \RuntimeException('MoMo payUrl missing');
                }

                return $payUrl;
            });
        } catch (\Throwable $e) {
            Log::error('MoMo create payment failed', ['order_id' => $order->id, 'error' => $e->getMessage()]);

            return redirect()->route('orders.show', $order->id)
                ->with('error', 'Không tạo được thanh toán MoMo. Vui lòng thử lại hoặc chọn phương thức khác.');
        }

        if ($alreadyPaid) {
            $this->markPaid($order->refresh());

            return redirect()->route('orders.show', $order->id)
                ->with('success', 'Đơn hàng đã được thanh toán.');
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
        // Không đánh dấu đã thanh toán cho đơn đã hủy/hết hạn (tránh trạng thái mâu thuẫn
        // cancelled + paid khi IPN/poll đến sau lệnh expire). Cũng bỏ qua nếu đã paid (idempotent).
        if ($order->status === OrderStatus::CANCELLED->value) {
            // Đơn đã hủy (thường do bị supersede khi khách đặt đơn mới) nhưng khách vẫn trả tiền
            // qua link cũ. KHÔNG tự đánh dấu paid (sẽ mâu thuẫn cancelled+paid và có thể oversell vì
            // kho/voucher đã nhả) — ghi bản ghi đối soát để admin thấy và liên hệ hoàn tiền.
            app(\App\Services\PaymentReconciliationService::class)
                ->flagCancelledButPaid($order, 'momo', (string) $order->momo_order_id);

            return;
        }

        if ($order->payment_status === PaymentStatus::PAID->value) {
            return;
        }

        // Đã thu tiền → đơn tự vào "Đang xử lý" và sinh phiếu xuất kho (chứng từ).
        // Kho KHÔNG bị trừ lại ở đây: hàng đã được giữ theo FIFO từ lúc checkout
        // (CheckoutController::store). Job hủy 30' khi tới hạn sẽ thấy đơn không còn
        // pending/unpaid nên tự bỏ qua.
        // Khóa + kiểm tra lại trong transaction để 2 IPN đến gần đồng thời (MoMo retry)
        // không tạo 2 phiếu xuất kho.
        DB::transaction(function () use ($order) {
            $fresh = Order::whereKey($order->id)->lockForUpdate()->first();
            if (! $fresh
                || $fresh->status === OrderStatus::CANCELLED->value
                || $fresh->payment_status === PaymentStatus::PAID->value
            ) {
                return;
            }

            $fresh->update([
                'payment_status' => PaymentStatus::PAID->value,
                'status'         => OrderStatus::PROCESSING->value,
            ]);

            app(OrderFulfillmentService::class)->generateSaleStockIssue($fresh);
        });

        // Thanh toán xong mới xóa sản phẩm khỏi giỏ (đơn online trước đó vẫn giữ giỏ để user
        // có thể tiếp tục thanh toán / đặt lại nếu bỏ dở).
        $order->clearPurchasedItemsFromCart();
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
