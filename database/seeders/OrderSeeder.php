<?php

namespace Database\Seeders;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Models\Address;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentMethod;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\DocumentSequenceService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Sinh lịch sử đơn hàng thật cho từng khách hàng (không phải dữ liệu demo cố
 * định vài dòng) — mỗi khách có 1-5 đơn rải rác trong 90 ngày gần nhất, với
 * phân bổ trạng thái/phương thức thanh toán phản ánh đúng vòng đời một đơn
 * hàng thời trang thật (phần lớn hoàn tất, một phần đang xử lý/giao, một ít
 * bị huỷ hoặc chưa thanh toán). order_code luôn lấy từ DocumentSequenceService
 * — không hand-roll — giống hệt CheckoutController/OrderController thật.
 */
class OrderSeeder extends Seeder
{
    private DocumentSequenceService $sequence;

    /** @var array<int, array{status: OrderStatus, weight: int}> */
    private array $statusWeights = [
        ['status' => OrderStatus::COMPLETED, 'weight' => 45],
        ['status' => OrderStatus::PROCESSING, 'weight' => 15],
        ['status' => OrderStatus::SHIPPING, 'weight' => 15],
        ['status' => OrderStatus::PENDING, 'weight' => 15],
        ['status' => OrderStatus::CANCELLED, 'weight' => 10],
    ];

    private array $notes = [
        null, null, null,
        'Giao giờ hành chính, gọi trước khi đến.',
        'Đóng gói kỹ giúp em nhé.',
        'Khách cần giao trong buổi sáng.',
        'Tặng sinh nhật, giao sau 18h.',
        'Không giao chủ nhật.',
        'Gửi bảo vệ toà nhà nếu không có nhà.',
        'Khách đổi ý sau khi đặt.',
    ];

    public function __construct()
    {
        $this->sequence = app(DocumentSequenceService::class);
    }

    public function run(): void
    {
        // Idempotent: đã có dữ liệu đơn hàng thì không sinh chồng thêm khi seed lại.
        if (Order::count() > 0) {
            return;
        }

        $customers = User::role(UserRole::CUSTOMER->value)->get();
        $paymentMethods = PaymentMethod::all();
        $variants = ProductVariant::with('product')->get();

        if ($customers->isEmpty() || $paymentMethods->isEmpty() || $variants->isEmpty()) {
            return;
        }

        foreach ($customers as $customer) {
            $addresses = Address::where('user_id', $customer->id)->orderByDesc('is_default')->get();
            if ($addresses->isEmpty()) {
                continue;
            }

            // Khách bị khoá tài khoản vẫn có thể có lịch sử mua hàng từ trước khi bị khoá.
            $orderCount = random_int(1, 5);

            for ($i = 0; $i < $orderCount; $i++) {
                $this->createOrder($customer, $addresses, $paymentMethods, $variants);
            }
        }
    }

    private function createOrder(User $customer, $addresses, $paymentMethods, $variants): void
    {
        DB::transaction(function () use ($customer, $addresses, $paymentMethods, $variants): void {
            $status = $this->randomStatus();
            $paymentMethod = $paymentMethods->random();
            $isCod = str_contains($paymentMethod->name, 'COD');
            $createdAt = Carbon::now()->subDays(random_int(0, 90))->subMinutes(random_int(0, 1439));

            $itemsCount = random_int(1, 3);
            $lineItems = [];
            $usedVariantIds = [];

            for ($i = 0; $i < $itemsCount; $i++) {
                $variant = $variants->random();
                if (in_array($variant->id, $usedVariantIds, true)) {
                    continue;
                }
                $usedVariantIds[] = $variant->id;

                $lineItems[] = [
                    'variant_id' => $variant->id,
                    'unit_price' => $variant->final_price,
                    'quantity' => random_int(1, 3),
                ];
            }

            if ($lineItems === []) {
                return;
            }

            $subtotal = collect($lineItems)->sum(
                fn (array $item): float => (float) $item['unit_price'] * (int) $item['quantity']
            );
            $shippingFee = $subtotal >= 500000 ? 0 : 30000;

            $paymentStatus = $this->resolvePaymentStatus($status, $isCod);

            $order = Order::create([
                'user_id' => $customer->id,
                'address_id' => $addresses->random()->id,
                'payment_method_id' => $paymentMethod->id,
                'order_code' => $this->sequence->generateOrderCode(),
                'phone' => $customer->phone_number,
                'note' => $this->notes[array_rand($this->notes)],
                'total_money' => $subtotal + $shippingFee,
                'shipping_fee' => $shippingFee,
                'status' => $status->value,
                'payment_status' => $paymentStatus->value,
                'completed_at' => $status === OrderStatus::COMPLETED
                    ? $createdAt->copy()->addDays(random_int(2, 5))
                    : null,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);

            foreach ($lineItems as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_variant_id' => $item['variant_id'],
                    'unit_price' => $item['unit_price'],
                    'quantity' => $item['quantity'],
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);
            }
        });
    }

    private function randomStatus(): OrderStatus
    {
        $totalWeight = array_sum(array_column($this->statusWeights, 'weight'));
        $roll = random_int(1, $totalWeight);
        $cumulative = 0;

        foreach ($this->statusWeights as $entry) {
            $cumulative += $entry['weight'];
            if ($roll <= $cumulative) {
                return $entry['status'];
            }
        }

        return OrderStatus::PENDING;
    }

    private function resolvePaymentStatus(OrderStatus $status, bool $isCod): PaymentStatus
    {
        return match ($status) {
            OrderStatus::COMPLETED => PaymentStatus::PAID,
            OrderStatus::CANCELLED => random_int(1, 100) <= 15 ? PaymentStatus::PAID : PaymentStatus::UNPAID,
            OrderStatus::PROCESSING, OrderStatus::SHIPPING => $isCod
                ? PaymentStatus::UNPAID
                : PaymentStatus::PAID,
            OrderStatus::PENDING => $isCod || random_int(1, 100) <= 70
                ? PaymentStatus::UNPAID
                : PaymentStatus::PAID,
        };
    }
}
