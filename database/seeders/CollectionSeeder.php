<?php

namespace Database\Seeders;

use App\Models\Collection;
use App\Models\Product;
use Illuminate\Database\Seeder;

class CollectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Đây là seeder CHỦ ĐẠO gán sản phẩm vào 4 bộ sưu tập theo mùa.
     * Ưu tiên gán theo tag mùa (mua-xuan/mua-he/mua-thu/mua-dong),
     * sau đó tới quy tắc theo danh mục. Sản phẩm không khớp sẽ được
     * phân bổ dàn đều để không bộ sưu tập nào bị trống.
     */
    public function run(): void
    {
        $collectionsData = [
            [
                'name' => 'Bộ sưu tập Xuân',
                'slug' => 'bst-xuan',
                'description' => 'Khởi đầu năm mới đầy hứng khởi với những thiết kế sơ mi, polo thanh lịch, chất liệu nhẹ nhàng mang sắc màu tươi sáng của mùa xuân.',
                'banner' => 'https://images.unsplash.com/photo-1487222477894-8943e31ef7b2?w=1200&h=800&fit=crop&q=80',
                'status' => true,
            ],
            [
                'name' => 'Bộ sưu tập Hạ',
                'slug' => 'bst-ha',
                'description' => 'Tận hưởng những ngày nắng rực rỡ với áo thun cotton mát lạnh, quần shorts năng động và chân váy bay bổng mang phong cách phóng khoáng.',
                'banner' => 'https://images.unsplash.com/photo-1539109136881-3be0616acf4b?w=1200&h=800&fit=crop&q=80',
                'status' => true,
            ],
            [
                'name' => 'Bộ sưu tập Thu',
                'slug' => 'bst-thu',
                'description' => 'Nét giao mùa dịu nhẹ cùng những chiếc cardigan, blazer len mỏng tinh tế và quần tây dáng suông thời thượng cho buổi chiều lộng gió.',
                'banner' => 'https://images.unsplash.com/photo-1507679799987-c73779587ccf?w=1200&h=800&fit=crop&q=80',
                'status' => true,
            ],
            [
                'name' => 'Bộ sưu tập Đông',
                'slug' => 'bst-dong',
                'description' => 'Giữ ấm cơ thể nhưng vẫn tôn vinh cá tính thời trang cùng áo khoác măng tô oversized, hoodie nỉ dày dặn và quần jeans cổ điển.',
                'banner' => 'https://images.unsplash.com/photo-1547721064-da6cfb341d50?w=1200&h=800&fit=crop&q=80',
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

        // Phân bổ sản phẩm vào các BST mùa dựa trên tag mùa hoặc category_slug
        $products = Product::with(['category', 'tags'])->get();

        // Đếm số sản phẩm mỗi mùa để cân bằng khi phân bổ ngẫu nhiên phần dư
        $bucketCounts = ['bst-xuan' => 0, 'bst-ha' => 0, 'bst-thu' => 0, 'bst-dong' => 0];
        $assignments  = []; // product_id => [collection_id, ...]

        foreach ($products as $product) {
            $categorySlug = $product->category->slug ?? '';
            $tagSlugs     = $product->tags->pluck('slug')->toArray();

            $seasons = [];

            // 1. BST Xuân — ưu tiên tag mùa xuân, sau đó sơ mi/polo/hàng mới
            if (
                in_array('mua-xuan', $tagSlugs) ||
                str_contains($categorySlug, 'so-mi') ||
                str_contains($categorySlug, 'polo')
            ) {
                $seasons[] = 'bst-xuan';
            }

            // 2. BST Hạ — tag mùa hè, hoặc áo thun/short/đầm/váy
            if (
                in_array('mua-he', $tagSlugs) ||
                str_contains($categorySlug, 'ao-thun') ||
                str_contains($categorySlug, 'short') ||
                str_contains($categorySlug, 'dam') ||
                str_contains($categorySlug, 'vay')
            ) {
                $seasons[] = 'bst-ha';
            }

            // 3. BST Thu — tag mùa thu, hoặc blazer/quần tây/áo len
            if (
                in_array('mua-thu', $tagSlugs) ||
                str_contains($categorySlug, 'blazer') ||
                str_contains($categorySlug, 'quan-tay') ||
                str_contains($categorySlug, 'ao-len')
            ) {
                $seasons[] = 'bst-thu';
            }

            // 4. BST Đông — tag mùa đông, hoặc áo khoác/hoodie/jeans
            if (
                in_array('mua-dong', $tagSlugs) ||
                str_contains($categorySlug, 'khoac') ||
                str_contains($categorySlug, 'hoodie') ||
                str_contains($categorySlug, 'jeans')
            ) {
                $seasons[] = 'bst-dong';
            }

            // Không khớp quy tắc nào -> gán vào mùa đang có ít sản phẩm nhất (cân bằng)
            if (empty($seasons)) {
                asort($bucketCounts);
                $seasons[] = array_key_first($bucketCounts);
            }

            $seasons = array_unique($seasons);
            foreach ($seasons as $slug) {
                $bucketCounts[$slug]++;
            }

            $assignments[$product->id] = array_map(
                fn ($slug) => $collections[$slug]->id,
                $seasons
            );
        }

        foreach ($assignments as $productId => $collectionIds) {
            Product::find($productId)?->collections()->sync($collectionIds);
        }
    }
}
