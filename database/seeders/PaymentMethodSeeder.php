<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    public function run(): void
    {
        $methods = [
            [
                'name' => 'Thanh toán khi nhận hàng (COD)',
                'status' => true,
                'image' => 'https://cdn-icons-png.flaticon.com/512/2331/2331941.png',
            ],
            [
                'name' => 'Chuyển khoản ngân hàng',
                'status' => true,
                'image' => 'https://cdn-icons-png.flaticon.com/512/2168/2168252.png',
            ],
            [
                'name' => 'PayOS - Quét mã QR',
                'status' => true,
                'image' => 'https://payos.vn/docs/img/logo.svg',
            ],
        ];

        foreach ($methods as $method) {
            PaymentMethod::updateOrCreate(
                ['name' => $method['name']],
                [
                    'status' => $method['status'],
                    'image' => $method['image'],
                ]
            );
        }

        // Ẩn phương thức VNPay cũ (đã thay bằng PayOS) nếu còn tồn tại.
        PaymentMethod::where('name', 'VNPay')->update(['status' => false]);
    }
}