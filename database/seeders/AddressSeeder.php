<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\User;
use Illuminate\Database\Seeder;

class AddressSeeder extends Seeder
{
    /**
     * Địa chỉ giao hàng gán cho từng user theo email. Đa số khách hàng thật có
     * ít nhất 1 địa chỉ nhà (mặc định) và một phần có thêm địa chỉ công ty/nơi
     * khác — phản ánh đúng hành vi lưu nhiều địa chỉ giao hàng trên các sàn TMĐT.
     */
    public function run(): void
    {
        $addressData = [
            'admin@gmail.com' => [
                ['city' => 'TP. Hồ Chí Minh', 'ward' => 'Phường Sài Gòn', 'apartment_number' => '45 Lê Duẩn'],
            ],
            'hiep29042021@gmail.com' => [
                ['city' => 'TP. Hồ Chí Minh', 'ward' => 'Phường Sài Gòn', 'apartment_number' => '120 Nguyễn Huệ'],
            ],
            '0306231295@caothang.edu.vn' => [
                ['city' => 'TP. Hồ Chí Minh', 'ward' => 'Phường Tăng Nhơn Phú', 'apartment_number' => '29 Lê Văn Việt'],
            ],
            'ThuHang@gmail.com' => [
                ['city' => 'TP. Hồ Chí Minh', 'ward' => 'Phường Tân Định', 'apartment_number' => '212 Nguyễn Văn Nguyễn'],
            ],
            'QuocBao@gmail.com' => [
                ['city' => 'TP. Hồ Chí Minh', 'ward' => 'Phường Long Bình', 'apartment_number' => '325 Nguyễn Văn Tăng'],
            ],
            'support.staff@gmail.com' => [
                ['city' => 'TP. Hồ Chí Minh', 'ward' => 'Phường Bến Thành', 'apartment_number' => '88 Cách Mạng Tháng Tám'],
            ],

            'nguyenvanhien@gmail.com' => [
                ['city' => 'TP. Hồ Chí Minh', 'ward' => 'Phường Bến Thành', 'apartment_number' => '135 Lý Tự Trọng'],
                ['city' => 'TP. Hồ Chí Minh', 'ward' => 'Phường Sài Gòn', 'apartment_number' => '72 Pasteur, Tòa nhà Bitexco (Văn phòng công ty)'],
            ],
            'nguyentrungtam@gmail.com' => [
                ['city' => 'TP. Hồ Chí Minh', 'ward' => 'Phường Cầu Ông Lãnh', 'apartment_number' => '56 Võ Văn Kiệt'],
            ],
            'nguyenthilan@gmail.com' => [
                ['city' => 'TP. Hà Nội', 'ward' => 'Phường Hoàn Kiếm', 'apartment_number' => '45 Đinh Tiên Hoàng'],
                ['city' => 'TP. Hà Nội', 'ward' => 'Phường Cửa Nam', 'apartment_number' => '18 Tràng Thi, Chung cư Sun Grand City'],
            ],
            'tranthingocmai@gmail.com' => [
                ['city' => 'TP. Hà Nội', 'ward' => 'Phường Cửa Nam', 'apartment_number' => '21 Tràng Thi'],
            ],
            'phamthuhuong@gmail.com' => [
                ['city' => 'TP. Hà Nội', 'ward' => 'Phường Giảng Võ', 'apartment_number' => '88 Giảng Võ'],
            ],
            'leminhkhanh@gmail.com' => [
                ['city' => 'TP. Hà Nội', 'ward' => 'Phường Cầu Giấy', 'apartment_number' => '136 Xuân Thủy'],
                ['city' => 'TP. Hà Nội', 'ward' => 'Phường Dịch Vọng', 'apartment_number' => 'Tòa CT2, KĐT Trung Hòa Nhân Chính (Văn phòng công ty)'],
            ],
            'hoanggiahuy@gmail.com' => [
                ['city' => 'TP. Đà Nẵng', 'ward' => 'Phường Hải Châu', 'apartment_number' => '78 Hoàng Diệu'],
            ],
            'buianhkhoa@gmail.com' => [
                ['city' => 'TP. Đà Nẵng', 'ward' => 'Phường An Hải', 'apartment_number' => '35 Ngô Quyền'],
            ],
            'dominhnhat@gmail.com' => [
                ['city' => 'TP. Đà Nẵng', 'ward' => 'Phường Ngũ Hành Sơn', 'apartment_number' => '102 Lê Văn Hiến'],
                ['city' => 'TP. Đà Nẵng', 'ward' => 'Phường Hải Châu', 'apartment_number' => '20 Bạch Đằng (Văn phòng công ty)'],
            ],
            'vothanhtung@gmail.com' => [
                ['city' => 'TP. Đà Nẵng', 'ward' => 'Phường Sơn Trà', 'apartment_number' => '58 Võ Nguyên Giáp'],
            ],
            'dangthuylinh@gmail.com' => [
                ['city' => 'TP. Hồ Chí Minh', 'ward' => 'Phường Tân Định', 'apartment_number' => '189 Hai Bà Trưng'],
            ],
            'maiquocviet@gmail.com' => [
                ['city' => 'TP. Hồ Chí Minh', 'ward' => 'Phường Tăng Nhơn Phú', 'apartment_number' => '450 Lê Văn Việt'],
                ['city' => 'TP. Hồ Chí Minh', 'ward' => 'Phường Long Bình', 'apartment_number' => '15 Đỗ Xuân Hợp (Nhà bố mẹ)'],
            ],
            'huynhkimngan@gmail.com' => [
                ['city' => 'TP. Hồ Chí Minh', 'ward' => 'Phường Long Bình', 'apartment_number' => '90 Đường 4A'],
            ],
            'truongnhatnam@gmail.com' => [
                ['city' => 'TP. Cần Thơ', 'ward' => 'Phường Ninh Kiều', 'apartment_number' => '30 Hai Bà Trưng'],
            ],
            'lythanhvy@gmail.com' => [
                ['city' => 'TP. Cần Thơ', 'ward' => 'Phường Cái Khế', 'apartment_number' => '12 Trần Văn Khéo'],
                ['city' => 'TP. Cần Thơ', 'ward' => 'Phường Ninh Kiều', 'apartment_number' => '85 Nguyễn Trãi (Văn phòng công ty)'],
            ],
            'nguyenhoangphuc@gmail.com' => [
                ['city' => 'TP. Hà Nội', 'ward' => 'Phường Ba Đình', 'apartment_number' => '25 Kim Mã'],
            ],
            'tranbaongoc@gmail.com' => [
                ['city' => 'TP. Hà Nội', 'ward' => 'Phường Tây Hồ', 'apartment_number' => '68 Lạc Long Quân'],
            ],
            'locked.customer@gmail.com' => [
                ['city' => 'TP. Hồ Chí Minh', 'ward' => 'Phường Bến Thành', 'apartment_number' => '99 Nguyễn Trãi'],
            ],
        ];

        foreach ($addressData as $email => $addresses) {
            $user = User::where('email', $email)->first();
            if (! $user) {
                continue;
            }

            foreach ($addresses as $index => $addr) {
                Address::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'apartment_number' => $addr['apartment_number'],
                    ],
                    [
                        'city' => $addr['city'],
                        'ward' => $addr['ward'],
                        'is_default' => $index === 0,
                    ]
                );
            }
        }
    }
}
