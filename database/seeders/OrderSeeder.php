<?php

namespace Database\Seeders;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Address;
use App\Models\Color;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->orders() as $orderData) {
            DB::transaction(function () use ($orderData): void {
                $user = User::where('email', $orderData['user'])->first();
                $paymentMethod = PaymentMethod::where('name', $orderData['payment_method'])->first();

                if (! $user || ! $paymentMethod) {
                    return;
                }

                $address = Address::where('user_id', $user->id)
                    ->orderBy('id')
                    ->skip($orderData['address_index'] ?? 0)
                    ->first();

                if (! $address) {
                    return;
                }

                $lineItems = $this->resolveItems($orderData['items']);

                // Không tạo đơn rỗng nếu dữ liệu sản phẩm / biến thể chưa khớp.
                if ($lineItems === []) {
                    return;
                }

                $subtotal = collect($lineItems)->sum(
                    fn (array $item): float => (float) $item['unit_price'] * (int) $item['quantity']
                );
                $shippingFee = $subtotal >= 500000 ? 0 : 35000;
                $createdAt = $orderData['created_at'];

                $order = Order::updateOrCreate(
                    ['order_code' => $orderData['order_code']],
                    [
                        'user_id' => $user->id,
                        'address_id' => $address->id,
                        'payment_method_id' => $paymentMethod->id,
                        'phone' => $orderData['phone'],
                        'note' => $orderData['note'] ?? null,
                        'total_money' => $subtotal + $shippingFee,
                        'shipping_fee' => $shippingFee,
                        'status' => $orderData['status']->value,
                        'payment_status' => $orderData['payment_status']->value,
                        'created_at' => $createdAt,
                        'updated_at' => $createdAt,
                    ]
                );

                // Đồng bộ lại item giúp seeder chạy nhiều lần vẫn ra cùng một bộ dữ liệu.
                $order->orderItems()->delete();

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
    }

    private function orders(): array
    {
        return [
            [
                'user' => 'nguyenvanhien@gmail.com',
                'address_index' => 0,
                'payment_method' => 'Thanh toán khi nhận hàng (COD)',
                'order_code' => 'ORD-20260601-0001',
                'phone' => '0962847315',
                'note' => 'Giao giờ hành chính, gọi trước khi đến.',
                'status' => OrderStatus::PENDING,
                'payment_status' => PaymentStatus::UNPAID,
                'created_at' => Carbon::now()->subDays(1)->setTime(9, 15),
                'items' => [
                    ['product' => 'ao-thun-nam-basic-uniqlo', 'color' => 'Trắng', 'size' => 'M', 'quantity' => 2],
                    ['product' => 'quan-jeans-nam-slim-511-levis', 'color' => 'Xanh navy', 'size' => 'M', 'quantity' => 1],
                ],
            ],
            [
                'user' => 'nguyentrungtam@gmail.com',
                'payment_method' => 'COD',
                'order_code' => 'ORD-20260602-0002',
                'phone' => '0885173946',
                'note' => null,
                'status' => OrderStatus::PROCESSING,
                'payment_status' => PaymentStatus::PAID,
                'created_at' => Carbon::now()->subDays(2)->setTime(14, 30),
                'items' => [
                    ['product' => 'ao-polo-nam-dri-fit-nike', 'color' => 'Đen', 'size' => 'L', 'quantity' => 1],
                    ['product' => 'quan-short-the-thao-nam-nike', 'color' => 'Xám', 'size' => 'L', 'quantity' => 2],
                ],
            ],
            [
                'user' => 'nguyenthilan@gmail.com',
                'payment_method' => 'Momo',
                'order_code' => 'ORD-20260603-0003',
                'phone' => '0773948261',
                'note' => 'Khách cần giao trong buổi sáng.',
                'status' => OrderStatus::SHIPPING,
                'payment_status' => PaymentStatus::PAID,
                'created_at' => Carbon::now()->subDays(3)->setTime(10, 5),
                'items' => [
                    ['product' => 'ao-croptop-nu-cuc-boc-hm', 'color' => 'Hồng', 'size' => 'S', 'quantity' => 1],
                    ['product' => 'vay-tennis-nu-adidas', 'color' => 'Trắng', 'size' => 'S', 'quantity' => 1],
                ],
            ],
            [
                'user' => 'tranthingocmai@gmail.com',
                'payment_method' => 'Chuyển khoản ngân hàng',
                'order_code' => 'ORD-20260604-0004',
                'phone' => '0827516394',
                'note' => null,
                'status' => OrderStatus::COMPLETED,
                'payment_status' => PaymentStatus::PAID,
                'created_at' => Carbon::now()->subDays(8)->setTime(16, 45),
                'items' => [
                    ['product' => 'dam-body-midi-nu-zara', 'color' => 'Đen', 'size' => 'M', 'quantity' => 1],
                    ['product' => 'ao-so-mi-lua-co-duc-nu-zara', 'color' => 'Trắng', 'size' => 'M', 'quantity' => 1],
                ],
            ],
            [
                'user' => 'phamthuhuong@gmail.com',
                'payment_method' => 'Thanh toán khi nhận hàng (COD)',
                'order_code' => 'ORD-20260605-0005',
                'phone' => '0946832175',
                'note' => 'Khách đổi ý sau khi đặt.',
                'status' => OrderStatus::CANCELLED,
                'payment_status' => PaymentStatus::UNPAID,
                'created_at' => Carbon::now()->subDays(12)->setTime(11, 20),
                'items' => [
                    ['product' => 'ao-thun-nu-oversize-hm', 'color' => 'Đen', 'size' => 'S', 'quantity' => 1],
                ],
            ],
            [
                'user' => 'leminhkhanh@gmail.com',
                'payment_method' => 'PayOS',
                'order_code' => 'ORD-20260606-0006',
                'phone' => '0358724619',
                'note' => null,
                'status' => OrderStatus::COMPLETED,
                'payment_status' => PaymentStatus::PAID,
                'created_at' => Carbon::now()->subDays(18)->setTime(19, 10),
                'items' => [
                    ['product' => 'ao-sweatshirt-trefoil-adidas', 'color' => 'Đen', 'size' => 'L', 'quantity' => 1],
                    ['product' => 'quan-jogger-tech-fleece-nike', 'color' => 'Xanh navy', 'size' => 'L', 'quantity' => 1],
                ],
            ],
            [
                'user' => 'hoanggiahuy@gmail.com',
                'payment_method' => 'Thanh toán khi nhận hàng (COD)',
                'order_code' => 'ORD-20260607-0007',
                'phone' => '0796245831',
                'note' => 'Đóng gói kỹ giúp khách.',
                'status' => OrderStatus::PROCESSING,
                'payment_status' => PaymentStatus::PAID,
                'created_at' => Carbon::now()->subDays(5)->setTime(8, 40),
                'items' => [
                    ['product' => 'ao-khoac-bomber-nam-adidas', 'color' => 'Đen', 'size' => 'XL', 'quantity' => 1],
                    ['product' => 'ao-len-co-tron-nam-merino-uniqlo', 'color' => 'Xám', 'size' => 'L', 'quantity' => 1],
                ],
            ],
            [
                'user' => 'buianhkhoa@gmail.com',
                'payment_method' => 'Thanh toán khi nhận hàng (COD)',
                'order_code' => 'ORD-20260608-0008',
                'phone' => '0569317482',
                'note' => null,
                'status' => OrderStatus::SHIPPING,
                'payment_status' => PaymentStatus::UNPAID,
                'created_at' => Carbon::now()->subDays(4)->setTime(13, 25),
                'items' => [
                    ['product' => 'ao-khoac-gio-chong-nuoc-tnf', 'color' => 'Đen', 'size' => 'L', 'quantity' => 1],
                    ['product' => 'quan-jeans-nam-straight-hm', 'color' => 'Xanh dương', 'size' => 'M', 'quantity' => 1],
                ],
            ],
            [
                'user' => 'dangthuylinh@gmail.com',
                'payment_method' => 'PayOS',
                'order_code' => 'ORD-20260609-0009',
                'phone' => '0378165924',
                'note' => 'Tặng sinh nhật, giao sau 18h.',
                'status' => OrderStatus::COMPLETED,
                'payment_status' => PaymentStatus::PAID,
                'created_at' => Carbon::now()->subDays(24)->setTime(18, 5),
                'items' => [
                    ['product' => 'dam-maxi-hoa-nhi-zara', 'color' => 'Hồng', 'size' => 'S', 'quantity' => 1],
                    ['product' => 'ao-phao-nu-ultra-light-uniqlo', 'color' => 'Be', 'size' => 'M', 'quantity' => 1],
                ],
            ],
            [
                'user' => 'huynhkimngan@gmail.com',
                'payment_method' => 'Chuyển khoản ngân hàng',
                'order_code' => 'ORD-20260610-0010',
                'phone' => '0894167532',
                'note' => null,
                'status' => OrderStatus::PENDING,
                'payment_status' => PaymentStatus::UNPAID,
                'created_at' => Carbon::now()->subHours(6),
                'items' => [
                    ['product' => 'ao-polo-nu-slim-fit-uniqlo', 'color' => 'Hồng', 'size' => 'M', 'quantity' => 1],
                    ['product' => 'quan-tay-nu-ong-suong-hm', 'color' => 'Be', 'size' => 'M', 'quantity' => 1],
                ],
            ],
        ];
    }

    private function resolveItems(array $items): array
    {
        $resolved = [];

        foreach ($items as $item) {
            $product = Product::where('slug', $item['product'])->first();
            $color = Color::where('name', $item['color'])->first();
            $sizeName = $item['size'];

            if (! $product || ! $color) {
                continue;
            }

            $variant = ProductVariant::where('product_id', $product->id)
                ->where('color_id', $color->id)
                ->whereHas('size', fn ($query) => $query->where('name', $sizeName))
                ->first();

            if (! $variant) {
                continue;
            }

            $resolved[] = [
                'variant_id' => $variant->id,
                'unit_price' => $variant->final_price,
                'quantity' => (int) $item['quantity'],
            ];
        }

        return $resolved;
    }
}