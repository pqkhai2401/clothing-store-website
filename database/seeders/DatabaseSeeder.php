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
            ReviewSeeder::class,
            WishlistSeeder::class,
            OrderSeeder::class,
            CollectionSeeder::class,
            VoucherSeeder::class,
        ]);
    }
}
