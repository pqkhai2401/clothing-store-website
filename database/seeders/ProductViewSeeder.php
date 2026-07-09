<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Product;
use App\Models\ProductView;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProductViewSeeder extends Seeder
{
    /**
     * Sinh lịch sử xem sản phẩm cho khách hàng (bảng product_views).
     * Dữ liệu này là đầu vào cho AI gợi ý (content-based / theo hành vi xem).
     */
    public function run(): void
    {
        $customers  = User::role(UserRole::CUSTOMER->value)->get();
        $productIds = Product::where('status', true)->pluck('id');

        if ($customers->isEmpty() || $productIds->isEmpty()) {
            return;
        }

        $rows = [];

        foreach ($customers as $customer) {
            // Mỗi khách xem 5–20 lượt, rải rác trong 45 ngày gần đây.
            $viewCount = random_int(5, 20);

            for ($i = 0; $i < $viewCount; $i++) {
                $rows[] = [
                    'user_id'    => $customer->id,
                    'product_id' => $productIds->random(),
                    'viewed_at'  => now()
                        ->subDays(random_int(0, 45))
                        ->subMinutes(random_int(0, 1439)),
                ];
            }
        }

        // Chèn theo lô để nhẹ DB.
        foreach (array_chunk($rows, 500) as $chunk) {
            ProductView::insert($chunk);
        }
    }
}
