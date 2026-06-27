<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = collect(UserRole::cases())
            ->mapWithKeys(fn (UserRole $role) => [
                $role->value => Role::firstOrCreate(['name' => $role->value, 'guard_name' => 'web']),
            ]);

        $users = [
            [
                'username' => 'admin',
                'email' => 'admin@gmail.com',
                'password' => 'Admin@123',
                'phone_number' => '0123456789',
                'role' => UserRole::ADMIN,
                'is_active' => true,
            ],
            [
                'username' => 'Trần Hữu Minh Hiệp',
                'email' => '0306231289@caothang.edu.vn',
                'password' => 'MinhHiep@123',
                'phone_number' => '0357989856',
                'role' => UserRole::ADMIN,
                'is_active' => true,
            ],
            [
                'username' => 'Trần Hữu Minh Hiệp',
                'email' => 'hiep29042021@gmail.com',
                'password' => 'MinhHiep@123',
                'phone_number' => '0357989857',
                'role' => UserRole::ADMIN,
                'is_active' => true,
            ],
            [
                'username' => 'Phạm Quang Khải',
                'email' => '0306231295@caothang.edu.vn',
                'password' => 'QuangKhai@123',
                'phone_number' => '0949032437',
                'role' => UserRole::ADMIN,
                'is_active' => true,
            ],
            [
                'username' => 'Nguyễn Thị Thu Hằng',
                'email' => 'ThuHang@gmail.com',
                'password' => 'ThuHang@123',
                'phone_number' => '0938261547',
                'role' => UserRole::STAFF,
                'is_active' => true,
            ],
            [
                'username' => 'Võ Quốc Bảo',
                'email' => 'QuocBao@gmail.com',
                'password' => 'QuocBao@123',
                'phone_number' => '0974156382',
                'role' => UserRole::STAFF,
                'is_active' => true,
            ],
            [
                'username' => 'Nguyễn Thị Ngọc Hân',
                'email' => 'support.staff@gmail.com',
                'password' => 'NgocHan@123',
                'phone_number' => '0859362741',
                'role' => UserRole::STAFF,
                'is_active' => false,
            ],
            [
                'username' => 'T',
                'email' => 'support.staff@gmail.com',
                'password' => 'NgocHan@123',
                'phone_number' => '0859362741',
                'role' => UserRole::STAFF,
                'is_active' => false,
            ],
            [
                'username' => 'Nguyễn Văn Hiền',
                'email' => 'nguyenvanhien@gmail.com',
                'password' => 'Hien@2026',
                'phone_number' => '0962847315',
                'role' => UserRole::CUSTOMER,
                'is_active' => true,
            ],
            [
                'username' => 'Nguyễn Trung Tâm',
                'email' => 'nguyentrungtam@gmail.com',
                'password' => 'Tam@2026',
                'phone_number' => '0885173946',
                'role' => UserRole::CUSTOMER,
                'is_active' => true,
            ],
            [
                'username' => 'Nguyễn Thị Lan',
                'email' => 'nguyenthilan@gmail.com',
                'password' => 'Lan@2026',
                'phone_number' => '0773948261',
                'role' => UserRole::CUSTOMER,
                'is_active' => true,
            ],
            [
                'username' => 'Trần Thị Ngọc Mai',
                'email' => 'tranthingocmai@gmail.com',
                'password' => 'Mai@2026',
                'phone_number' => '0827516394',
                'role' => UserRole::CUSTOMER,
                'is_active' => true,
            ],
            [
                'username' => 'Phạm Thu Hương',
                'email' => 'phamthuhuong@gmail.com',
                'password' => 'Huong@2026',
                'phone_number' => '0946832175',
                'role' => UserRole::CUSTOMER,
                'is_active' => true,
            ],
            [
                'username' => 'Lê Minh Khánh',
                'email' => 'leminhkhanh@gmail.com',
                'password' => 'Khanh@2026',
                'phone_number' => '0358724619',
                'role' => UserRole::CUSTOMER,
                'is_active' => true,
            ],
            [
                'username' => 'Hoàng Gia Huy',
                'email' => 'hoanggiahuy@gmail.com',
                'password' => 'Huy@2026',
                'phone_number' => '0796245831',
                'role' => UserRole::CUSTOMER,
                'is_active' => true,
            ],
            [
                'username' => 'Bùi Anh Khoa',
                'email' => 'buianhkhoa@gmail.com',
                'password' => 'Khoa@2026',
                'phone_number' => '0569317482',
                'role' => UserRole::CUSTOMER,
                'is_active' => true,
            ],
            [
                'username' => 'Đỗ Minh Nhật',
                'email' => 'dominhnhat@gmail.com',
                'password' => 'Nhat@2026',
                'phone_number' => '0834619275',
                'role' => UserRole::CUSTOMER,
                'is_active' => true,
            ],
            [
                'username' => 'Võ Thanh Tùng',
                'email' => 'vothanhtung@gmail.com',
                'password' => 'Tung@2026',
                'phone_number' => '0927451836',
                'role' => UserRole::CUSTOMER,
                'is_active' => true,
            ],
            [
                'username' => 'Đặng Thùy Linh',
                'email' => 'dangthuylinh@gmail.com',
                'password' => 'Linh@2026',
                'phone_number' => '0378165924',
                'role' => UserRole::CUSTOMER,
                'is_active' => true,
            ],
            [
                'username' => 'Mai Quốc Việt',
                'email' => 'maiquocviet@gmail.com',
                'password' => 'Viet@2026',
                'phone_number' => '0785296413',
                'role' => UserRole::CUSTOMER,
                'is_active' => true,
            ],
            [
                'username' => 'Huỳnh Kim Ngân',
                'email' => 'huynhkimngan@gmail.com',
                'password' => 'Ngan@2026',
                'phone_number' => '0894167532',
                'role' => UserRole::CUSTOMER,
                'is_active' => true,
            ],
            [
                'username' => 'Trương Nhật Nam',
                'email' => 'truongnhatnam@gmail.com',
                'password' => 'Nam@2026',
                'phone_number' => '0347295816',
                'role' => UserRole::CUSTOMER,
                'is_active' => true,
            ],
            [
                'username' => 'Lý Thanh Vy',
                'email' => 'lythanhvy@gmail.com',
                'password' => 'Vy@2026',
                'phone_number' => '0816374925',
                'role' => UserRole::CUSTOMER,
                'is_active' => true,
            ],
            [
                'username' => 'Nguyễn Hoàng Phúc',
                'email' => 'nguyenhoangphuc@gmail.com',
                'password' => 'Phuc@2026',
                'phone_number' => '0704382169',
                'role' => UserRole::CUSTOMER,
                'is_active' => true,
            ],
            [
                'username' => 'Trần Bảo Ngọc',
                'email' => 'tranbaongoc@gmail.com',
                'password' => 'Ngoc@2026',
                'phone_number' => '0528193746',
                'role' => UserRole::CUSTOMER,
                'is_active' => true,
            ],
            [
                'username' => 'Customer Bị Khóa',
                'email' => 'locked.customer@gmail.com',
                'password' => 'Locked@2026',
                'phone_number' => '0395827461',
                'role' => UserRole::CUSTOMER,
                'is_active' => false,
            ],
        ];

        foreach ($users as $userData) {
            $user = User::updateOrCreate(
                ['email' => $userData['email']],
                [
                    'username' => $userData['username'],
                    'password' => Hash::make($userData['password']),
                    'phone_number' => $userData['phone_number'],
                    'is_active' => $userData['is_active'],
                    'email_verified_at' => now(),
                ],
            );

            $user->syncRoles([$userData['role']->value]);
        }
    }
}