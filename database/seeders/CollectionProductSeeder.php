<?php

namespace Database\Seeders;

use App\Models\Collection;
use App\Models\Product;
use Illuminate\Database\Seeder;

class CollectionProductSeeder extends Seeder
{
    /**
     * Gán sản phẩm vào từng bộ sưu tập (bảng pivot collection_product).
     * Mỗi bộ sưu tập nhận một tập sản phẩm ngẫu nhiên để trang bộ sưu tập có hàng hiển thị.
     */
    public function run(): void
    {
        $collections = Collection::all();
        $productIds  = Product::pluck('id');

        if ($collections->isEmpty() || $productIds->isEmpty()) {
            return;
        }

        foreach ($collections as $collection) {
            // Mỗi bộ sưu tập gồm 6–12 sản phẩm ngẫu nhiên (không trùng).
            $count = min($productIds->count(), random_int(6, 12));
            $pick  = $productIds->random($count);
            $pick  = $pick instanceof \Illuminate\Support\Collection ? $pick : collect([$pick]);

            // sync (thay vì attach) để chạy lại seeder không tạo lỗi khóa trùng.
            $collection->products()->sync($pick->all());
        }
    }
}
