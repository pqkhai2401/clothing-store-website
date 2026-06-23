<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\User;
use Illuminate\Database\Seeder;

class AddressSeeder extends Seeder
{
    public function run(): void
    {
        // Địa chỉ gán cho từng customer theo email
        $addressData = [
            'customer1@example.com' => [
                [
                    'city'             => 'TP. Hồ Chí Minh',
                    'district'         => 'Quận 1',
                    'ward'             => 'Phường Bến Nghé',
                    'apartment_number' => '123 Nguyễn Huệ',
                ],
                [
                    'city'             => 'TP. Hồ Chí Minh',
                    'district'         => 'Thành phố Thủ Đức',
                    'ward'             => 'Phường Long Thạnh Mỹ',
                    'apartment_number' => '456 Lê Văn Việt',
                ],
            ],
            'customer2@example.com' => [
                [
                    'city'             => 'Đà Nẵng',
                    'district'         => 'Quận Ngũ Hành Sơn',
                    'ward'             => 'Phường Mỹ An',
                    'apartment_number' => '78 Hoàng Diệu',
                ],
                [
                    'city'             => 'Đà Nẵng',
                    'district'         => 'Quận Hải Châu',
                    'ward'             => 'Phường Phước Ninh',
                    'apartment_number' => '12 Trần Phú',
                ],
            ],
            'customer3@example.com' => [
                [
                    'city'             => 'Hà Nội',
                    'district'         => 'Quận Ba Đình',
                    'ward'             => 'Phường Phúc Xá',
                    'apartment_number' => '45 Đinh Tiên Hoàng',
                ],
                [
                    'city'             => 'Hà Nội',
                    'district'         => 'Quận Cầu Giấy',
                    'ward'             => 'Phường Quan Hoa',
                    'apartment_number' => '88 Cầu Giấy',
                ],
            ],
            'manager@example.com' => [
                [
                    'city'             => 'TP. Hồ Chí Minh',
                    'district'         => 'Quận Bình Thạnh',
                    'ward'             => 'Phường 22',
                    'apartment_number' => '200 Điện Biên Phủ',
                ],
            ],
        ];

        foreach ($addressData as $email => $addresses) {
            $user = User::where('email', $email)->first();
            if (! $user) {
                continue;
            }

            foreach ($addresses as $addr) {
                Address::firstOrCreate(
                    [
                        'user_id'          => $user->id,
                        'apartment_number' => $addr['apartment_number'],
                    ],
                    [
                        'city'     => $addr['city'],
                        'district' => $addr['district'],
                        'ward'     => $addr['ward'],
                    ]
                );
            }
        }
    }
}
