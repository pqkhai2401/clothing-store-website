<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Giao tiếp với cổng thanh toán MoMo (https://developers.momo.vn) — API v2 gateway.
 *
 * Chữ ký dùng HMAC-SHA256 với secretKey. Khác PayOS: MoMo cố định danh sách field
 * và thứ tự cho từng loại request (create/query/ipn), không ksort tùy ý.
 */
class MomoService
{
    /** Thời hạn hiệu lực của QR/link thanh toán (phút) — đồng bộ với đếm ngược + lệnh dọn đơn. */
    public const EXPIRE_MINUTES = 30;

    private string $partnerCode;
    private string $accessKey;
    private string $secretKey;
    private string $createUrl;
    private string $queryUrl;
    private string $redirectUrl;
    private string $ipnUrl;
    private string $requestType;
    private bool $verifySsl;

    public function __construct()
    {
        $this->partnerCode = (string) config('services.momo.partner_code');
        $this->accessKey   = (string) config('services.momo.access_key');
        $this->secretKey   = (string) config('services.momo.secret_key');
        $this->createUrl   = rtrim((string) config('services.momo.endpoint'), '/');
        $this->queryUrl    = str_replace('/create', '/query', $this->createUrl);
        $this->redirectUrl = (string) config('services.momo.redirect_url');
        $this->ipnUrl      = (string) config('services.momo.ipn_url');
        $this->requestType = (string) config('services.momo.request_type', 'captureWallet');
        $this->verifySsl   = (bool) config('services.momo.verify_ssl', false);
    }

    /**
     * Tạo yêu cầu thanh toán MoMo cho đơn hàng.
     *
     * @return array<string, mixed> data từ MoMo (payUrl, qrCodeUrl, deeplink, resultCode, ...)
     *
     * @throws RuntimeException khi MoMo trả về lỗi (resultCode != 0)
     */
    public function createPayment(Order $order, string $momoOrderId): array
    {
        $amount    = (int) round((float) $order->total_money);
        $orderInfo = 'Thanh toán đơn hàng '.$order->order_code;
        $requestId = $momoOrderId;
        $extraData = '';

        // Raw signature cho create — thứ tự field theo đúng tài liệu MoMo.
        $rawSignature = 'accessKey='.$this->accessKey
            .'&amount='.$amount
            .'&extraData='.$extraData
            .'&ipnUrl='.$this->ipnUrl
            .'&orderId='.$momoOrderId
            .'&orderInfo='.$orderInfo
            .'&partnerCode='.$this->partnerCode
            .'&redirectUrl='.$this->redirectUrl
            .'&requestId='.$requestId
            .'&requestType='.$this->requestType;

        $payload = [
            'partnerCode' => $this->partnerCode,
            'partnerName' => 'HK Store',
            'storeId'     => 'HKStore',
            'requestId'   => $requestId,
            'amount'      => $amount,
            'orderId'     => $momoOrderId,
            'orderInfo'   => $orderInfo,
            'redirectUrl' => $this->redirectUrl,
            'ipnUrl'      => $this->ipnUrl,
            'lang'        => 'vi',
            'requestType' => $this->requestType,
            'autoCapture' => true,
            'extraData'   => $extraData,
            'signature'   => $this->hmac($rawSignature),
        ];

        $response = Http::withOptions(['verify' => $this->verifySsl])
            ->acceptJson()
            ->post($this->createUrl, $payload);

        $body = $response->json();

        if (! $response->successful() || (int) ($body['resultCode'] ?? -1) !== 0) {
            throw new RuntimeException('MoMo tạo thanh toán thất bại: '.($body['message'] ?? ('HTTP '.$response->status())));
        }

        return $body;
    }

    /**
     * Truy vấn trạng thái giao dịch (chủ động hỏi — không phụ thuộc IPN, chạy được trên localhost).
     *
     * @return array<string, mixed>|null (resultCode: 0 = thành công; 1000 = đang chờ; khác = lỗi/hủy)
     */
    public function queryPayment(string $momoOrderId): ?array
    {
        $requestId    = $momoOrderId.'-'.uniqid();
        $rawSignature = 'accessKey='.$this->accessKey
            .'&orderId='.$momoOrderId
            .'&partnerCode='.$this->partnerCode
            .'&requestId='.$requestId;

        $response = Http::withOptions(['verify' => $this->verifySsl])
            ->acceptJson()
            ->post($this->queryUrl, [
                'partnerCode' => $this->partnerCode,
                'requestId'   => $requestId,
                'orderId'     => $momoOrderId,
                'lang'        => 'vi',
                'signature'   => $this->hmac($rawSignature),
            ]);

        if (! $response->successful()) {
            return null;
        }

        return $response->json();
    }

    /**
     * Xác thực chữ ký của dữ liệu MoMo gửi về (IPN hoặc redirect return).
     *
     * @param array<string, mixed> $data
     */
    public function verifyIpn(array $data): bool
    {
        $signature = $data['signature'] ?? null;

        if (! is_string($signature) || $signature === '') {
            return false;
        }

        $rawSignature = 'accessKey='.$this->accessKey
            .'&amount='.($data['amount'] ?? '')
            .'&extraData='.($data['extraData'] ?? '')
            .'&message='.($data['message'] ?? '')
            .'&orderId='.($data['orderId'] ?? '')
            .'&orderInfo='.($data['orderInfo'] ?? '')
            .'&orderType='.($data['orderType'] ?? '')
            .'&partnerCode='.($data['partnerCode'] ?? '')
            .'&payType='.($data['payType'] ?? '')
            .'&requestId='.($data['requestId'] ?? '')
            .'&responseTime='.($data['responseTime'] ?? '')
            .'&resultCode='.($data['resultCode'] ?? '')
            .'&transId='.($data['transId'] ?? '');

        return hash_equals($this->hmac($rawSignature), $signature);
    }

    private function hmac(string $raw): string
    {
        return hash_hmac('sha256', $raw, $this->secretKey);
    }
}
