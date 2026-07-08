<?php

namespace Database\Seeders;

use App\Models\Collection;
use App\Models\Product;
use Illuminate\Database\Seeder;

class CollectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $collectionsData = [
            [
                'name' => 'Bộ sưu tập Xuân',
                'slug' => 'bst-xuan',
                'description' => 'Khởi đầu năm mới đầy hứng khởi với những thiết kế sơ mi, polo thanh lịch, chất liệu nhẹ nhàng mang sắc màu tươi sáng của mùa xuân.',
                'banner' => 'images/collection_spring.png',
                'status' => true,
            ],
            [
                'name' => 'Bộ sưu tập Hạ',
                'slug' => 'bst-ha',
                'description' => 'Tận hưởng những ngày nắng rực rỡ với áo thun cotton mát lạnh, quần shorts năng động và chân váy bay bổng mang phong cách phóng khoáng.',
                'banner' => 'images/collection_summer.png',
                'status' => true,
            ],
            [
                'name' => 'Bộ sưu tập Thu',
                'slug' => 'bst-thu',
                'description' => 'Nét giao mùa dịu nhẹ cùng những chiếc cardigan, blazer len mỏng tinh tế và quần tây dáng suông thời thượng cho buổi chiều lộng gió.',
                'banner' => 'images/collection_autumn.png',
                'status' => true,
            ],
            [
                'name' => 'Bộ sưu tập Đông',
                'slug' => 'bst-dong',
                'description' => 'Giữ ấm cơ thể nhưng vẫn tôn vinh cá tính thời trang cùng áo khoác măng tô oversized, hoodie nỉ dày dặn và quần jeans cổ điển.',
                'banner' => 'images/collection_winter.png',
                'status' => true,
            ],
        ];

        $collections = [];
        foreach ($collectionsData as $data) {
            $collections[$data['slug']] = Collection::updateOrCreate(
                ['slug' => $data['slug']],
                [
                    'name' => $data['name'],
                    'description' => $data['description'],
                    'banner' => $data['banner'],
                    'status' => $data['status'],
                ]
            );
        }

        // Phân bổ sản phẩm vào các BST mùa dựa trên category_slug hoặc tags
        $products = Product::with(['category', 'tags'])->get();

        foreach ($products as $product) {
            $categorySlug = $product->category->slug ?? '';
            $tagSlugs = $product->tags->pluck('slug')->toArray();

            $assignedCollections = [];

            // 1. Phân loại BST Xuân
            if (
                str_contains($categorySlug, 'so-mi') ||
                str_contains($categorySlug, 'polo') ||
                in_array('hang-moi', $tagSlugs) ||
                in_array('casual', $tagSlugs)
            ) {
                $assignedCollections[] = $collections['bst-xuan']->id;
            }

            // 2. Phân loại BST Hạ
            if (
                str_contains($categorySlug, 'ao-thun') ||
                str_contains($categorySlug, 'short') ||
                str_contains($categorySlug, 'dam') ||
                str_contains($categorySlug, 'vay') ||
                in_array('mua-he', $tagSlugs)
            ) {
                $assignedCollections[] = $collections['bst-ha']->id;
            }

            // 3. Phân loại BST Thu
            if (
                str_contains($categorySlug, 'blazer') ||
                str_contains($categorySlug, 'quan-tay') ||
                (str_contains($categorySlug, 'len') && !str_contains($categorySlug, 'nu-ao-len')) || // Áo len mỏng
                in_array('xu-huong', $tagSlugs)
            ) {
                $assignedCollections[] = $collections['bst-thu']->id;
            }

            // 4. Phân loại BST Đông
            if (
                str_contains($categorySlug, 'khoac') ||
                str_contains($categorySlug, 'hoodie') ||
                str_contains($categorySlug, 'nu-ao-len') ||
                str_contains($categorySlug, 'jeans') ||
                in_array('mua-dong', $tagSlugs)
            ) {
                $assignedCollections[] = $collections['bst-dong']->id;
            }

            // Nếu không khớp quy tắc nào thì gán ngẫu nhiên vào Xuân hoặc Hạ để tránh bị trống
            if (empty($assignedCollections)) {
                $randomSlug = rand(0, 1) ? 'bst-xuan' : 'bst-ha';
                $assignedCollections[] = $collections[$randomSlug]->id;
            }

            $product->collections()->sync($assignedCollections);
        }
    }
}
