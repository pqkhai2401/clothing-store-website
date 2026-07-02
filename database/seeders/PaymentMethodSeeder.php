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
                'name' => 'VNPay',
                'status' => true,
                'image' => 'https://cdn.haitrieu.com/wp-content/uploads/2022/10/Logo-VNPAY-QR.png',
            ],
            [
                'name' => 'Momo',
                'status' => true,
                'image' => 'https://cdn.haitrieu.com/wp-content/uploads/2022/10/Logo-MoMo-Square.png',
            ],
            [
                'name' => 'ZaloPay',
                'status' => true,
                'image' => 'https://cdn.haitrieu.com/wp-content/uploads/2022/01/Logo-ZaloPay-Square.png',
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
    }
}
