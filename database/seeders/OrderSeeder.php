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
use App\Models\Size;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $orders = [
            // ── Đơn 1: customer1 – completed & paid (COD) ───────────────────────
            [
                'user'           => 'customer1@example.com',
                'address_index'  => 0,          // địa chỉ đầu tiên của user
                'payment_method' => 'Thanh toán khi nhận hàng (COD)',
                'order_code'     => 'ORD-20250110-00001',
                'phone'          => '0903000001',
                'note'           => 'Giao giờ hành chính, gọi trước 15 phút.',
                'status'         => OrderStatus::COMPLETED,
                'payment_status' => PaymentStatus::PAID,
                'created_at'     => Carbon::now()->subDays(165),
                'items'          => [
                    ['product' => 'ao-thun-nam-basic-uniqlo',   'color' => 'Trắng',     'size' => 'M', 'qty' => 2],
                    ['product' => 'quan-jeans-nam-slim-511-levis', 'color' => 'Xanh navy', 'size' => 'M', 'qty' => 1],
                ],
            ],

            // ── Đơn 2: customer1 – completed & paid (VNPay) ─────────────────────
            [
                'user'           => 'customer1@example.com',
                'address_index'  => 1,
                'payment_method' => 'VNPay',
                'order_code'     => 'ORD-20250215-00002',
                'phone'          => '0903000001',
                'note'           => null,
                'status'         => OrderStatus::COMPLETED,
                'payment_status' => PaymentStatus::PAID,
                'created_at'     => Carbon::now()->subDays(126),
                'items'          => [
                    ['product' => 'ao-hoodie-classic-champion', 'color' => 'Xám',  'size' => 'L',  'qty' => 1],
                    ['product' => 'ao-polo-nam-dri-fit-nike',   'color' => 'Đen',  'size' => 'M',  'qty' => 1],
                ],
            ],

            // ── Đơn 3: customer1 – shipping, unpaid (Chuyển khoản) ──────────────
            [
                'user'           => 'customer1@example.com',
                'address_index'  => 0,
                'payment_method' => 'Chuyển khoản ngân hàng',
                'order_code'     => 'ORD-20250601-00003',
                'phone'          => '0903000001',
                'note'           => 'Để hàng trước cửa nếu không có nhà.',
                'status'         => OrderStatus::SHIPPING,
                'payment_status' => PaymentStatus::UNPAID,
                'created_at'     => Carbon::now()->subDays(5),
                'items'          => [
                    ['product' => 'ao-khoac-gio-chong-nuoc-tnf', 'color' => 'Đen',       'size' => 'L', 'qty' => 1],
                    ['product' => 'quan-jogger-tech-fleece-nike', 'color' => 'Xanh navy', 'size' => 'L', 'qty' => 1],
                ],
            ],

            // ── Đơn 4: customer1 – cancelled (COD) ──────────────────────────────
            [
                'user'           => 'customer1@example.com',
                'address_index'  => 0,
                'payment_method' => 'Thanh toán khi nhận hàng (COD)',
                'order_code'     => 'ORD-20250318-00004',
                'phone'          => '0903000001',
                'note'           => null,
                'status'         => OrderStatus::CANCELLED,
                'payment_status' => PaymentStatus::UNPAID,
                'created_at'     => Carbon::now()->subDays(96),
                'items'          => [
                    ['product' => 'vay-tennis-nu-adidas', 'color' => 'Trắng', 'size' => 'S', 'qty' => 1],
                ],
            ],

            // ── Đơn 5: customer2 – completed & paid (Momo) ──────────────────────
            [
                'user'           => 'customer2@example.com',
                'address_index'  => 0,
                'payment_method' => 'Momo',
                'order_code'     => 'ORD-20250120-00005',
                'phone'          => '0903000002',
                'note'           => null,
                'status'         => OrderStatus::COMPLETED,
                'payment_status' => PaymentStatus::PAID,
                'created_at'     => Carbon::now()->subDays(153),
                'items'          => [
                    ['product' => 'dam-maxi-hoa-nhi-zara',       'color' => 'Hồng',     'size' => 'S', 'qty' => 1],
                    ['product' => 'ao-so-mi-nu-linen-zara',       'color' => 'Trắng',    'size' => 'M', 'qty' => 1],
                    ['product' => 'ao-croptop-nu-cuc-boc-hm',    'color' => 'Hồng',     'size' => 'S', 'qty' => 2],
                ],
            ],

            // ── Đơn 6: customer2 – completed & paid (ZaloPay) ───────────────────
            [
                'user'           => 'customer2@example.com',
                'address_index'  => 1,
                'payment_method' => 'ZaloPay',
                'order_code'     => 'ORD-20250310-00006',
                'phone'          => '0903000002',
                'note'           => 'Tặng quà sinh nhật, vui lòng gói đẹp.',
                'status'         => OrderStatus::COMPLETED,
                'payment_status' => PaymentStatus::PAID,
                'created_at'     => Carbon::now()->subDays(104),
                'items'          => [
                    ['product' => 'quan-jeans-nu-skinny-721-levis', 'color' => 'Đen',  'size' => 'S', 'qty' => 1],
                    ['product' => 'vay-tennis-nu-adidas',           'color' => 'Đen',  'size' => 'S', 'qty' => 1],
                ],
            ],

            // ── Đơn 7: customer2 – processing, paid (VNPay) ─────────────────────
            [
                'user'           => 'customer2@example.com',
                'address_index'  => 0,
                'payment_method' => 'VNPay',
                'order_code'     => 'ORD-20250610-00007',
                'phone'          => '0903000002',
                'note'           => null,
                'status'         => OrderStatus::PROCESSING,
                'payment_status' => PaymentStatus::PAID,
                'created_at'     => Carbon::now()->subDays(12),
                'items'          => [
                    ['product' => 'dam-body-midi-nu-zara',    'color' => 'Đen',  'size' => 'M', 'qty' => 1],
                    ['product' => 'ao-len-co-lo-nu-uniqlo',   'color' => 'Be',   'size' => 'S', 'qty' => 1],
                ],
            ],

            // ── Đơn 8: customer2 – pending (COD) ────────────────────────────────
            [
                'user'           => 'customer2@example.com',
                'address_index'  => 0,
                'payment_method' => 'Thanh toán khi nhận hàng (COD)',
                'order_code'     => 'ORD-20250620-00008',
                'phone'          => '0903000002',
                'note'           => null,
                'status'         => OrderStatus::PENDING,
                'payment_status' => PaymentStatus::UNPAID,
                'created_at'     => Carbon::now()->subDays(2),
                'items'          => [
                    ['product' => 'ao-thun-tron-unisex-uniqlo', 'color' => 'Hồng',    'size' => 'S', 'qty' => 3],
                ],
            ],

            // ── Đơn 9: customer3 – completed & paid (Momo) ──────────────────────
            [
                'user'           => 'customer3@example.com',
                'address_index'  => 0,
                'payment_method' => 'Momo',
                'order_code'     => 'ORD-20250205-00009',
                'phone'          => '0903000003',
                'note'           => null,
                'status'         => OrderStatus::COMPLETED,
                'payment_status' => PaymentStatus::PAID,
                'created_at'     => Carbon::now()->subDays(138),
                'items'          => [
                    ['product' => 'ao-phao-nu-ultra-light-uniqlo', 'color' => 'Đen', 'size' => 'M', 'qty' => 1],
                    ['product' => 'ao-len-co-lo-nu-uniqlo',        'color' => 'Xám', 'size' => 'M', 'qty' => 1],
                    ['product' => 'ao-thun-tron-unisex-uniqlo',    'color' => 'Đen', 'size' => 'L', 'qty' => 2],
                ],
            ],

            // ── Đơn 10: customer3 – completed & paid (Chuyển khoản) ─────────────
            [
                'user'           => 'customer3@example.com',
                'address_index'  => 1,
                'payment_method' => 'Chuyển khoản ngân hàng',
                'order_code'     => 'ORD-20250401-00010',
                'phone'          => '0903000003',
                'note'           => 'Sáng giao trước 10h.',
                'status'         => OrderStatus::COMPLETED,
                'payment_status' => PaymentStatus::PAID,
                'created_at'     => Carbon::now()->subDays(83),
                'items'          => [
                    ['product' => 'ao-sweatshirt-trefoil-adidas', 'color' => 'Đen', 'size' => 'L', 'qty' => 1],
                    ['product' => 'ao-khoac-bomber-nam-adidas',   'color' => 'Đen', 'size' => 'L', 'qty' => 1],
                ],
            ],

            // ── Đơn 11: customer3 – processing, paid (VNPay) ────────────────────
            [
                'user'           => 'customer3@example.com',
                'address_index'  => 0,
                'payment_method' => 'VNPay',
                'order_code'     => 'ORD-20250508-00011',
                'phone'          => '0903000003',
                'note'           => null,
                'status'         => OrderStatus::PROCESSING,
                'payment_status' => PaymentStatus::PAID,
                'created_at'     => Carbon::now()->subDays(45),
                'items'          => [
                    ['product' => 'ao-blazer-nam-slim-zara',   'color' => 'Xám', 'size' => 'L', 'qty' => 1],
                    ['product' => 'quan-tay-nam-slim-zara',     'color' => 'Đen', 'size' => 'L', 'qty' => 1],
                    ['product' => 'quan-short-the-thao-nam-nike', 'color' => 'Đen', 'size' => 'L', 'qty' => 2],
                ],
            ],

            // ── Đơn 12: customer3 – pending (COD) ───────────────────────────────
            [
                'user'           => 'customer3@example.com',
                'address_index'  => 0,
                'payment_method' => 'Thanh toán khi nhận hàng (COD)',
                'order_code'     => 'ORD-20250621-00012',
                'phone'          => '0903000003',
                'note'           => null,
                'status'         => OrderStatus::PENDING,
                'payment_status' => PaymentStatus::UNPAID,
                'created_at'     => Carbon::now()->subDays(1),
                'items'          => [
                    ['product' => 'ao-hoodie-classic-champion', 'color' => 'Đen',     'size' => 'L', 'qty' => 1],
                    ['product' => 'quan-jogger-tech-fleece-nike', 'color' => 'Xanh navy', 'size' => 'L', 'qty' => 1],
                ],
            ],
        ];

        foreach ($orders as $orderData) {
            // Bỏ qua nếu đã tồn tại
            if (Order::where('order_code', $orderData['order_code'])->exists()) {
                continue;
            }

            $user          = User::where('email', $orderData['user'])->first();
            $paymentMethod = PaymentMethod::where('name', $orderData['payment_method'])->first();

            if (! $user || ! $paymentMethod) {
                continue;
            }

            $address = Address::where('user_id', $user->id)
                ->orderBy('id')
                ->skip($orderData['address_index'])
                ->first();

            if (! $address) {
                continue;
            }

            // Tính tổng tiền từ các items
            $lineItems   = $this->resolveItems($orderData['items']);
            $subtotal    = collect($lineItems)->sum(fn ($i) => $i['unit_price'] * $i['qty']);
            $shippingFee = $subtotal >= 500000 ? 0 : 35000;

            $order = Order::create([
                'user_id'           => $user->id,
                'address_id'        => $address->id,
                'payment_method_id' => $paymentMethod->id,
                'order_code'        => $orderData['order_code'],
                'phone'             => $orderData['phone'],
                'note'              => $orderData['note'],
                'total_money'       => $subtotal + $shippingFee,
                'shipping_fee'      => $shippingFee,
                'status'            => $orderData['status']->value,
                'payment_status'    => $orderData['payment_status']->value,
                'created_at'        => $orderData['created_at'],
                'updated_at'        => $orderData['created_at'],
            ]);

            foreach ($lineItems as $item) {
                OrderItem::firstOrCreate(
                    [
                        'order_id'           => $order->id,
                        'product_variant_id' => $item['variant_id'],
                    ],
                    [
                        'unit_price' => $item['unit_price'],
                        'quantity'   => $item['qty'],
                    ]
                );
            }
        }
    }

    /** Tra cứu variant và tính đơn giá cho từng item. */
    private function resolveItems(array $items): array
    {
        $resolved = [];

        foreach ($items as $item) {
            $product = Product::where('slug', $item['product'])->first();
            $color   = Color::where('name', $item['color'])->first();
            $size    = Size::where('name', $item['size'])->first();

            if (! $product || ! $color || ! $size) {
                continue;
            }

            $variant = ProductVariant::where([
                'product_id' => $product->id,
                'color_id'   => $color->id,
                'size_id'    => $size->id,
            ])->first();

            if (! $variant) {
                continue;
            }

            $resolved[] = [
                'variant_id' => $variant->id,
                'unit_price' => $product->sale_price ?? $product->price,
                'qty'        => $item['qty'],
            ];
        }

        return $resolved;
    }
}
