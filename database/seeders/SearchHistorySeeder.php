<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Brand;
use App\Models\Category;
use App\Models\SearchHistory;
use App\Models\User;
use Illuminate\Database\Seeder;

class SearchHistorySeeder extends Seeder
{
    /**
     * Sinh lịch sử tìm kiếm cho khách hàng (bảng search_histories).
     * Bổ trợ AI gợi ý cho user đã đăng nhập (theo từ khóa đã tìm).
     */
    public function run(): void
    {
        $customers = User::role(UserRole::CUSTOMER->value)->get();

        if ($customers->isEmpty()) {
            return;
        }

        // Nguồn từ khóa: tên danh mục + tên thương hiệu + một số từ khóa phổ biến.
        $keywords = collect()
            ->merge(Category::pluck('name'))
            ->merge(Brand::pluck('name'))
            ->merge(['áo thun', 'quần jean', 'áo khoác', 'váy', 'sơ mi', 'hoodie', 'đồ thể thao', 'giảm giá'])
            ->filter()
            ->unique()
            ->values();

        if ($keywords->isEmpty()) {
            return;
        }

        $rows = [];

        foreach ($customers as $customer) {
            // Mỗi khách có 2–8 lượt tìm kiếm gần đây.
            $count = random_int(2, 8);

            for ($i = 0; $i < $count; $i++) {
                $at = now()->subDays(random_int(0, 30))->subMinutes(random_int(0, 1439));
                $rows[] = [
                    'user_id'    => $customer->id,
                    'keyword'    => (string) $keywords->random(),
                    'created_at' => $at,
                    'updated_at' => $at,
                ];
            }
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            SearchHistory::insert($chunk);
        }
    }
}
