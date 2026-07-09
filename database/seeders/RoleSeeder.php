<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Tạo permissions
        $allPermissions = [
            'access-admin',       // Truy cập trang quản trị
            'view-dashboard',     // Xem dashboard 
            'manage-staff',       // Quản lý nhân sự (chỉ admin)
            'manage-customers',   // Quản lý khách hàng
            'manage-products',    // Quản lý sản phẩm
            'manage-goods-receipts', // Quản lý phiếu nhập kho
            'manage-categories',  // Quản lý danh mục
            'manage-brands',      // Quản lý thương hiệu
            'manage-colors',      // Quản lý màu sắc
            'manage-sizes',       // Quản lý kích thước
            'manage-orders',      // Quản lý đơn hàng
            'manage-reviews',     // Quản lý đánh giá
            'manage-revenue',     // Thống kê doanh thu
            'manage-suppliers',      // Quản lý nhà cung cấp
            'manage-vouchers',    // Quản lý voucher
            'manage-collections', // Quản lý bộ sưu tập
            'manage-logs',        // Nhật ký làm việc (chỉ admin)
        ];

        foreach ($allPermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Admin có TẤT CẢ permissions
        $admin = Role::firstOrCreate(['name' => UserRole::ADMIN->value, 'guard_name' => 'web']);
        $admin->syncPermissions($allPermissions);

        // Staff có tất cả TRỪ manage-staff và manage-logs (nhật ký chỉ dành cho admin)
        $staff = Role::firstOrCreate(['name' => UserRole::STAFF->value, 'guard_name' => 'web']);
        $staffPermissions = array_values(array_filter(
            $allPermissions,
            fn ($p) => ! in_array($p, ['manage-staff', 'manage-logs'], true)
        ));
        $staff->syncPermissions($staffPermissions);

        // Customer không có permission nào trên admin panel
        $customer = Role::firstOrCreate(['name' => UserRole::CUSTOMER->value, 'guard_name' => 'web']);
        $customer->syncPermissions([]);
    }
}