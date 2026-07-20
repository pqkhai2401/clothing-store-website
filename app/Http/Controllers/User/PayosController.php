<?php

namespace App\Http\Controllers\User;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Services\OrderFulfillmentService;
use App\Services\PayosService;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class PayosController extends Controller
{
    public function __construct(private readonly PayosService $payos)
    {
    }

    /**
     * Trang hiển thị QR PayOS + tự động poll trạng thái thanh toán.
     */
    public function show(Request $request, Order $order): View|RedirectResponse
    {
        $this->authorizeOrder($request, $order);

        if ($order->payment_status === PaymentStatus::PAID->value) {
            return redirect()->route('orders.show', $order->id)
                ->with('success', 'Đơn hàng đã được thanh toán.');
        }

        // Tái sử dụng mã + QR PayOS đã sinh trước đó nếu link còn hiệu lực (PENDING/PROCESSING),
        // thay vì sinh code mới mỗi lần mở trang (F5, mở lại tab...). Trước đây làm vậy khiến:
        // (1) webhook/return tra theo code mới trong DB không thấy đơn nếu user thanh toán đúng QR cũ,
        // (2) đồng hồ đếm ngược luôn bị reset về 30 phút dù link cũ chưa hết hạn.
        // getPaymentInfo() (GET /v2/payment-requests/{id}) của PayOS không trả lại `qrCode`
        // (trường này chỉ có ở response lúc tạo mới) nên phải tự cache lại vào payos_payload.
        // Khóa dòng đơn khi quyết định "tái dùng mã cũ hay sinh mã mới" + ghi payos_order_code:
        // double-click "Tiếp tục thanh toán" hoặc mở 2 tab có thể cùng sinh 2 mã rồi ghi đè nhau;
        // mã bị ghi đè khiến webhook/return tra theo code không thấy đơn → khách trả tiền nhưng
        // đơn kẹt. getPaymentInfo/createPaymentLink là gọi HTTP ra PayOS — chấp nhận giữ khóa
        // trong lúc gọi vì 2 request chỉ cách nhau vài ms và lưu lượng thấp.
        $alreadyPaid = false;

        try {
            $payload = DB::transaction(function () use ($order, &$alreadyPaid) {
                $locked = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();

                if ($locked->payment_status === PaymentStatus::PAID->value) {
                    $alreadyPaid = true;

                    return null;
                }

                $payosOrderCode = $locked->payos_order_code ? (int) $locked->payos_order_code : null;

                if ($payosOrderCode && $locked->payos_payload) {
                    $info   = $this->payos->getPaymentInfo($payosOrderCode);
                    $status = $info['status'] ?? null;

                    if ($status === 'PAID') {
                        $alreadyPaid = true;

                        return null;
                    }

                    if ($info && in_array($status, ['PENDING', 'PROCESSING'], true)) {
                        return $locked->payos_payload; // tái dùng link cũ còn hiệu lực
                    }
                }

                // Chưa có link hợp lệ → sinh mã mới + tạo link, ghi ngay khi còn giữ khóa.
                $payosOrderCode = $this->generatePayosOrderCode($locked);
                $data = $this->payos->createPaymentLink($locked, $payosOrderCode);
                $data['_created_at'] = now()->timestamp;

                $locked->update([
                    'payos_order_code' => $payosOrderCode,
                    'payos_payload'    => $data,
                ]);

                return $data;
            });
        } catch (\Throwable $e) {
            Log::error('PayOS create link failed', ['order_id' => $order->id, 'error' => $e->getMessage()]);

            return redirect()->route('orders.show', $order->id)
                ->with('error', 'Không tạo được mã thanh toán PayOS. Vui lòng thử lại hoặc chọn phương thức khác.');
        }

        if ($alreadyPaid) {
            $this->markPaid($order->refresh());

            return redirect()->route('orders.show', $order->id)
                ->with('success', 'Đơn hàng đã được thanh toán.');
        }

        $createdAt = $payload['_created_at'] ?? now()->timestamp;
        $expiresIn = max(0, PayosService::EXPIRE_MINUTES * 60 - (now()->timestamp - $createdAt));

        return view('user.checkout.payos', [
            'order'         => $order,
            'qrCode'        => (string) ($payload['qrCode'] ?? ''),
            'checkoutUrl'   => (string) ($payload['checkoutUrl'] ?? ''),
            'amount'        => (int) round((float) $order->total_money),
            'accountName'   => $payload['accountName'] ?? null,
            'accountNumber' => $payload['accountNumber'] ?? null,
            'bin'           => $payload['bin'] ?? null,
            'bankName'      => $this->bankNameFromBin($payload['bin'] ?? null),
            'expiresIn'     => $expiresIn,
            'paymentMethods' => PaymentMethod::where('status', true)->orderBy('id')->get(),
        ]);
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

        // Chủ động hỏi PayOS — không phụ thuộc webhook nên chạy được trên localhost.
        if ($order->payos_order_code) {
            $info      = $this->payos->getPaymentInfo((int) $order->payos_order_code);
            $payStatus = $info['status'] ?? null;

            if ($payStatus === 'PAID') {
                $this->markPaid($order);

                return response()->json(['status' => 'paid', 'redirect' => route('orders.show', $order->id)]);
            }

            if (in_array($payStatus, ['CANCELLED', 'EXPIRED'], true)) {
                return response()->json(['status' => 'cancelled']);
            }
        }

        return response()->json(['status' => 'pending']);
    }

    /**
     * Người dùng được PayOS redirect về sau khi thanh toán trên trang hosted (fallback).
     */
    public function return(Request $request): RedirectResponse
    {
        $order = $this->findOrderByPayosCode($request);

        if (! $order) {
            return redirect()->route('orders.index');
        }

        if ($order->payment_status !== PaymentStatus::PAID->value) {
            $info = $this->payos->getPaymentInfo((int) $order->payos_order_code);
            if (($info['status'] ?? null) === 'PAID') {
                $this->markPaid($order);
            }
        }

        if ($order->payment_status === PaymentStatus::PAID->value) {
            return redirect()->route('orders.show', $order->id)
                ->with('success', 'Thanh toán thành công! Cảm ơn bạn đã đặt hàng.');
        }

        return redirect()->route('checkout.payos.show', $order->id)
            ->with('warning', 'Chưa nhận được thanh toán. Vui lòng thử lại.');
    }

    /**
     * Người dùng hủy thanh toán trên trang hosted (fallback).
     */
    public function cancel(Request $request): RedirectResponse
    {
        $order = $this->findOrderByPayosCode($request);

        if ($order) {
            return redirect()->route('checkout.payos.show', $order->id)
                ->with('warning', 'Bạn đã hủy thanh toán. Có thể quét lại mã để thanh toán.');
        }

        return redirect()->route('orders.index');
    }

    /**
     * Webhook server-to-server từ PayOS (không auth, không CSRF — nằm ở routes/api.php).
     */
    public function webhook(Request $request): JsonResponse
    {
        $payload = $request->all();

        if (! $this->payos->verifyWebhook($payload)) {
            return response()->json(['success' => false, 'message' => 'Invalid signature'], 400);
        }

        $data           = $payload['data'] ?? [];
        $payosOrderCode = $data['orderCode'] ?? null;

        if ($payosOrderCode) {
            $order = Order::where('payos_order_code', $payosOrderCode)->first();

            // Chỉ tin trường nằm trong phạm vi HMAC (`data.code`) — `payload.success` không
            // được ký nên có thể bị sửa mà không làm sai chữ ký, không được dùng để quyết định.
            $isSuccess = ($data['code'] ?? null) === '00';

            if ($order && $isSuccess && $order->payment_status !== PaymentStatus::PAID->value) {
                $this->markPaid($order);
            }
        }

        return response()->json(['success' => true]);
    }

    private function markPaid(Order $order): void
    {
        // Không đánh dấu đã thanh toán cho đơn đã hủy/hết hạn (tránh trạng thái mâu thuẫn
        // cancelled + paid khi webhook/poll đến sau lệnh expire). Cũng bỏ qua nếu đã paid (idempotent).
        if ($order->status === OrderStatus::CANCELLED->value) {
            // Đơn đã hủy (thường do bị supersede khi khách đặt đơn mới) nhưng khách vẫn trả tiền
            // qua QR cũ. KHÔNG tự đánh dấu paid (sẽ mâu thuẫn cancelled+paid và có thể oversell vì
            // kho/voucher đã nhả) — ghi bản ghi đối soát để admin thấy và liên hệ hoàn tiền.
            app(\App\Services\PaymentReconciliationService::class)
                ->flagCancelledButPaid($order, 'payos', (string) $order->payos_order_code);

            return;
        }

        if ($order->payment_status === PaymentStatus::PAID->value) {
            return;
        }

        // Đã thu tiền → đơn tự vào "Đang xử lý" và sinh phiếu xuất kho (chứng từ).
        // Kho KHÔNG bị trừ lại ở đây: hàng đã được giữ theo FIFO từ lúc checkout
        // (CheckoutController::store). Job hủy 30' khi tới hạn sẽ thấy đơn không còn
        // pending/unpaid nên tự bỏ qua.
        // Khóa + kiểm tra lại trong transaction để 2 webhook đến gần đồng thời (PayOS retry)
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

    private function findOrderByPayosCode(Request $request): ?Order
    {
        $code = (int) $request->query('orderCode');

        if (! $code) {
            return null;
        }

        $order = Order::where('payos_order_code', $code)->first();

        if (! $order || $order->user_id !== $request->user()->id) {
            return null;
        }

        return $order;
    }

    private function generatePayosOrderCode(Order $order): int
    {
        // Duy nhất và <= giới hạn PayOS (9_007_199_254_740_991).
        // id đơn làm tiền tố + hậu tố ngẫu nhiên để tạo lại QR nhiều lần không bị trùng.
        return (int) ($order->id * 1_000_000 + random_int(0, 999_999));
    }

    /**
     * Suy ra tên ngân hàng từ mã BIN (napas) mà PayOS trả về.
     */
    private function bankNameFromBin(?string $bin): ?string
    {
        if (! $bin) {
            return null;
        }

        // Danh sách BIN → tên ngân hàng của các ngân hàng phổ biến tại VN.
        $banks = [
            '970422' => 'Ngân hàng TMCP Quân đội (MB)',
            '970436' => 'Vietcombank',
            '970415' => 'VietinBank',
            '970418' => 'BIDV',
            '970405' => 'Agribank',
            '970407' => 'Techcombank',
            '970416' => 'ACB',
            '970432' => 'VPBank',
            '970423' => 'TPBank',
            '970403' => 'Sacombank',
            '970443' => 'SHB',
            '970437' => 'HDBank',
            '970431' => 'Eximbank',
            '970441' => 'VIB',
            '970426' => 'MSB',
            '970448' => 'OCB',
            '970440' => 'SeABank',
            '970419' => 'NCB',
            '970412' => 'PVcomBank',
            '970438' => 'BaoVietBank',
            '970425' => 'ABBANK',
            '970427' => 'VietABank',
            '970429' => 'SCB',
            '970424' => 'Shinhan Bank',
            '970433' => 'VietBank',
        ];

        return $banks[$bin] ?? ('Ngân hàng (BIN '.$bin.')');
    }
}
