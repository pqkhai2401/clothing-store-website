<?php

namespace Database\Seeders;

use App\Models\Collection;
use App\Models\Product;
use Illuminate\Database\Seeder;

class CollectionProductSeeder extends Seeder
{
    /**
     * Lưới an toàn (safety net) cho bộ sưu tập.
     *
     * CollectionSeeder mới đã gán sản phẩm theo mùa một cách xác định.
     * Seeder này KHÔNG random đè lên nữa — nó chỉ bổ sung sản phẩm cho
     * những bộ sưu tập vô tình bị trống, để trang bộ sưu tập luôn có hàng.
     */
    public function run(): void
    {
        $productIds = Product::where('status', true)->pluck('id');

        if ($productIds->isEmpty()) {
            return;
        }

        Collection::withCount('products')->get()->each(function (Collection $collection) use ($productIds): void {
            if ($collection->products_count > 0) {
                return; // đã có sản phẩm, giữ nguyên
            }

            // attach (không detach) một ít sản phẩm ngẫu nhiên để không bị trống
            $count = min($productIds->count(), random_int(6, 10));
            $collection->products()->syncWithoutDetaching($productIds->random($count)->all());
        });
    }
}
