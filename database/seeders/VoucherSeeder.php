<?php

namespace Database\Seeders;

use App\Models\Voucher;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class VoucherSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        $vouchers = [
            // 1. Vouchers percentage active
            [
                'code' => 'HELLOSUMMER',
                'type' => 'percentage',
                'value' => 10.00,
                'min_order_amount' => 200000.00,
                'max_discount_amount' => 50000.00,
                'quantity' => 100,
                'used_count' => 15,
                'start_date' => $now->copy()->subDays(5),
                'end_date' => $now->copy()->addDays(20),
                'status' => true,
                'description' => 'Mã giảm giá chào hè 10% cho đơn hàng từ 200k.',
            ],
            [
                'code' => 'FASHION50',
                'type' => 'percentage',
                'value' => 50.00,
                'min_order_amount' => 1000000.00,
                'max_discount_amount' => 300000.00,
                'quantity' => 50,
                'used_count' => 45,
                'start_date' => $now->copy()->subDays(10),
                'end_date' => $now->copy()->addDays(5),
                'status' => true,
                'description' => 'Mã giảm giá 50% cực khủng tối đa 300k.',
            ],
            [
                'code' => 'NEWUSER15',
                'type' => 'percentage',
                'value' => 15.00,
                'min_order_amount' => 0.00,
                'max_discount_amount' => 30000.00,
                'quantity' => 200,
                'used_count' => 88,
                'start_date' => $now->copy()->subDays(30),
                'end_date' => $now->copy()->addDays(60),
                'status' => true,
                'description' => 'Mã giảm giá 15% dành cho thành viên mới đăng ký.',
            ],
            [
                'code' => 'STUDENTGREEN',
                'type' => 'percentage',
                'value' => 12.00,
                'min_order_amount' => 150000.00,
                'max_discount_amount' => 40000.00,
                'quantity' => 150,
                'used_count' => 24,
                'start_date' => $now->copy()->subDays(2),
                'end_date' => $now->copy()->addDays(15),
                'status' => true,
                'description' => 'Mã giảm giá 12% cho học sinh sinh viên.',
            ],

            // 2. Vouchers fixed active
            [
                'code' => 'GIAM30K',
                'type' => 'fixed',
                'value' => 30000.00,
                'min_order_amount' => 300000.00,
                'max_discount_amount' => 0.00,
                'quantity' => 100,
                'used_count' => 12,
                'start_date' => $now->copy()->subDays(3),
                'end_date' => $now->copy()->addDays(10),
                'status' => true,
                'description' => 'Giảm ngay 30k cho đơn từ 300k.',
            ],
            [
                'code' => 'GIAM50K',
                'type' => 'fixed',
                'value' => 50000.00,
                'min_order_amount' => 500000.00,
                'max_discount_amount' => 0.00,
                'quantity' => 100,
                'used_count' => 50,
                'start_date' => $now->copy()->subDays(4),
                'end_date' => $now->copy()->addDays(12),
                'status' => true,
                'description' => 'Giảm ngay 50k cho đơn hàng từ 500k.',
            ],
            [
                'code' => 'GIAM100K',
                'type' => 'fixed',
                'value' => 100000.00,
                'min_order_amount' => 800000.00,
                'max_discount_amount' => 0.00,
                'quantity' => 80,
                'used_count' => 30,
                'start_date' => $now->copy()->subDays(6),
                'end_date' => $now->copy()->addDays(8),
                'status' => true,
                'description' => 'Giảm ngay 100k cho đơn hàng từ 800k.',
            ],
            [
                'code' => 'GIAM200K',
                'type' => 'fixed',
                'value' => 200000.00,
                'min_order_amount' => 1500000.00,
                'max_discount_amount' => 0.00,
                'quantity' => 30,
                'used_count' => 5,
                'start_date' => $now->copy()->subDays(1),
                'end_date' => $now->copy()->addDays(30),
                'status' => true,
                'description' => 'Giảm ngay 200k khi mua sắm thời trang đơn từ 1.5 triệu.',
            ],

            // 3. Vouchers sắp hết hạn (còn hạn dưới 3 ngày)
            [
                'code' => 'MIDNIGHTDEAL',
                'type' => 'percentage',
                'value' => 20.00,
                'min_order_amount' => 400000.00,
                'max_discount_amount' => 80000.00,
                'quantity' => 50,
                'used_count' => 42,
                'start_date' => $now->copy()->subDays(3),
                'end_date' => $now->copy()->addHours(12),
                'status' => true,
                'description' => 'Mã giảm giá chớp nhoáng 20%.',
            ],
            [
                'code' => 'FLASH30',
                'type' => 'percentage',
                'value' => 30.00,
                'min_order_amount' => 200000.00,
                'max_discount_amount' => 60000.00,
                'quantity' => 40,
                'used_count' => 38,
                'start_date' => $now->copy()->subDays(2),
                'end_date' => $now->copy()->addDays(1),
                'status' => true,
                'description' => 'Mã giảm giá Flash Sale 30%.',
            ],
            [
                'code' => 'QUICK10K',
                'type' => 'fixed',
                'value' => 10000.00,
                'min_order_amount' => 100000.00,
                'max_discount_amount' => 0.00,
                'quantity' => 100,
                'used_count' => 95,
                'start_date' => $now->copy()->subDays(5),
                'end_date' => $now->copy()->addDays(2),
                'status' => true,
                'description' => 'Giảm 10k đơn cực nhỏ sắp hết hạn.',
            ],

            // 4. Vouchers hết lượt dùng (depleted)
            [
                'code' => 'SOLDOutPERCENT',
                'type' => 'percentage',
                'value' => 15.00,
                'min_order_amount' => 100000.00,
                'max_discount_amount' => 20000.00,
                'quantity' => 50,
                'used_count' => 50,
                'start_date' => $now->copy()->subDays(10),
                'end_date' => $now->copy()->addDays(10),
                'status' => true,
                'description' => 'Mã giảm giá đã dùng hết lượt phát hành.',
            ],
            [
                'code' => 'SOLDOUT50K',
                'type' => 'fixed',
                'value' => 50000.00,
                'min_order_amount' => 300000.00,
                'max_discount_amount' => 0.00,
                'quantity' => 30,
                'used_count' => 30,
                'start_date' => $now->copy()->subDays(10),
                'end_date' => $now->copy()->addDays(10),
                'status' => true,
                'description' => 'Mã giảm giá 50k đã dùng hết lượt phát hành.',
            ],

            // 5. Vouchers hết hạn (expired)
            [
                'code' => 'EXPIRED20',
                'type' => 'percentage',
                'value' => 20.00,
                'min_order_amount' => 200000.00,
                'max_discount_amount' => 40000.00,
                'quantity' => 100,
                'used_count' => 45,
                'start_date' => $now->copy()->subDays(30),
                'end_date' => $now->copy()->subDays(5),
                'status' => true,
                'description' => 'Mã giảm giá đã hết thời gian sử dụng.',
            ],
            [
                'code' => 'EXPIRED100K',
                'type' => 'fixed',
                'value' => 100000.00,
                'min_order_amount' => 600000.00,
                'max_discount_amount' => 0.00,
                'quantity' => 50,
                'used_count' => 12,
                'start_date' => $now->copy()->subDays(20),
                'end_date' => $now->copy()->subDays(2),
                'status' => true,
                'description' => 'Mã giảm giá fixed đã quá hạn.',
            ],

            // 6. Vouchers chưa kích hoạt (chưa đến ngày start)
            [
                'code' => 'UPCOMING10',
                'type' => 'percentage',
                'value' => 10.00,
                'min_order_amount' => 200000.00,
                'max_discount_amount' => 30000.00,
                'quantity' => 100,
                'used_count' => 0,
                'start_date' => $now->copy()->addDays(5),
                'end_date' => $now->copy()->addDays(25),
                'status' => true,
                'description' => 'Mã giảm giá sắp tới chưa đến ngày bắt đầu.',
            ],
            [
                'code' => 'UPCOMING50K',
                'type' => 'fixed',
                'value' => 50000.00,
                'min_order_amount' => 400000.00,
                'max_discount_amount' => 0.00,
                'quantity' => 50,
                'used_count' => 0,
                'start_date' => $now->copy()->addDays(10),
                'end_date' => $now->copy()->addDays(20),
                'status' => true,
                'description' => 'Mã giảm giá fixed sắp diễn ra.',
            ],

            // 7. Vouchers bị tạm khóa (status = false)
            [
                'code' => 'PAUSED15',
                'type' => 'percentage',
                'value' => 15.00,
                'min_order_amount' => 300000.00,
                'max_discount_amount' => 60000.00,
                'quantity' => 100,
                'used_count' => 5,
                'start_date' => $now->copy()->subDays(5),
                'end_date' => $now->copy()->addDays(20),
                'status' => false,
                'description' => 'Mã giảm giá đang bị tạm ngưng hoạt động.',
            ],
            [
                'code' => 'PAUSED30K',
                'type' => 'fixed',
                'value' => 30000.00,
                'min_order_amount' => 200000.00,
                'max_discount_amount' => 0.00,
                'quantity' => 50,
                'used_count' => 2,
                'start_date' => $now->copy()->subDays(5),
                'end_date' => $now->copy()->addDays(20),
                'status' => false,
                'description' => 'Mã giảm giá cố định tạm dừng hoạt động.',
            ],

            // 8. Thêm voucher đặc biệt
            [
                'code' => 'GOLDENHOUR',
                'type' => 'percentage',
                'value' => 25.00,
                'min_order_amount' => 500000.00,
                'max_discount_amount' => 150000.00,
                'quantity' => 20,
                'used_count' => 8,
                'start_date' => $now->copy()->subDays(1),
                'end_date' => $now->copy()->addDays(3),
                'status' => true,
                'description' => 'Giờ vàng giảm giá 25% tối đa 150k.',
            ]
        ];

        foreach ($vouchers as $voucherData) {
            Voucher::updateOrCreate(
                ['code' => $voucherData['code']],
                collect($voucherData)->except('code')->all()
            );
        }
    }
}
