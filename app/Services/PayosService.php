<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Giao tiếp với cổng thanh toán PayOS (https://payos.vn).
 *
 * Chữ ký dùng HMAC-SHA256 với checksum key: nối các tham số theo thứ tự
 * alphabet dạng "key=value&key=value" rồi hash.
 */
class PayosService
{
    private string $clientId;
    private string $apiKey;
    private string $checksumKey;
    private string $baseUrl;
    private bool $verifySsl;

    public function __construct()
    {
        $this->clientId    = (string) config('services.payos.client_id');
        $this->apiKey      = (string) config('services.payos.api_key');
        $this->checksumKey = trim((string) config('services.payos.checksum_key'));
        $this->baseUrl     = rtrim((string) config('services.payos.base_url'), '/');
        $this->verifySsl   = (bool) config('services.payos.verify_ssl', true);
    }

    /**
     * HTTP client đã gắn header xác thực PayOS.
     */
    private function client(): \Illuminate\Http\Client\PendingRequest
    {
        return Http::withHeaders([
            'x-client-id' => $this->clientId,
            'x-api-key'   => $this->apiKey,
        ])->withOptions(['verify' => $this->verifySsl])->acceptJson();
    }

    /**
     * Tạo link/QR thanh toán cho đơn hàng.
     *
     * @return array<string, mixed> data từ PayOS (checkoutUrl, qrCode, accountNumber, ...)
     *
     * @throws RuntimeException khi PayOS trả về lỗi
     */
    public function createPaymentLink(Order $order, int $payosOrderCode): array
    {
        $amount      = (int) round((float) $order->total_money);
        $description = $order->order_code;                  // <= 25 ký tự
        $returnUrl   = (string) config('services.payos.return_url');
        $cancelUrl   = (string) config('services.payos.cancel_url');

        // Chữ ký chỉ tính trên 5 trường bắt buộc này.
        $signature = $this->createSignature([
            'amount'      => $amount,
            'cancelUrl'   => $cancelUrl,
            'description' => $description,
            'orderCode'   => $payosOrderCode,
            'returnUrl'   => $returnUrl,
        ]);

        $response = $this->client()->post($this->baseUrl.'/v2/payment-requests', [
            'orderCode'   => $payosOrderCode,
            'amount'      => $amount,
            'description' => $description,
            'returnUrl'   => $returnUrl,
            'cancelUrl'   => $cancelUrl,
            'signature'   => $signature,
            'buyerName'   => (string) ($order->user->username ?? ''),
            'expiredAt'   => now()->addMinutes(30)->timestamp,
        ]);

        $body = $response->json();

        if (! $response->successful() || ($body['code'] ?? null) !== '00') {
            throw new RuntimeException('PayOS tạo link thất bại: '.($body['desc'] ?? ('HTTP '.$response->status())));
        }

        return $body['data'] ?? [];
    }

    /**
     * Lấy thông tin/trạng thái của một link thanh toán.
     * Dùng để chủ động kiểm tra (không phụ thuộc webhook — chạy được trên localhost).
     *
     * @return array<string, mixed>|null status: PENDING | PAID | PROCESSING | CANCELLED | EXPIRED
     */
    public function getPaymentInfo(int $payosOrderCode): ?array
    {
        $response = $this->client()->get($this->baseUrl.'/v2/payment-requests/'.$payosOrderCode);

        $body = $response->json();

        if (! $response->successful() || ($body['code'] ?? null) !== '00') {
            return null;
        }

        return $body['data'] ?? null;
    }

    /**
     * Xác thực chữ ký của payload webhook do PayOS gửi về.
     */
    public function verifyWebhook(array $payload): bool
    {
        $data      = $payload['data'] ?? null;
        $signature = $payload['signature'] ?? null;

        if (! is_array($data) || ! is_string($signature) || $signature === '') {
            return false;
        }

        return hash_equals($this->createSignature($data), $signature);
    }

    /**
     * Tạo chữ ký HMAC-SHA256: sắp các key theo alphabet, nối "key=value&...".
     *
     * @param array<string, mixed> $data
     */
    public function createSignature(array $data): string
    {
        ksort($data);

        $pairs = [];
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $value = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            } elseif (is_bool($value)) {
                $value = $value ? 'true' : 'false';
            } elseif (is_null($value)) {
                $value = '';
            }

            $pairs[] = $key.'='.$value;
        }

        return hash_hmac('sha256', implode('&', $pairs), $this->checksumKey);
    }
}
