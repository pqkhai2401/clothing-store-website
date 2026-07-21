<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Product;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Database\Seeder;

/**
 * Mỗi khách hàng có 0-6 sản phẩm yêu thích ngẫu nhiên trong số sản phẩm đang
 * bán — phản ánh đúng việc không phải khách nào cũng dùng tính năng wishlist.
 */
class WishlistSeeder extends Seeder
{
    public function run(): void
    {
        if (Wishlist::count() > 0) {
            return;
        }

        $customers = User::role(UserRole::CUSTOMER->value)->get();
        $productIds = Product::where('status', true)->pluck('id');

        if ($customers->isEmpty() || $productIds->isEmpty()) {
            return;
        }

        foreach ($customers as $customer) {
            // ~20% khách chưa từng dùng wishlist.
            if (random_int(1, 100) <= 20) {
                continue;
            }

            $count = min($productIds->count(), random_int(2, 6));

            foreach ($productIds->random($count) as $productId) {
                Wishlist::firstOrCreate([
                    'user_id' => $customer->id,
                    'product_id' => $productId,
                ]);
            }
        }
    }
}
