<?php

namespace App\Http\Controllers\User;

use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\PayosService;
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
        $payosOrderCode = $order->payos_order_code ? (int) $order->payos_order_code : null;
        $payload = null;

        if ($payosOrderCode && $order->payos_payload) {
            $info   = $this->payos->getPaymentInfo($payosOrderCode);
            $status = $info['status'] ?? null;

            if ($status === 'PAID') {
                $this->markPaid($order);

                return redirect()->route('orders.show', $order->id)
                    ->with('success', 'Đơn hàng đã được thanh toán.');
            }

            if ($info && in_array($status, ['PENDING', 'PROCESSING'], true)) {
                $payload = $order->payos_payload;
            }
        }

        $isNewCode = $payload === null;

        if ($isNewCode) {
            $payosOrderCode = $this->generatePayosOrderCode($order);

            try {
                $data = $this->payos->createPaymentLink($order, $payosOrderCode);
            } catch (\Throwable $e) {
                Log::error('PayOS create link failed', ['order_id' => $order->id, 'error' => $e->getMessage()]);

                return redirect()->route('orders.show', $order->id)
                    ->with('error', 'Không tạo được mã thanh toán PayOS. Vui lòng thử lại hoặc chọn phương thức khác.');
            }

            $payload = $data;
            $payload['_created_at'] = now()->timestamp;

            $order->update([
                'payos_order_code' => $payosOrderCode,
                'payos_payload'    => $payload,
            ]);
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

            $isSuccess = ($payload['success'] ?? false) === true || ($data['code'] ?? null) === '00';

            if ($order && $isSuccess && $order->payment_status !== PaymentStatus::PAID->value) {
                $this->markPaid($order);
            }
        }

        return response()->json(['success' => true]);
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
