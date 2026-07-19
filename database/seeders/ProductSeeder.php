<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Color;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\Size;
use App\Models\Tag;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    private array $brandAbbr = [
        'Nike'           => 'NIK',
        'Adidas'         => 'ADI',
        'Zara'           => 'ZAR',
        'H&M'            => 'HNM',
        'Uniqlo'         => 'UNI',
        "Levi's"         => 'LEV',
        'Champion'       => 'CHA',
        'The North Face' => 'TNF',
        'Puma'           => 'PUM',
        'New Balance'    => 'NB',
        'MLB'            => 'MLB',
        'Converse'       => 'CNV',
    ];

    private array $colorAbbr = [
        'Đen'        => 'BLK',
        'Trắng'      => 'WHT',
        'Xám'        => 'GRY',
        'Xanh navy'  => 'NVY',
        'Xanh dương' => 'BLU',
        'Đỏ'         => 'RED',
        'Xanh lá'    => 'GRN',
        'Vàng'       => 'YLW',
        'Cam'        => 'ORG',
        'Hồng'       => 'PNK',
        'Tím'        => 'PRP',
        'Nâu'        => 'BRN',
        'Be'         => 'BGE',
        'Xanh rêu'   => 'OLV',
    ];

    public function run(): void
    {
        $categories = Category::pluck('id', 'slug');
        $brands     = Brand::pluck('id', 'name');
        $colors     = Color::pluck('id', 'name');
        $sizes      = Size::pluck('id', 'name');
        $tags       = Tag::pluck('id', 'slug');

        $clothingSizeNames = ['XS', 'S', 'M', 'L', 'XL', 'XXL'];
        $clothingSizes = $sizes->only($clothingSizeNames);

        $products = $this->getProductData();

        foreach ($products as $index => $data) {
            $productNum = str_pad($index + 1, 3, '0', STR_PAD_LEFT);
            $brandCode  = $this->brandAbbr[$data['brand']] ?? 'GEN';

            $product = Product::firstOrCreate(
                ['slug' => $data['slug']],
                [
                    'name'        => $data['name'],
                    'description' => $data['description'],
                    'discount_type' => ! empty($data['discount']) ? 'percent' : null,
                    'discount_value' => $data['discount'] ?? 0,
                    'thumbnail'   => $data['thumbnail'],
                    'category_id' => $categories[$data['category']] ?? null,
                    'brand_id'    => $brands[$data['brand']] ?? null,
                    'gender'      => $data['gender'],
                    'views_count' => $data['views_count'],
                    'is_featured' => $data['is_featured'],
                    'status'      => true,
                ]
            );

            if ($product->wasRecentlyCreated) {
                foreach ($data['images'] as $imageUrl) {
                    ProductImage::create([
                        'product_id' => $product->id,
                        'image'      => $imageUrl,
                    ]);
                }

                $tagIds = collect($data['tags'])
                    ->map(fn ($slug) => $tags[$slug] ?? null)
                    ->filter()
                    ->values()
                    ->toArray();
                $product->tags()->sync($tagIds);

                foreach ($data['colors'] as $colorName) {
                    $colorId   = $colors[$colorName] ?? null;
                    $colorCode = $this->colorAbbr[$colorName] ?? 'UNK';

                    if (! $colorId) {
                        continue;
                    }

                    foreach ($data['sizes'] as $sizeName) {
                        if (! $clothingSizes->has($sizeName)) {
                            continue;
                        }

                        $sizeId = $clothingSizes[$sizeName] ?? null;

                        if (! $sizeId) {
                            continue;
                        }

                        $sku = "{$brandCode}{$productNum}-{$colorCode}-{$sizeName}";

                        ProductVariant::firstOrCreate(
                            ['sku' => $sku],
                            [
                                'product_id' => $product->id,
                                'color_id'   => $colorId,
                                'size_id'    => $sizeId,
                                // Tồn kho & giá vốn KHÔNG được gán trực tiếp ở đây — chúng chỉ do
                                // Kho quản lý qua Lô hàng (ProductBatch). InventorySeeder sẽ tạo
                                // phiếu nhập tồn đầu kỳ thật (qua InventoryBatchService::receive())
                                // cho mọi biến thể còn stock=0 sau bước này.
                                'stock'      => 0,
                                'image'      => $data['thumbnail'],
                                'cost_price' => 0,
                                'price'      => round($data['price'], 2),
                                'status'     => 'Active',
                            ]
                        );
                    }
                }
            }
        }
    }

    private function getProductData(): array
    {
        return [
            // ═══════════════════════════════════════════════════════════════════
            // NAM — ÁO THUN
            // ═══════════════════════════════════════════════════════════════════
            [
                'name'        => 'Áo Thun Nam Basic Tee',
                'slug'        => 'ao-thun-nam-basic-uniqlo',
                'description' => 'Áo thun nam basic từ Uniqlo làm từ chất liệu cotton 100% cao cấp, mềm mại và thoáng khí. Thiết kế đơn giản với cổ tròn, phù hợp mặc hàng ngày hoặc phối layering. Vải được xử lý chống co rút, giữ form tốt sau nhiều lần giặt.',
                'price'       => 199000,
                'thumbnail'   => 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?w=800&h=1000&fit=crop&q=80',
                'category'    => 'nam-ao-thun',
                'brand'       => 'Uniqlo',
                'gender'      => 'men',
                'is_featured' => true,
                'views_count' => 482,
                'images'      => [
                    'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?w=800&h=1000&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1583743814966-8936f5b7be1a?w=800&h=1000&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1562157873-818bc0726f68?w=800&h=1000&fit=crop&q=80',
                ],
                'colors' => ['Trắng', 'Đen', 'Xám'],
                'sizes'  => ['S', 'M', 'L', 'XL'],
                'tags'   => ['hang-moi', 'casual'],
            ],
            [
                'name'        => 'Áo Thun Nam Dry-EX Thể Thao',
                'slug'        => 'ao-thun-nam-dry-ex-uniqlo',
                'description' => 'Áo thun thể thao Uniqlo Dry-EX với công nghệ thoát ẩm nhanh, trọng lượng siêu nhẹ chỉ 120g. Thiết kế cổ tròn, đường may phẳng không gây cọ xát khi vận động mạnh. Lý tưởng cho chạy bộ, gym hoặc các hoạt động ngoài trời.',
                'price'       => 249000,
                'thumbnail'   => 'https://images.unsplash.com/photo-1581655353564-df123a1eb820?w=800&h=1000&fit=crop&q=80',
                'category'    => 'nam-ao-thun',
                'brand'       => 'Uniqlo',
                'gender'      => 'men',
                'is_featured' => false,
                'views_count' => 198,
                'images'      => [
                    'https://images.unsplash.com/photo-1581655353564-df123a1eb820?w=800&h=1000&fit=crop&q=80',
                ],
                'colors' => ['Đen', 'Xanh navy', 'Xám'],
                'sizes'  => ['S', 'M', 'L', 'XL'],
                'tags'   => ['the-thao', 'mua-he'],
            ],
            [
                'name'        => 'Áo Thun Nam Puma Essential Logo',
                'slug'        => 'ao-thun-nam-puma-essential-logo',
                'description' => 'Áo thun nam Puma Essential với logo Puma Cat in lớn phía trước, chất cotton pha polyester thoáng mát. Cổ tròn, tay ngắn, form regular fit thoải mái. Phù hợp cho tập luyện thể thao hoặc mặc hàng ngày.',
                'price'       => 350000,
                'discount'    => 20,
                'thumbnail'   => 'https://images.unsplash.com/photo-1618354691373-d851c5c3a990?w=800&h=1000&fit=crop&q=80',
                'category'    => 'nam-ao-thun',
                'brand'       => 'Puma',
                'gender'      => 'men',
                'is_featured' => false,
                'views_count' => 156,
                'images'      => [
                    'https://images.unsplash.com/photo-1618354691373-d851c5c3a990?w=800&h=1000&fit=crop&q=80',
                ],
                'colors' => ['Đen', 'Trắng', 'Đỏ'],
                'sizes'  => ['S', 'M', 'L', 'XL'],
                'tags'   => ['the-thao', 'giam-gia'],
            ],

            // ═══════════════════════════════════════════════════════════════════
            // NAM — ÁO SƠ MI
            // ═══════════════════════════════════════════════════════════════════
            [
                'name'        => 'Áo Sơ Mi Nam Sợi Tre Kháng Khuẩn',
                'slug'        => 'ao-so-mi-nam-soi-tre-uniqlo',
                'description' => 'Áo sơ mi nam Uniqlo làm từ sợi tre thiên nhiên có đặc tính kháng khuẩn và khử mùi. Chất vải mềm mượt hơn cotton, thoáng khí cực tốt trong mùa hè. Thiết kế regular fit, cổ đức sang trọng, phù hợp cho cả công sở lẫn dạo phố.',
                'price'       => 490000,
                'thumbnail'   => 'https://images.unsplash.com/photo-1596755094514-f87e34085b2c?w=800&h=1000&fit=crop&q=80',
                'category'    => 'nam-ao-so-mi',
                'brand'       => 'Uniqlo',
                'gender'      => 'men',
                'is_featured' => true,
                'views_count' => 276,
                'images'      => [
                    'https://images.unsplash.com/photo-1596755094514-f87e34085b2c?w=800&h=1000&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1602810318383-e386cc2a3ccf?w=800&h=1000&fit=crop&q=80',
                ],
                'colors' => ['Trắng', 'Xanh dương', 'Xám'],
                'sizes'  => ['S', 'M', 'L', 'XL'],
                'tags'   => ['cong-so', 'hang-moi'],
            ],
            [
                'name'        => 'Áo Sơ Mi Nam Oxford Classic',
                'slug'        => 'ao-so-mi-nam-oxford-hm',
                'description' => 'Áo sơ mi nam H&M chất liệu Oxford dày dặn, bề mặt vải dệt kiểu rổ đặc trưng. Cổ button-down, túi ngực trái, form regular fit. Một chiếc áo đa năng phù hợp từ công sở đến casual khi xắn tay áo lên.',
                'price'       => 390000,
                'thumbnail'   => 'https://images.unsplash.com/photo-1602810318383-e386cc2a3ccf?w=800&h=1000&fit=crop&q=80',
                'category'    => 'nam-ao-so-mi',
                'brand'       => 'H&M',
                'gender'      => 'men',
                'is_featured' => false,
                'views_count' => 213,
                'images'      => [
                    'https://images.unsplash.com/photo-1602810318383-e386cc2a3ccf?w=800&h=1000&fit=crop&q=80',
                ],
                'colors' => ['Xanh dương', 'Trắng', 'Hồng'],
                'sizes'  => ['S', 'M', 'L', 'XL'],
                'tags'   => ['cong-so', 'casual'],
            ],

            // ═══════════════════════════════════════════════════════════════════
            // NAM — ÁO POLO
            // ═══════════════════════════════════════════════════════════════════
            [
                'name'        => 'Áo Polo Nam Dri-FIT Piqué',
                'slug'        => 'ao-polo-nam-dri-fit-nike',
                'description' => 'Áo polo Nike Dri-FIT với công nghệ thấm hút mồ hôi tiên tiến, giúp bạn luôn khô thoáng trong các hoạt động thể thao. Chất vải piqué cao cấp, cổ bẻ hai cúc thanh lịch. Phù hợp cho sân golf, gym hoặc dạo phố.',
                'price'       => 450000,
                'thumbnail'   => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcS5mbrVFMfO0RTvuMi2AIIJfWdMfkg05luDs-xGoGhPUQ&s',
                'category'    => 'nam-ao-polo',
                'brand'       => 'Nike',
                'gender'      => 'men',
                'is_featured' => true,
                'views_count' => 365,
                'images'      => [
                    'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQZ5wfsL7VkIXvdq39NwgF4cKOMwcwHIDG11bakvT5z5A&s=10',
                    'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSLXmPZTVtsdDRHaYTsJhhHrrEMGfhucpAczhQQFh-fFA&s',
                ],
                'colors' => ['Xanh navy', 'Trắng', 'Đen'],
                'sizes'  => ['S', 'M', 'L', 'XL'],
                'tags'   => ['the-thao', 'best-seller'],
            ],
            [
                'name'        => 'Áo Polo Nam New Balance Athletics',
                'slug'        => 'ao-polo-nam-nb-athletics',
                'description' => 'Áo polo nam New Balance dòng Athletics với chất liệu Dry nhẹ thoáng, thấm hút mồ hôi nhanh. Logo NB thêu tinh tế trên ngực. Cổ bẻ hai cúc, form regular fit thoải mái. Phù hợp tập thể thao hoặc mặc hàng ngày.',
                'price'       => 490000,
                'discount'    => 10,
                'thumbnail'   => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQ4jXJs_hF_3odaq0vFq7RJE1pXSO1ufZa-K4zR6ZoXQg&s=10',
                'category'    => 'nam-ao-polo',
                'brand'       => 'New Balance',
                'gender'      => 'men',
                'is_featured' => false,
                'views_count' => 132,
                'images'      => [
                    'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQOp5vy4vuI4DdX33x21bEGREApqGJFkgVbCB2bfF5_1Q&s',
                ],
                'colors' => ['Xanh navy', 'Đen', 'Xám'],
                'sizes'  => ['M', 'L', 'XL'],
                'tags'   => ['the-thao', 'giam-gia'],
            ],

            // ═══════════════════════════════════════════════════════════════════
            // NAM — ÁO HOODIE
            // ═══════════════════════════════════════════════════════════════════
            [
                'name'        => 'Áo Hoodie Classic Nam',
                'slug'        => 'ao-hoodie-classic-nam-champion',
                'description' => 'Áo hoodie Champion với chất liệu fleece dày dặn, giữ ấm hoàn hảo trong những ngày se lạnh. Logo chữ C thêu nổi bật trên ngực trái. Túi kangaroo rộng rãi, dây rút mũ điều chỉnh được. Lý tưởng cho phong cách streetwear.',
                'price'       => 650000,
                'thumbnail'   => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTgcoCQV99G9r1V-L1IUHrabJOXgyhgOu_pzkdS0KVvdQ&s',
                'category'    => 'nam-ao-hoodie',
                'brand'       => 'Champion',
                'gender'      => 'men',
                'is_featured' => true,
                'views_count' => 412,
                'images'      => [
                    'https://images.unsplash.com/photo-1503341455253-b2e723bb3dbb?w=800&h=1000&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1591047139829-d91aecb6caea?w=800&h=1000&fit=crop&q=80',
                ],
                'colors' => ['Xám', 'Đen', 'Xanh navy'],
                'sizes'  => ['S', 'M', 'L', 'XL'],
                'tags'   => ['xu-huong', 'casual'],
            ],
            [
                'name'        => 'Áo Sweatshirt Trefoil Unisex',
                'slug'        => 'ao-sweatshirt-trefoil-adidas',
                'description' => 'Áo sweatshirt Adidas Originals với logo Trefoil 3 lá biểu tượng thêu nổi trên ngực. Chất liệu French Terry dày vừa, cổ tròn co giãn tốt, gấu và tay bo cổ điển. Item streetwear kinh điển không bao giờ lỗi mốt.',
                'price'       => 790000,
                'thumbnail'   => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSopyl9yoYDbHoKisvz_fLg_KwlxUBnlEfZpOnEfmm7eA&s=10',
                'category'    => 'nam-ao-hoodie',
                'brand'       => 'Adidas',
                'gender'      => 'unisex',
                'is_featured' => false,
                'views_count' => 275,
                'images'      => [
                    'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSTuZ3CPVsCVfjBPYDDLU4qr60JUAI9gf6C7X7Mg-jD4g&s=10',
                    'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcR8W6ra8ivDmDevg0VBPIT6Ep-M91VfGQILuRw0e93xcA&s',
                ],
                'colors' => ['Đen', 'Trắng', 'Xám'],
                'sizes'  => ['S', 'M', 'L', 'XL'],
                'tags'   => ['xu-huong', 'casual', 'giam-gia'],
            ],

            // ═══════════════════════════════════════════════════════════════════
            // NAM — ÁO KHOÁC
            // ═══════════════════════════════════════════════════════════════════
            [
                'name'        => 'Áo Khoác Bomber Nam Essential',
                'slug'        => 'ao-khoac-bomber-nam-adidas',
                'description' => 'Áo khoác bomber Adidas kiểu dáng cổ điển, chất liệu nylon chống nước nhẹ. Logo 3 sọc trên tay áo, lớp lót fleece mỏng giữ ấm. Túi bên có khóa zip bảo vệ đồ vật. Phù hợp cho mùa thu đông.',
                'price'       => 1290000,
                'thumbnail'   => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcS_gCXb-VCy14trkOl_AMY2r9ZQbCbNwoEjk0aqVqZNPw&s=10',
                'category'    => 'nam-ao-khoac',
                'brand'       => 'Adidas',
                'gender'      => 'men',
                'is_featured' => true,
                'views_count' => 321,
                'images'      => [
                    'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRatK6kX1qjo-uRoJjOHNJm7bI4AVl3cZk51VRNcLoZ6g&s=10',
                    'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRRxDINO6gfwbwZNeZD-vdFUA5-nCYS04u2bf0QMJAvzA&s=10',
                ],
                'colors' => ['Đen', 'Xanh navy', 'Xám'],
                'sizes'  => ['M', 'L', 'XL'],
                'tags'   => ['xu-huong', 'the-thao'],
            ],
            [
                'name'        => 'Áo Khoác Gió Chống Nước Resolve',
                'slug'        => 'ao-khoac-gio-chong-nuoc-tnf',
                'description' => 'Áo khoác gió The North Face Resolve với công nghệ DryVent™ chống nước hoàn toàn. Trọng lượng siêu nhẹ gấp gọn vào túi. Mũ đính liền điều chỉnh được. Lý tưởng cho leo núi, trekking và du lịch ngoài trời.',
                'price'       => 2490000,
                'thumbnail'   => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRUuq-rhBsm5PlnDLYq0xmMY3Jn-8LGeH241gTddfVhag&s=10',
                'category'    => 'nam-ao-khoac',
                'brand'       => 'The North Face',
                'gender'      => 'unisex',
                'is_featured' => true,
                'views_count' => 398,
                'images'      => [
                    'https://images.unsplash.com/photo-1551698618-1dfe5d97d256?w=800&h=1000&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1521150932951-303a95503ed3?w=800&h=1000&fit=crop&q=80',
                ],
                'colors' => ['Đen', 'Xanh dương', 'Cam'],
                'sizes'  => ['S', 'M', 'L', 'XL'],
                'tags'   => ['da-ngoai', 'cao-cap'],
            ],

            // ═══════════════════════════════════════════════════════════════════
            // NAM — ÁO BLAZER
            // ═══════════════════════════════════════════════════════════════════
            [
                'name'        => 'Áo Blazer Nam Slim Fit',
                'slug'        => 'ao-blazer-nam-slim-zara',
                'description' => 'Áo blazer Zara Man form slim fit hiện đại, chất liệu pha len cao cấp kháng nhăn. Thiết kế 2 cúc, ve áo đơn giản thanh lịch. Phù hợp cho công sở, sự kiện quan trọng hoặc hẹn hò.',
                'price'       => 1190000,
                'thumbnail'   => 'https://images.unsplash.com/photo-1507679799987-c73779587ccf?w=800&h=1000&fit=crop&q=80',
                'category'    => 'nam-ao-blazer',
                'brand'       => 'Zara',
                'gender'      => 'men',
                'is_featured' => false,
                'views_count' => 164,
                'images'      => [
                    'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTbKzjdXztGHdVO6t0-OGIhIGTBwwR6Q37xUKb1Vo2I-g&s=10',
                    'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTIuRvku1cAUa55eavKkmSM7u9MZQhlpX8bpA7JBrqEQg&s',
                ],
                'colors' => ['Đen', 'Xám', 'Nâu'],
                'sizes'  => ['M', 'L', 'XL'],
                'tags'   => ['cong-so', 'cao-cap'],
            ],

            // ═══════════════════════════════════════════════════════════════════
            // NAM — QUẦN JEANS
            // ═══════════════════════════════════════════════════════════════════
            [
                'name'        => 'Quần Jeans Nam Slim Fit 511',
                'slug'        => 'quan-jeans-nam-slim-511-levis',
                'description' => "Quần jeans Levi's 511 Slim Fit kinh điển với form ôm vừa vặn. Chất liệu denim cao cấp 99% cotton pha 1% elastane, đường may nổi màu vàng đặc trưng, khóa kéo YKK chắc chắn. Phù hợp mọi dịp từ đi học, đi làm đến dạo phố.",
                'price'       => 990000,
                'thumbnail'   => 'https://images.unsplash.com/photo-1542272604-787c3835535d?w=800&h=1000&fit=crop&q=80',
                'category'    => 'nam-quan-jeans',
                'brand'       => "Levi's",
                'gender'      => 'men',
                'is_featured' => false,
                'views_count' => 289,
                'images'      => [
                    'https://images.unsplash.com/photo-1542272604-787c3835535d?w=800&h=1000&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1543087903-1ac2ec7aa8c5?w=800&h=1000&fit=crop&q=80',
                ],
                'colors' => ['Xanh navy', 'Đen'],
                'sizes'  => ['S', 'M', 'L', 'XL'],
                'tags'   => ['best-seller', 'dao-pho'],
            ],
            [
                'name'        => 'Quần Jeans Nam Straight Fit',
                'slug'        => 'quan-jeans-nam-straight-hm',
                'description' => 'Quần jeans nam H&M dáng straight fit cổ điển, ống suông thoải mái. Chất denim 100% cotton dày dặn, wash nhẹ tạo độ mềm tự nhiên. Năm túi truyền thống, khóa kéo phía trước. Phù hợp cho mọi dịp.',
                'price'       => 590000,
                'thumbnail'   => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSK81vyP52g3P7_rOTcSm2tdo4YOeZ4Bqt8CUp4lDe8tQ&s=10',
                'category'    => 'nam-quan-jeans',
                'brand'       => 'H&M',
                'gender'      => 'men',
                'is_featured' => false,
                'views_count' => 167,
                'images'      => [
                    'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRgEWid4cdxlvEZmND6GD9469zdLAs2vWTG2f3w6n75YA&s',
                    'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTIoIoDGsz-aja5Ho6mFjh04fD9wVnwMdFhWB02VIcU3g&s=10',
                ],
                'colors' => ['Xanh dương', 'Đen', 'Xám'],
                'sizes'  => ['S', 'M', 'L', 'XL'],
                'tags'   => ['casual', 'dao-pho'],
            ],

            // ═══════════════════════════════════════════════════════════════════
            // NAM — QUẦN TÂY
            // ═══════════════════════════════════════════════════════════════════
            [
                'name'        => 'Quần Tây Nam Âu Slim Fit',
                'slug'        => 'quan-tay-nam-slim-zara',
                'description' => 'Quần tây Zara Man form slim fit hiện đại, đường may tinh xảo. Chất liệu pha len polyester cao cấp kháng nhăn, thoáng khí. Cạp âu có dây đai, khóa kéo ẩn thanh lịch. Phù hợp cho công sở và sự kiện trang trọng.',
                'price'       => 790000,
                'thumbnail'   => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcROe_r_taf2tMqkJ8Xi76Z_UV6DZTL8H1na5VO9S_rbQA&s=10',
                'category'    => 'nam-quan-tay',
                'brand'       => 'Zara',
                'gender'      => 'men',
                'is_featured' => false,
                'views_count' => 182,
                'images'      => [
                    'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSkMShWIUOGpeoPLYQmO2WZa5aUHRaoy8WOr2suWbWNWA&s=10',
                    'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTyLvjnWM_Xkm96_ezTI5aBIcvVD3klr-O-MstHJdSIdA&s=10',
                ],
                'colors' => ['Đen', 'Xám', 'Nâu'],
                'sizes'  => ['M', 'L', 'XL'],
                'tags'   => ['cong-so', 'cao-cap'],
            ],

            // ═══════════════════════════════════════════════════════════════════
            // NAM — QUẦN SHORT
            // ═══════════════════════════════════════════════════════════════════
            [
                'name'        => 'Quần Short Thể Thao Dri-FIT Nam',
                'slug'        => 'quan-short-the-thao-nam-nike',
                'description' => 'Quần short thể thao Nike Dri-FIT với công nghệ thấm hút mồ hôi tối ưu. Vải nhẹ co giãn 4 chiều, cạp chun dây rút. Túi bên có khóa zip bảo vệ điện thoại. Phù hợp cho chạy bộ, gym hay các hoạt động ngoài trời.',
                'price'       => 390000,
                'thumbnail'   => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcT2d-Hnp5iajFX9BSQIus-8EPxB6mShSXbo519DTwrs4Q&s=10',
                'category'    => 'nam-quan-short',
                'brand'       => 'Nike',
                'gender'      => 'men',
                'is_featured' => false,
                'views_count' => 310,
                'images'      => [
                    'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSqj_R7Jej5cte3K6Dt3F4fvBlFO06Ro1ucry9qorUb2Q&s=10',
                    'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTTKFyIrORWfnIVIeOxiWTWhzgzdKxgDg53qAJPO-ovew&s=10',
                ],
                'colors' => ['Đen', 'Xám', 'Xanh dương'],
                'sizes'  => ['S', 'M', 'L', 'XL'],
                'tags'   => ['the-thao', 'mua-he'],
            ],

            // ═══════════════════════════════════════════════════════════════════
            // NAM — QUẦN JOGGER
            // ═══════════════════════════════════════════════════════════════════
            [
                'name'        => 'Quần Jogger Tech Fleece Nam',
                'slug'        => 'quan-jogger-tech-fleece-nike',
                'description' => 'Quần jogger Nike Tech Fleece công nghệ vải 2 lớp nhẹ mà vẫn ấm, form tapered hẹp dần tôn dáng. Cạp chun dây rút tiện lợi, hai túi bên sâu rộng và túi phía sau có khóa zip. Phù hợp tập gym, chạy bộ hay dạo phố.',
                'price'       => 890000,
                'thumbnail'   => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSkwXAjfb1LjRgRSUktbY61lvJmhiNnGj-yHjs87fNtNg&s=10',
                'category'    => 'nam-quan-jogger',
                'brand'       => 'Nike',
                'gender'      => 'men',
                'is_featured' => true,
                'views_count' => 412,
                'images'      => [
                    'https://images.unsplash.com/photo-1552902865-b72c031ac5ea?w=800&h=1000&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1618354691438-25bc04584c23?w=800&h=1000&fit=crop&q=80',
                ],
                'colors' => ['Đen', 'Xám', 'Xanh navy'],
                'sizes'  => ['S', 'M', 'L', 'XL'],
                'tags'   => ['the-thao', 'best-seller'],
            ],

            // ═══════════════════════════════════════════════════════════════════
            // NỮ — ÁO THUN
            // ═══════════════════════════════════════════════════════════════════
            [
                'name'        => 'Áo Thun Nữ Oversize Cotton',
                'slug'        => 'ao-thun-nu-oversize-hm',
                'description' => 'Áo thun nữ H&M dáng oversize rộng rãi thoải mái, chất cotton organic mềm mịn thân thiện với da. Cổ tròn rộng, tay lỡ tạo phong cách trẻ trung. Dễ phối cùng quần jeans, chân váy hoặc quần short cho mọi dịp.',
                'price'       => 179000,
                'thumbnail'   => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRwaDWPPno3V9yzkR3gKD-9QYstP_vnMciCUpCKErc9Ww&s=10',
                'category'    => 'nu-ao-thun',
                'brand'       => 'H&M',
                'gender'      => 'women',
                'is_featured' => false,
                'views_count' => 312,
                'images'      => [
                    'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTr_e-cso9r8pN3o-dxltVvGokd5BFKNDYlN14-1GgmyA&s=10',
                    'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQURu1VggfzjPBCGxGgtugTr6Menbq-BVajhUuv6dF6lQ&s',
                ],
                'colors' => ['Trắng', 'Đen', 'Hồng'],
                'sizes'  => ['XS', 'S', 'M', 'L'],
                'tags'   => ['casual', 'mua-he'],
            ],
            [
                'name'        => 'Áo Croptop Nữ Cúc Bọc',
                'slug'        => 'ao-croptop-nu-cuc-boc-hm',
                'description' => 'Áo croptop H&M với hàng cúc bọc vải dọc thân tạo điểm nhấn thời trang độc đáo. Chất cotton pha linen nhẹ mát, thoáng khí lý tưởng cho mùa hè. Phong cách trẻ trung, năng động cho các bạn gái yêu thích thời trang đường phố.',
                'price'       => 250000,
                'thumbnail'   => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTCGk4eWwhbOKOz3e3t3OLEPplkCnO49p1hVjupnwR_dw&s=10',
                'category'    => 'nu-ao-thun',
                'brand'       => 'H&M',
                'gender'      => 'women',
                'is_featured' => false,
                'views_count' => 234,
                'images'      => [
                    'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTgTxwAcSkqYHlLTapsMiyvr6wd5_LwyX8UXoF-jTSqHA&s',
                ],
                'colors' => ['Trắng', 'Đen', 'Hồng'],
                'sizes'  => ['XS', 'S', 'M'],
                'tags'   => ['xu-huong', 'mua-he'],
            ],

            // ═══════════════════════════════════════════════════════════════════
            // NỮ — ÁO SƠ MI
            // ═══════════════════════════════════════════════════════════════════
            [
                'name'        => 'Áo Sơ Mi Lụa Cổ Đức Nữ',
                'slug'        => 'ao-so-mi-lua-co-duc-nu-zara',
                'description' => 'Áo sơ mi nữ Zara chất liệu lụa satin cao cấp mềm rũ sang trọng. Thiết kế cổ đức nhẹ nhàng kết hợp tay bồng tạo nét nữ tính. Lý tưởng cho công sở, tiệc nhẹ hoặc phối cùng blazer cho phong cách thanh lịch.',
                'price'       => 590000,
                'thumbnail'   => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTB6UCoW-iCfEIArqm2lts7sogn3zrV2LuHACLl2htunA&s=10',
                'category'    => 'nu-ao-so-mi',
                'brand'       => 'Zara',
                'gender'      => 'women',
                'is_featured' => false,
                'views_count' => 195,
                'images'      => [
                    'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQKMAjlJ4k-FsyS6WEKd0Ioc8VazNE-M74xFL-cWc05QA&s',
                    'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcT33wLT40E2YQdqV1_RMv9Y7Q0a7SG-hFE9X9NkysozmQ&s=10',
                ],
                'colors' => ['Trắng', 'Be', 'Hồng'],
                'sizes'  => ['S', 'M', 'L'],
                'tags'   => ['cong-so', 'cao-cap'],
            ],

            // ═══════════════════════════════════════════════════════════════════
            // NỮ — ÁO POLO
            // ═══════════════════════════════════════════════════════════════════
            [
                'name'        => 'Áo Polo Nữ Slim Fit Cổ Điển',
                'slug'        => 'ao-polo-nu-slim-fit-uniqlo',
                'description' => 'Áo polo nữ Uniqlo dáng slim fit ôm nhẹ tôn dáng, chất liệu piqué cotton mềm mại. Cổ bẻ thanh lịch, tay ngắn gọn gàng. Phù hợp mặc đi làm, đi học hay dạo phố cuối tuần với phong cách lịch sự.',
                'price'       => 350000,
                'thumbnail'   => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQ-zHijGJEAjhJ0JjOcDwPArscXiZp6-ghViBqlD2NQTg&s=10',
                'category'    => 'nu-ao-polo',
                'brand'       => 'Uniqlo',
                'gender'      => 'women',
                'is_featured' => false,
                'views_count' => 187,
                'images'      => [
                    'https://images.unsplash.com/photo-1586363104862-3a5e2ab60d99?w=800&h=1000&fit=crop&q=80',
                ],
                'colors' => ['Trắng', 'Hồng', 'Xanh dương'],
                'sizes'  => ['XS', 'S', 'M', 'L'],
                'tags'   => ['casual', 'cong-so'],
            ],

            // ═══════════════════════════════════════════════════════════════════
            // NỮ — ÁO HOODIE
            // ═══════════════════════════════════════════════════════════════════
            [
                'name'        => 'Áo Hoodie Nữ Crop Zip-Up',
                'slug'        => 'ao-hoodie-nu-crop-zip-hm',
                'description' => 'Áo hoodie nữ H&M dáng crop ngắn eo với khóa zip phía trước, phong cách năng động trẻ trung. Chất nỉ bông French Terry mềm mại, giữ ấm nhẹ. Phối cùng quần jogger hoặc chân váy tennis cho set đồ hoàn hảo.',
                'price'       => 450000,
                'thumbnail'   => 'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?w=800&h=1000&fit=crop&q=80',
                'category'    => 'nu-ao-hoodie',
                'brand'       => 'H&M',
                'gender'      => 'women',
                'is_featured' => false,
                'views_count' => 287,
                'images'      => [
                    'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?w=800&h=1000&fit=crop&q=80',
                ],
                'colors' => ['Hồng', 'Trắng', 'Tím'],
                'sizes'  => ['XS', 'S', 'M', 'L'],
                'tags'   => ['xu-huong', 'dao-pho'],
            ],

            // ═══════════════════════════════════════════════════════════════════
            // NỮ — ÁO KHOÁC
            // ═══════════════════════════════════════════════════════════════════
            [
                'name'        => 'Áo Phao Nữ Ultra Light Down',
                'slug'        => 'ao-phao-nu-ultra-light-uniqlo',
                'description' => 'Áo phao Uniqlo Ultra Light Down trọng lượng cực nhẹ chỉ 200g nhưng giữ ấm vượt trội nhờ lông vũ 90% white goose down. Gấp gọn vào túi kèm theo, lớp vỏ nylon siêu mỏng chống gió và kháng nước nhẹ.',
                'price'       => 990000,
                'thumbnail'   => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQcSQ7OSviGym9iRMBmwzL6xvaI2hj30-fk8CaiPVoVQA&s=10',
                'category'    => 'nu-ao-khoac',
                'brand'       => 'Uniqlo',
                'gender'      => 'women',
                'is_featured' => true,
                'views_count' => 489,
                'images'      => [
                    'https://images.unsplash.com/photo-1547721064-da6cfb341d50?w=800&h=1000&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1512436991641-6745cdb1723f?w=800&h=1000&fit=crop&q=80',
                ],
                'colors' => ['Đen', 'Hồng', 'Be'],
                'sizes'  => ['S', 'M', 'L'],
                'tags'   => ['mua-dong', 'cao-cap', 'best-seller'],
            ],
            [
                'name'        => 'Áo Blazer Nữ Oversize Thanh Lịch',
                'slug'        => 'ao-blazer-nu-oversize-zara',
                'description' => 'Áo blazer nữ Zara dáng oversize hiện đại, vai suông tạo silhouette thời thượng. Chất vải tweed pha len mềm, lót bên trong. Hai túi bên ngoài, một nút cài phía trước. Phối cùng quần ống rộng hoặc đầm cho phong cách thanh lịch.',
                'price'       => 1090000,
                'thumbnail'   => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSiCTcAHOy0IKQYzM2p-Q9tE5tgzPcywlRRy0zZ5QqpSA&s',
                'category'    => 'nu-ao-khoac',
                'brand'       => 'Zara',
                'gender'      => 'women',
                'is_featured' => true,
                'views_count' => 356,
                'images'      => [
                    'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRgiF2Ip6ffVYA2WwLok2HahGF9v8OrNiT5kuJGpRENYw&s=10',
                    'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQouL7L4XvGY-miLcMc3QCl17e6b7APOprkazRnEsjMlQ&s',
                ],
                'colors' => ['Be', 'Đen', 'Trắng'],
                'sizes'  => ['S', 'M', 'L'],
                'tags'   => ['cong-so', 'xu-huong', 'cao-cap'],
            ],

            // ═══════════════════════════════════════════════════════════════════
            // NỮ — ÁO LEN
            // ═══════════════════════════════════════════════════════════════════
            [
                'name'        => 'Áo Len Cổ Lọ Nữ Premium Lambswool',
                'slug'        => 'ao-len-co-lo-nu-uniqlo',
                'description' => 'Áo len cổ lọ Uniqlo làm từ len lambswool cao cấp, cực kỳ mềm mại và không gây ngứa da. Cổ lọ cao giữ ấm vùng cổ hiệu quả. Form body vừa vặn tôn dáng, màu sắc trung tính dễ phối.',
                'price'       => 690000,
                'thumbnail'   => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRuj0AKIOzSyj-7HZQd9kNCREvAVfeDmERsH3EhDDLQmw&s=10',
                'category'    => 'nu-ao-len',
                'brand'       => 'Uniqlo',
                'gender'      => 'women',
                'is_featured' => false,
                'views_count' => 228,
                'images'      => [
                    'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQYBXtnbquxhdfc0YsL5R88Kj5t9sH0WMKBNifKiYgQlA&s',
                    'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTK1QOxTVZH5y1MS5-wuOr769g33sMi7lRmNhzJVVs4oQ&s=10',
                ],
                'colors' => ['Be', 'Trắng', 'Xám', 'Hồng'],
                'sizes'  => ['S', 'M', 'L'],
                'tags'   => ['mua-dong', 'cao-cap'],
            ],
            [
                'name'        => 'Áo Len Cổ Tròn Merino Unisex',
                'slug'        => 'ao-len-co-tron-merino-uniqlo',
                'description' => 'Áo len Uniqlo từ sợi len Merino Extra Fine 100% mịn và nhẹ, không gây ngứa. Cổ tròn gọn gàng dễ phối, giữ ấm tốt trong mùa lạnh. Có thể mặc đơn hoặc layering bên trong áo khoác, blazer.',
                'price'       => 590000,
                'thumbnail'   => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRsCc-41_PULUeTP4x-SjuAcZnZ1Mcj32UKIC1beDWcVw&s=10',
                'category'    => 'nu-ao-len',
                'brand'       => 'Uniqlo',
                'gender'      => 'unisex',
                'is_featured' => false,
                'views_count' => 165,
                'images'      => [
                    'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRu3xojSfEcUdLBkEAchzBeNkxumfzwuQ11R3Wa07olew&s=10',
                    'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRHjwYAXrQDo7b6jBRmyu4JB1b6v6MwTDODoh4MNzKX1g&s',
                ],
                'colors' => ['Xám', 'Đen', 'Xanh navy', 'Nâu'],
                'sizes'  => ['S', 'M', 'L', 'XL'],
                'tags'   => ['mua-dong', 'cong-so'],
            ],

            // ═══════════════════════════════════════════════════════════════════
            // NỮ — QUẦN JEANS
            // ═══════════════════════════════════════════════════════════════════
            [
                'name'        => 'Quần Jeans Nữ Skinny 721 High Rise',
                'slug'        => 'quan-jeans-nu-skinny-721-levis',
                'description' => "Quần jeans Levi's 721 form skinny ôm sát cùng cạp cao tôn vóc dáng, kéo dài đôi chân. Chất denim co giãn 4 chiều đảm bảo thoải mái. Đường wash vintage tạo độ phai màu tự nhiên thời trang.",
                'price'       => 850000,
                'thumbnail'   => 'https://images.unsplash.com/photo-1541099649105-f69ad21f3246?w=800&h=1000&fit=crop&q=80',
                'category'    => 'nu-quan-jeans',
                'brand'       => "Levi's",
                'gender'      => 'women',
                'is_featured' => false,
                'views_count' => 387,
                'images'      => [
                    'https://images.unsplash.com/photo-1541099649105-f69ad21f3246?w=800&h=1000&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1544441893-675973e31985?w=800&h=1000&fit=crop&q=80',
                ],
                'colors' => ['Xanh navy', 'Đen'],
                'sizes'  => ['XS', 'S', 'M', 'L'],
                'tags'   => ['best-seller', 'dao-pho'],
            ],

            // ═══════════════════════════════════════════════════════════════════
            // NỮ — QUẦN TÂY
            // ═══════════════════════════════════════════════════════════════════
            [
                'name'        => 'Quần Tây Nữ Ống Suông Công Sở',
                'slug'        => 'quan-tay-nu-ong-suong-hm',
                'description' => 'Quần tây nữ H&M dáng ống suông hiện đại, cạp cao tôn dáng. Chất vải polyester pha viscose mềm mịn, kháng nhăn tốt. Đường ly ủi phẳng, túi xéo hai bên tiện dụng. Phù hợp mặc đi làm, phỏng vấn hoặc sự kiện.',
                'price'       => 590000,
                'thumbnail'   => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQ4vjtHgzYDgZbw9lXqnsQROkwMIPYqzpNXtbCI7wlGHA&s=10',
                'category'    => 'nu-quan-tay',
                'brand'       => 'H&M',
                'gender'      => 'women',
                'is_featured' => false,
                'views_count' => 143,
                'images'      => [
                    'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQim9Z4h7to7YVnkgeZ9S3q9vK4bXaF-E9ZIukTVEJufA&s=10',
                    'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcT51hMVbDlOkW-LSR_8KPmS5cBLDhrPJuPHrtGELoEhbA&s=10',
                ],
                'colors' => ['Đen', 'Be', 'Xám'],
                'sizes'  => ['XS', 'S', 'M', 'L'],
                'tags'   => ['cong-so', 'hang-moi'],
            ],
            [
                'name'        => 'Quần Short Nữ Linen Cao Cấp',
                'slug'        => 'quan-short-nu-linen-zara',
                'description' => 'Quần short nữ Zara chất liệu linen tự nhiên thoáng mát, cạp cao tôn dáng. Phom rộng nhẹ tạo sự thoải mái khi mặc cả ngày. Phối cùng áo thun, áo sơ mi hoặc croptop cho set đồ hè hoàn hảo.',
                'price'       => 490000,
                'thumbnail'   => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRZQrJtbH66IkJ83TmdWCQQ92q8WGBYr057egv-zaMPRA&s=10',
                'category'    => 'nu-quan-tay',
                'brand'       => 'Zara',
                'gender'      => 'women',
                'is_featured' => false,
                'views_count' => 176,
                'images'      => [
                    'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRLnDOPYwmUU87BpUJyU0j3Q28wYHqFfJDz_-j_6jxm7g&s=10',
                    'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTz4LCVhMRrEe5J45E2umwHTzvUR1iqms0dMRoTvHhzWw&s',
                ],
                'colors' => ['Be', 'Trắng', 'Xanh rêu'],
                'sizes'  => ['XS', 'S', 'M', 'L'],
                'tags'   => ['mua-he', 'dao-pho'],
            ],

            // ═══════════════════════════════════════════════════════════════════
            // NỮ — ĐẦM
            // ═══════════════════════════════════════════════════════════════════
            [
                'name'        => 'Đầm Maxi Hoa Nhí Nữ',
                'slug'        => 'dam-maxi-hoa-nhi-zara',
                'description' => 'Đầm maxi Zara với họa tiết hoa nhí nhỏ xinh, chất liệu viscose mềm nhẹ rũ tự nhiên. Thiết kế cổ V nhẹ, tay bồng nhẹ tạo điểm nhấn tinh tế. Dài qua gối, phù hợp đi biển, dạo phố hoặc tiệc ngoài trời.',
                'price'       => 690000,
                'discount'    => 15,
                'thumbnail'   => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRvLUOZqiSkqUOuvudko2YvfrIkHfkx2xaSpBczm8aExg&s=10',
                'category'    => 'nu-dam',
                'brand'       => 'Zara',
                'gender'      => 'women',
                'is_featured' => true,
                'views_count' => 474,
                'images'      => [
                    'https://images.unsplash.com/photo-1487222477894-8943e31ef7b2?w=800&h=1000&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1572804013427-4d7ca7268217?w=800&h=1000&fit=crop&q=80',
                ],
                'colors' => ['Hồng', 'Be', 'Trắng'],
                'sizes'  => ['S', 'M', 'L'],
                'tags'   => ['mua-he', 'hang-moi', 'giam-gia'],
            ],
            [
                'name'        => 'Đầm Body Midi Nữ',
                'slug'        => 'dam-body-midi-nu-zara',
                'description' => 'Đầm body midi Zara ôm sát cơ thể tôn đường cong, chất liệu jersey co giãn cao cấp. Dài qua đầu gối, phù hợp cho dạ tiệc, sự kiện trang trọng hoặc đi chơi buổi tối. Có thể phối cùng blazer cho phong cách công sở.',
                'price'       => 790000,
                'thumbnail'   => 'https://images.unsplash.com/photo-1617922001439-4a2e6562f328?w=800&h=1000&fit=crop&q=80',
                'category'    => 'nu-dam',
                'brand'       => 'Zara',
                'gender'      => 'women',
                'is_featured' => true,
                'views_count' => 456,
                'images'      => [
                    'https://images.unsplash.com/photo-1617922001439-4a2e6562f328?w=800&h=1000&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1572804013427-4d7ca7268217?w=800&h=1000&fit=crop&q=80',
                ],
                'colors' => ['Đen', 'Đỏ', 'Be'],
                'sizes'  => ['XS', 'S', 'M', 'L'],
                'tags'   => ['dao-pho', 'xu-huong', 'cao-cap'],
            ],
            [
                'name'        => 'Đầm Wrap Nữ Hoa Vintage',
                'slug'        => 'dam-wrap-nu-hoa-vintage-hm',
                'description' => 'Đầm wrap nữ H&M kiểu đắp chéo tôn eo, họa tiết hoa vintage lãng mạn. Chất viscose mềm rũ thoáng mát, tay dài nhún nhẹ. Thắt đai eo bên hông tạo điểm nhấn. Phù hợp cho buổi hẹn hò, tiệc trà hay dạo phố mùa thu.',
                'price'       => 490000,
                'thumbnail'   => 'https://images.unsplash.com/photo-1572804013427-4d7ca7268217?w=800&h=1000&fit=crop&q=80',
                'category'    => 'nu-dam',
                'brand'       => 'H&M',
                'gender'      => 'women',
                'is_featured' => false,
                'views_count' => 267,
                'images'      => [
                    'https://images.unsplash.com/photo-1572804013427-4d7ca7268217?w=800&h=1000&fit=crop&q=80',
                ],
                'colors' => ['Đỏ', 'Xanh rêu', 'Nâu'],
                'sizes'  => ['S', 'M', 'L'],
                'tags'   => ['dao-pho', 'xu-huong'],
            ],

            // ═══════════════════════════════════════════════════════════════════
            // NỮ — VÁY
            // ═══════════════════════════════════════════════════════════════════
            [
                'name'        => 'Váy Tennis Nữ Thể Thao',
                'slug'        => 'vay-tennis-nu-adidas',
                'description' => 'Váy tennis Adidas hai trong một với quần short bên trong, chất liệu Aeroready thấm hút mồ hôi nhanh. Cạp chun co giãn, viền xếp ly nhẹ tạo dáng xòe năng động. Phù hợp cho sân tennis, yoga, gym hay dạo phố.',
                'price'       => 550000,
                'thumbnail'   => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQad-Kv_XzbwOdO9okxXbAXCt9G_DEuvZp0CIm6w8HFtQ&s=10',
                'category'    => 'nu-vay',
                'brand'       => 'Adidas',
                'gender'      => 'women',
                'is_featured' => false,
                'views_count' => 210,
                'images'      => [
                    'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRzL56Ntc4kqtEe2qRbXX_ZJMCY3rXwor4sQYp2Fw6M8w&s=10',
                    'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcThmNGm0ZuhZcbEXBqMdFeIjrgeWfxzEIN3a_rYGshoqQ&s',
                ],
                'colors' => ['Trắng', 'Đen', 'Hồng'],
                'sizes'  => ['XS', 'S', 'M', 'L'],
                'tags'   => ['the-thao', 'mua-he'],
            ],
            [
                'name'        => 'Váy Midi Xếp Ly Thanh Lịch',
                'slug'        => 'vay-midi-xep-ly-zara',
                'description' => 'Váy midi Zara xếp ly toàn thân sang trọng, chất vải chiffon nhẹ nhàng bay bổng. Cạp chun co giãn thoải mái, chiều dài qua gối thanh lịch. Phối cùng áo sơ mi hoặc áo len cho phong cách Hàn Quốc nữ tính.',
                'price'       => 590000,
                'thumbnail'   => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcS1CoQPk36BXch_CE5rZhmreySItmL5E6Vr0PTcNk-xoA&s=10',
                'category'    => 'nu-vay',
                'brand'       => 'Zara',
                'gender'      => 'women',
                'is_featured' => true,
                'views_count' => 334,
                'images'      => [
                    'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRYsSk8HppFktfIitS_HPoPXQqWtBGtNyq6R4ZgDUbUSg&s=10',
                    'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSjhhFY_j3azR9SpxaYCgYZWtHaaNzQ0qYIsrtRNeNWng&s=10',
                ],
                'colors' => ['Be', 'Đen', 'Xanh rêu'],
                'sizes'  => ['S', 'M', 'L'],
                'tags'   => ['cong-so', 'xu-huong'],
            ],
            [
                'name'        => 'Váy Jean Chữ A Mini',
                'slug'        => 'vay-jean-chu-a-mini-levis',
                'description' => "Váy jean Levi's dáng chữ A mini trẻ trung, chất denim dày dặn co giãn nhẹ. Hàng nút đồng phía trước, cạp cao tôn vóc dáng. Wash nhẹ tạo độ vintage tự nhiên. Phối cùng áo thun hoặc áo sơ mi đều đẹp.",
                'price'       => 690000,
                'thumbnail'   => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQQBxpyIXfSfA87hDDEXBZZGRNaq0Q98LVSou6hHp7YoQ&s',
                'category'    => 'nu-vay',
                'brand'       => "Levi's",
                'gender'      => 'women',
                'is_featured' => false,
                'views_count' => 189,
                'images'      => [
                    'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRLET1lYRqILI2iRLf1rE2362J9RoxlB1qvCB4NZuAl3Q&s=10',
                ],
                'colors' => ['Xanh dương', 'Đen'],
                'sizes'  => ['XS', 'S', 'M', 'L'],
                'tags'   => ['casual', 'dao-pho'],
            ],
            // ═══════════════════════════════════════════════════════════════════
            // BỘ SƯU TẬP MÙA XUÂN — sắc sáng, chất liệu nhẹ, phom thanh lịch
            // ═══════════════════════════════════════════════════════════════════
            [
                'name'        => 'Áo Sơ Mi Nam Linen Pastel',
                'slug'        => 'ao-so-mi-nam-linen-pastel-uniqlo',
                'description' => 'Áo sơ mi nam Uniqlo chất liệu linen pha cotton mềm nhẹ, tông pastel tươi sáng đặc trưng mùa xuân. Form regular fit thoáng khí, dễ phối cùng quần tây hoặc jeans cho những buổi dạo phố đầu năm.',
                'price'       => 450000,
                'thumbnail'   => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRak5rY28C2sP4w01AKR2-jOsXg0HrjpSwNDgW8qUDGqg&s',
                'category'    => 'nam-ao-so-mi',
                'brand'       => 'Uniqlo',
                'gender'      => 'men',
                'is_featured' => true,
                'views_count' => 254,
                'images'      => [
                    'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSFl-Ooc_V958JTvR8wFPaUM7pCcAwhCXZ5nlSoxW9NEQ&s',
                    'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcT4nDxpKTNxUS60fDZCbbX7azlszOYLpY7aEsfNAhc2iA&s=10',
                ],
                'colors' => ['Trắng', 'Be', 'Xanh dương'],
                'sizes'  => ['S', 'M', 'L', 'XL'],
                'tags'   => ['mua-xuan', 'hang-moi', 'casual'],
            ],
            [
                'name'        => 'Đầm Hoa Nhí Cổ Vuông Nữ',
                'slug'        => 'dam-hoa-nhi-co-vuong-nu-hm',
                'description' => 'Đầm nữ H&M họa tiết hoa nhí tươi tắn, cổ vuông nhẹ nhàng nữ tính. Chất viscose mềm rũ thoáng mát, tay bồng duyên dáng. Món đồ hoàn hảo cho những ngày xuân ấm áp, đi chơi hay chụp ảnh dã ngoại.',
                'price'       => 520000,
                'thumbnail'   => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQT9Lc_-ZlNDaHIBC8bFco1DmXvgOO8qxFiZ4pYGgh9DA&s=10',
                'category'    => 'nu-dam',
                'brand'       => 'H&M',
                'gender'      => 'women',
                'is_featured' => true,
                'views_count' => 341,
                'images'      => [
                    'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcS9z_1aGcJ_5hQRqhcQ5pYJN0h_1OrgfTL-0FMzYYpGLw&s=10',
                    'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQW-nSIgK02dNkCnsftFgGgMtU2ztXTcDxrDKdW5fyAGg&s=10',
                ],
                'colors' => ['Hồng', 'Be', 'Trắng'],
                'sizes'  => ['XS', 'S', 'M', 'L'],
                'tags'   => ['mua-xuan', 'hang-moi', 'xu-huong'],
            ],
            [
                'name'        => 'Áo Cardigan Len Mỏng Nữ',
                'slug'        => 'ao-cardigan-len-mong-nu-uniqlo',
                'description' => 'Áo cardigan Uniqlo dệt kim mỏng nhẹ, cực kỳ phù hợp cho tiết trời giao mùa se dịu của mùa xuân. Chất len cotton không gây ngứa, cúc bọc thanh lịch. Dễ dàng khoác ngoài áo thun hay đầm cho set đồ layer tinh tế.',
                'price'       => 490000,
                'thumbnail'   => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQubjDhMyCopK5LY7WkjQBHwQDSCpqtPHrChj4RcpNzaA&s=10',
                'category'    => 'nu-ao-len',
                'brand'       => 'Uniqlo',
                'gender'      => 'women',
                'is_featured' => false,
                'views_count' => 208,
                'images'      => [
                    'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRtfBAErL28FT6785_KxhopajtJ7KHUJvwtGPK8_lMoeg&s=10',
                ],
                'colors' => ['Be', 'Hồng', 'Trắng', 'Xám'],
                'sizes'  => ['S', 'M', 'L'],
                'tags'   => ['mua-xuan', 'cong-so'],
            ],

            // ═══════════════════════════════════════════════════════════════════
            // BỘ SƯU TẬP MÙA HẠ — mát mẻ, năng động, thoáng khí
            // ═══════════════════════════════════════════════════════════════════
            [
                'name'        => 'Áo Polo Nam Cotton Piqué Mát Lạnh',
                'slug'        => 'ao-polo-nam-cotton-pique-mua-he-uniqlo',
                'description' => 'Áo polo Uniqlo AIRism chất cotton piqué công nghệ mát lạnh, thấm hút mồ hôi tức thì cho những ngày hè oi bức. Cổ bẻ gọn gàng, form vừa vặn. Lựa chọn lý tưởng cho công sở lẫn dạo phố mùa nắng.',
                'price'       => 390000,
                'thumbnail'   => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSzk_oJvBm2JC8hEXKyZRMBQ662aw9Bs6wHx09Y8fzmRA&s',
                'category'    => 'nam-ao-polo',
                'brand'       => 'Uniqlo',
                'gender'      => 'men',
                'is_featured' => false,
                'views_count' => 233,
                'images'      => [
                    'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSwmPQPfmXuRT1OozGpXiTXafU7RiFw1dKXUjPVucRe6Q&s',
                ],
                'colors' => ['Trắng', 'Xanh dương', 'Vàng'],
                'sizes'  => ['S', 'M', 'L', 'XL'],
                'tags'   => ['mua-he', 'casual'],
            ],
            [
                'name'        => 'Váy Midi Chiffon Bay Bổng',
                'slug'        => 'vay-midi-chiffon-mua-he-zara',
                'description' => 'Váy midi Zara chất chiffon nhẹ bay bổng, tông màu tươi sáng đúng chất mùa hạ. Cạp chun co giãn thoải mái, độ dài qua gối thanh lịch. Phối cùng áo croptop hoặc sơ mi buộc vạt cho phong cách nghỉ dưỡng.',
                'price'       => 560000,
                'thumbnail'   => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTFOHovDl3MZ6KuahYaMEeGCT2uWe3Mz8DTE1A8NVMUfg&s=10',
                'category'    => 'nu-vay',
                'brand'       => 'Zara',
                'gender'      => 'women',
                'is_featured' => false,
                'views_count' => 219,
                'images'      => [
                    'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTFOHovDl3MZ6KuahYaMEeGCT2uWe3Mz8DTE1A8NVMUfg&s=10',
                ],
                'colors' => ['Vàng', 'Be', 'Xanh dương'],
                'sizes'  => ['XS', 'S', 'M', 'L'],
                'tags'   => ['mua-he', 'xu-huong'],
            ],

            // ═══════════════════════════════════════════════════════════════════
            // BỘ SƯU TẬP MÙA THU — tông trầm ấm, layer, thanh lịch
            // ═══════════════════════════════════════════════════════════════════
            [
                'name'        => 'Áo Blazer Nam Tweed Mùa Thu',
                'slug'        => 'ao-blazer-nam-tweed-mua-thu-zara',
                'description' => 'Áo blazer Zara Man chất tweed pha len ấm áp, tông nâu đất trầm ấm đặc trưng mùa thu. Form slim fit lịch lãm, ve áo bản vừa. Khoác cùng áo len cổ tròn và quần tây cho set đồ thu thanh lịch, ấm áp.',
                'price'       => 1290000,
                'thumbnail'   => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRHBkrgF-bFN-dIf0DNd1rp2OX6s3-nriimxNVBqnbdhQ&s=10',
                'category'    => 'nam-ao-blazer',
                'brand'       => 'Zara',
                'gender'      => 'men',
                'is_featured' => true,
                'views_count' => 287,
                'images'      => [
                    'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSAbnQVQv3VkcOrO7ngKphxWazZFkTDeBJMABIZ9PIXQw&s',
                ],
                'colors' => ['Nâu', 'Xám', 'Xanh rêu'],
                'sizes'  => ['M', 'L', 'XL'],
                'tags'   => ['mua-thu', 'cong-so', 'cao-cap'],
            ],
            [
                'name'        => 'Áo Len Cổ Tròn Nam Tông Đất',
                'slug'        => 'ao-len-co-tron-nam-tong-dat-uniqlo',
                'description' => 'Áo len Uniqlo sợi Merino mịn nhẹ, tông màu đất ấm áp gợi cảm giác mùa thu. Cổ tròn gọn gàng dễ phối, giữ ấm nhẹ vừa đủ cho tiết trời se lạnh. Mặc đơn hoặc layer bên trong blazer đều thanh lịch.',
                'price'       => 590000,
                'thumbnail'   => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQh-NRA2zHcjcumtA-3fCgbRejl1KnkuHrk1-M-NUcAyw&s=10',
                'category'    => 'nam-ao-blazer',
                'brand'       => 'Uniqlo',
                'gender'      => 'men',
                'is_featured' => false,
                'views_count' => 176,
                'images'      => [
                    'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTRGvh2CtXWpFVKCNP9qYH2P9f_RvNRfHkUZVQr8krOIA&s=10',
                    'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRgJuhaol_OIaFw7GJGhclE4P7JoJCRU2H3vFgyfb5Q8Q&s=10',
                ],
                'colors' => ['Nâu', 'Xanh rêu', 'Be'],
                'sizes'  => ['S', 'M', 'L', 'XL'],
                'tags'   => ['mua-thu', 'cong-so'],
            ],
            [
                'name'        => 'Áo Blazer Nữ Dạ Tweed Cổ Điển',
                'slug'        => 'ao-blazer-nu-da-tweed-mua-thu-zara',
                'description' => 'Áo blazer nữ Zara chất dạ tweed dệt cổ điển, tông trung tính ấm áp cho mùa thu. Dáng vừa vặn tôn eo, cúc bọc tinh tế. Diện cùng quần ống rộng hoặc chân váy midi cho set đồ công sở thời thượng.',
                'price'       => 1150000,
                'thumbnail'   => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQKbjxVxyyf4a8FXDP-_OlZiQpi4aBH0Rr_TabvsI7Amw&s=10',
                'category'    => 'nu-ao-khoac',
                'brand'       => 'Zara',
                'gender'      => 'women',
                'is_featured' => true,
                'views_count' => 312,
                'images'      => [
                    'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQBccyjLExj_oMCl2aF0Gcz7TY-xFG8KpYNV7YT24FlTw&s=10',
                    'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTy5Yy_Z2RT-qskpsm7xZpcu2IhwxJO2f65FM3u54yQ3Q&s',
                ],
                'colors' => ['Be', 'Nâu', 'Xám'],
                'sizes'  => ['S', 'M', 'L'],
                'tags'   => ['mua-thu', 'cong-so', 'cao-cap'],
            ],

            // ═══════════════════════════════════════════════════════════════════
            // BỘ SƯU TẬP MÙA ĐÔNG — giữ ấm, dày dặn, cá tính
            // ═══════════════════════════════════════════════════════════════════
            [
                'name'        => 'Áo Măng Tô Dạ Nam Oversize',
                'slug'        => 'ao-mang-to-da-nam-oversize-zara',
                'description' => 'Áo măng tô Zara Man chất dạ dày dặn giữ ấm vượt trội cho mùa đông giá lạnh. Dáng oversize hiện đại, dài qua gối sang trọng. Lớp lót mềm mịn bên trong. Khoác ngoài áo len và sơ mi cho phong cách quý ông ấm áp.',
                'price'       => 1990000,
                'thumbnail'   => 'https://images.unsplash.com/photo-1551537482-f2075a1d41f2?w=800&h=1000&fit=crop&q=80',
                'category'    => 'nam-ao-khoac',
                'brand'       => 'Zara',
                'gender'      => 'men',
                'is_featured' => true,
                'views_count' => 368,
                'images'      => [
                    'https://images.unsplash.com/photo-1551537482-f2075a1d41f2?w=800&h=1000&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1547949003-9792a18a2601?w=800&h=1000&fit=crop&q=80',
                ],
                'colors' => ['Đen', 'Nâu', 'Xám'],
                'sizes'  => ['M', 'L', 'XL'],
                'tags'   => ['mua-dong', 'cao-cap', 'xu-huong'],
            ],
            [
                'name'        => 'Áo Phao Nữ Lông Vũ Dày Dặn',
                'slug'        => 'ao-phao-nu-long-vu-day-mua-dong-uniqlo',
                'description' => 'Áo phao Uniqlo lông vũ ấm áp vượt trội, chần bông dày dặn chắn gió lạnh mùa đông. Mũ liền có thể tháo rời, khóa kéo hai chiều tiện lợi. Trọng lượng nhẹ nhưng giữ nhiệt tối ưu cho những ngày rét đậm.',
                'price'       => 1490000,
                'thumbnail'   => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTVS1qAiJRjpbKADCnrpY9UTye8SA4qtJsfAHTvafJCAw&s=10',
                'category'    => 'nu-ao-khoac',
                'brand'       => 'Uniqlo',
                'gender'      => 'women',
                'is_featured' => true,
                'views_count' => 421,
                'images'      => [
                    'https://images.unsplash.com/photo-1547721064-da6cfb341d50?w=800&h=1000&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1512436991641-6745cdb1723f?w=800&h=1000&fit=crop&q=80',
                ],
                'colors' => ['Đen', 'Be', 'Xanh navy'],
                'sizes'  => ['S', 'M', 'L', 'XL'],
                'tags'   => ['mua-dong', 'cao-cap', 'best-seller'],
            ],
        ];
    }
}