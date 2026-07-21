<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            // ── Cơ sở ──────────────────────────
            RoleSeeder::class,
            UserSeeder::class,
            AddressSeeder::class,
            PaymentMethodSeeder::class,

            // ── Danh mục & Sản phẩm ────────────
            CategorySeeder::class,
            BrandSeeder::class,
            ColorSeeder::class,
            SizeSeeder::class,
            TagSeeder::class,
            ProductSeeder::class,

            // ── Tương tác người dùng ────────────
            OrderSeeder::class,
            ReviewSeeder::class,       // cần đơn hàng đã hoàn tất từ OrderSeeder
            WishlistSeeder::class,
            CollectionSeeder::class,
            VoucherSeeder::class,
            InventorySeeder::class,

            // ── Dữ liệu bổ sung (bộ sưu tập, hành vi, voucher) ──
            CollectionProductSeeder::class,   // gán sản phẩm vào bộ sưu tập
            ProductViewSeeder::class,         // lịch sử xem (cho AI gợi ý)
            SearchHistorySeeder::class,       // lịch sử tìm kiếm (cho AI gợi ý)
            VoucherHistorySeeder::class,      // lịch sử dùng voucher
        ]);
    }
}
