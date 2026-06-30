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
                    'price'       => $data['price'],
                    'discount'    => $data['discount'] ?? 0,
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
                                'stock'      => rand(5, 80),
                                'image'      => $data['thumbnail'],
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
            // NHÓM ÁO
            // ═══════════════════════════════════════════════════════════════════

            // ─── Áo Thun ─────────────────────────────────────────────────────
            [
                'name'        => 'Áo Thun Nam Basic Tee',
                'slug'        => 'ao-thun-nam-basic-uniqlo',
                'description' => 'Áo thun nam basic từ Uniqlo làm từ chất liệu cotton 100% cao cấp, mềm mại và thoáng khí. Thiết kế đơn giản với cổ tròn, phù hợp mặc hàng ngày hoặc phối layering. Vải được xử lý chống co rút, giữ form tốt sau nhiều lần giặt.',
                'price'       => 199000,
                'thumbnail'   => 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?w=800&h=1000&fit=crop&q=80',
                'category'    => 'ao-thun',
                'brand'       => 'Uniqlo',
                'gender'      => 'men',
                'is_featured' => true,
                'views_count' => 482,
                'images'      => [
                    'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?w=800&h=1000&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1583743814966-8936f5b7be1a?w=800&h=1000&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1562157873-818bc0726f68?w=800&h=1000&fit=crop&q=80',
                ],
                'colors'      => ['Trắng', 'Đen', 'Xám'],
                'sizes'       => ['S', 'M', 'L', 'XL'],
                'tags'        => ['hang-moi', 'casual'],
            ],
            [
                'name'        => 'Áo Thun Nữ Oversize Cotton',
                'slug'        => 'ao-thun-nu-oversize-hm',
                'description' => 'Áo thun nữ H&M dáng oversize rộng rãi thoải mái, chất cotton organic mềm mịn thân thiện với da. Cổ tròn rộng, tay lỡ tạo phong cách trẻ trung. Dễ phối cùng quần jeans, chân váy hoặc quần short cho mọi dịp.',
                'price'       => 179000,
                'thumbnail'   => 'https://images.unsplash.com/photo-1529139574466-a303027c1d8b?w=800&h=1000&fit=crop&q=80',
                'category'    => 'ao-thun',
                'brand'       => 'H&M',
                'gender'      => 'women',
                'is_featured' => false,
                'views_count' => 312,
                'images'      => [
                    'https://images.unsplash.com/photo-1529139574466-a303027c1d8b?w=800&h=1000&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1485518882345-15568b007407?w=800&h=1000&fit=crop&q=80',
                ],
                'colors'      => ['Trắng', 'Đen', 'Hồng'],
                'sizes'       => ['XS', 'S', 'M', 'L'],
                'tags'        => ['casual', 'mua-he'],
            ],
            [
                'name'        => 'Áo Thun Nam Dry-EX Thể Thao',
                'slug'        => 'ao-thun-nam-dry-ex-uniqlo',
                'description' => 'Áo thun thể thao Uniqlo Dry-EX với công nghệ thoát ẩm nhanh, trọng lượng siêu nhẹ chỉ 120g. Thiết kế cổ tròn, đường may phẳng không gây cọ xát khi vận động mạnh. Lý tưởng cho chạy bộ, gym hoặc các hoạt động ngoài trời.',
                'price'       => 249000,
                'thumbnail'   => 'https://images.unsplash.com/photo-1581655353564-df123a1eb820?w=800&h=1000&fit=crop&q=80',
                'category'    => 'ao-thun',
                'brand'       => 'Uniqlo',
                'gender'      => 'men',
                'is_featured' => false,
                'views_count' => 198,
                'images'      => [
                    'https://images.unsplash.com/photo-1581655353564-df123a1eb820?w=800&h=1000&fit=crop&q=80',
                ],
                'colors'      => ['Đen', 'Xanh navy', 'Xám'],
                'sizes'       => ['S', 'M', 'L', 'XL'],
                'tags'        => ['the-thao', 'mua-he'],
            ],
            [
                'name'        => 'Áo Croptop Nữ Cúc Bọc',
                'slug'        => 'ao-croptop-nu-cuc-boc-hm',
                'description' => 'Áo croptop H&M với hàng cúc bọc vải dọc thân tạo điểm nhấn thời trang độc đáo. Chất cotton pha linen nhẹ mát, thoáng khí lý tưởng cho mùa hè. Phong cách trẻ trung, năng động cho các bạn gái yêu thích thời trang đường phố.',
                'price'       => 250000,
                'thumbnail'   => 'https://images.unsplash.com/photo-1539109136881-3be0616acf4b?w=800&h=1000&fit=crop&q=80',
                'category'    => 'ao-thun',
                'brand'       => 'H&M',
                'gender'      => 'women',
                'is_featured' => false,
                'views_count' => 234,
                'images'      => [
                    'https://images.unsplash.com/photo-1539109136881-3be0616acf4b?w=800&h=1000&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1485518882345-15568b007407?w=800&h=1000&fit=crop&q=80',
                ],
                'colors'      => ['Trắng', 'Đen', 'Hồng'],
                'sizes'       => ['XS', 'S', 'M'],
                'tags'        => ['xu-huong', 'mua-he'],
            ],

            // ─── Áo Polo ────────────────────────────────────────────────────
            [
                'name'        => 'Áo Polo Nam Dri-FIT Piqué',
                'slug'        => 'ao-polo-nam-dri-fit-nike',
                'description' => 'Áo polo Nike Dri-FIT với công nghệ thấm hút mồ hôi tiên tiến, giúp bạn luôn khô thoáng trong các hoạt động thể thao. Chất vải piqué cao cấp, cổ bẻ hai cúc thanh lịch. Phù hợp cho sân golf, gym hoặc dạo phố.',
                'price'       => 450000,
                'thumbnail'   => 'https://images.unsplash.com/photo-1603252109360-909baaf261ae?w=800&h=1000&fit=crop&q=80',
                'category'    => 'ao-polo',
                'brand'       => 'Nike',
                'gender'      => 'men',
                'is_featured' => true,
                'views_count' => 365,
                'images'      => [
                    'https://images.unsplash.com/photo-1603252109360-909baaf261ae?w=800&h=1000&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1600269452121-4f2416e55c28?w=800&h=1000&fit=crop&q=80',
                ],
                'colors'      => ['Xanh navy', 'Trắng', 'Đen'],
                'sizes'       => ['S', 'M', 'L', 'XL'],
                'tags'        => ['the-thao', 'best-seller'],
            ],
            [
                'name'        => 'Áo Polo Nữ Slim Fit Cổ Điển',
                'slug'        => 'ao-polo-nu-slim-fit-uniqlo',
                'description' => 'Áo polo nữ Uniqlo dáng slim fit ôm nhẹ tôn dáng, chất liệu piqué cotton mềm mại. Cổ bẻ thanh lịch, tay ngắn gọn gàng. Phù hợp mặc đi làm, đi học hay dạo phố cuối tuần với phong cách lịch sự.',
                'price'       => 350000,
                'thumbnail'   => 'https://images.unsplash.com/photo-1586363104862-3a5e2ab60d99?w=800&h=1000&fit=crop&q=80',
                'category'    => 'ao-polo',
                'brand'       => 'Uniqlo',
                'gender'      => 'women',
                'is_featured' => false,
                'views_count' => 187,
                'images'      => [
                    'https://images.unsplash.com/photo-1586363104862-3a5e2ab60d99?w=800&h=1000&fit=crop&q=80',
                ],
                'colors'      => ['Trắng', 'Hồng', 'Xanh dương'],
                'sizes'       => ['XS', 'S', 'M', 'L'],
                'tags'        => ['casual', 'cong-so'],
            ],

            // ─── Áo Sơ Mi ───────────────────────────────────────────────────
            [
                'name'        => 'Áo Sơ Mi Nam Sợi Tre Kháng Khuẩn',
                'slug'        => 'ao-so-mi-nam-soi-tre-uniqlo',
                'description' => 'Áo sơ mi nam Uniqlo làm từ sợi tre thiên nhiên có đặc tính kháng khuẩn và khử mùi. Chất vải mềm mượt hơn cotton, thoáng khí cực tốt trong mùa hè. Thiết kế regular fit, cổ đức sang trọng, phù hợp cho cả công sở lẫn dạo phố.',
                'price'       => 490000,
                'thumbnail'   => 'https://images.unsplash.com/photo-1596755094514-f87e34085b2c?w=800&h=1000&fit=crop&q=80',
                'category'    => 'ao-so-mi',
                'brand'       => 'Uniqlo',
                'gender'      => 'men',
                'is_featured' => true,
                'views_count' => 276,
                'images'      => [
                    'https://images.unsplash.com/photo-1596755094514-f87e34085b2c?w=800&h=1000&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1602810318383-e386cc2a3ccf?w=800&h=1000&fit=crop&q=80',
                ],
                'colors'      => ['Trắng', 'Xanh dương', 'Xám'],
                'sizes'       => ['S', 'M', 'L', 'XL'],
                'tags'        => ['cong-so', 'hang-moi'],
            ],
            [
                'name'        => 'Áo Sơ Mi Lụa Cổ Đức Nữ',
                'slug'        => 'ao-so-mi-lua-co-duc-nu-zara',
                'description' => 'Áo sơ mi nữ Zara chất liệu lụa satin cao cấp mềm rũ sang trọng. Thiết kế cổ đức nhẹ nhàng kết hợp tay bồng tạo nét nữ tính. Lý tưởng cho công sở, tiệc nhẹ hoặc phối cùng blazer cho phong cách thanh lịch.',
                'price'       => 590000,
                'thumbnail'   => 'https://images.unsplash.com/photo-1434389677669-e08b4cac3105?w=800&h=1000&fit=crop&q=80',
                'category'    => 'ao-so-mi',
                'brand'       => 'Zara',
                'gender'      => 'women',
                'is_featured' => false,
                'views_count' => 195,
                'images'      => [
                    'https://images.unsplash.com/photo-1434389677669-e08b4cac3105?w=800&h=1000&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1487222477894-8943e31ef7b2?w=800&h=1000&fit=crop&q=80',
                ],
                'colors'      => ['Trắng', 'Be', 'Hồng'],
                'sizes'       => ['S', 'M', 'L'],
                'tags'        => ['cong-so', 'cao-cap'],
            ],

            // ─── Áo Hoodie & Sweatshirt ─────────────────────────────────────
            [
                'name'        => 'Áo Hoodie Classic Nam',
                'slug'        => 'ao-hoodie-classic-nam-champion',
                'description' => 'Áo hoodie Champion với chất liệu fleece dày dặn, giữ ấm hoàn hảo trong những ngày se lạnh. Logo chữ C thêu nổi bật trên ngực trái. Túi kangaroo rộng rãi, dây rút mũ điều chỉnh được. Lý tưởng cho phong cách streetwear.',
                'price'       => 650000,
                'thumbnail'   => 'https://images.unsplash.com/photo-1556821840-3a63f15732ce?w=800&h=1000&fit=crop&q=80',
                'category'    => 'ao-hoodie-sweatshirt',
                'brand'       => 'Champion',
                'gender'      => 'men',
                'is_featured' => true,
                'views_count' => 412,
                'images'      => [
                    'https://images.unsplash.com/photo-1556821840-3a63f15732ce?w=800&h=1000&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1591047139829-d91aecb6caea?w=800&h=1000&fit=crop&q=80',
                ],
                'colors'      => ['Xám', 'Đen', 'Xanh navy'],
                'sizes'       => ['S', 'M', 'L', 'XL'],
                'tags'        => ['xu-huong', 'casual'],
            ],
            [
                'name'        => 'Áo Hoodie Nữ Crop Zip-Up',
                'slug'        => 'ao-hoodie-nu-crop-zip-hm',
                'description' => 'Áo hoodie nữ H&M dáng crop ngắn eo với khóa zip phía trước, phong cách năng động trẻ trung. Chất nỉ bông French Terry mềm mại, giữ ấm nhẹ. Phối cùng quần jogger hoặc chân váy tennis cho set đồ hoàn hảo.',
                'price'       => 450000,
                'thumbnail'   => 'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?w=800&h=1000&fit=crop&q=80',
                'category'    => 'ao-hoodie-sweatshirt',
                'brand'       => 'H&M',
                'gender'      => 'women',
                'is_featured' => false,
                'views_count' => 287,
                'images'      => [
                    'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?w=800&h=1000&fit=crop&q=80',
                ],
                'colors'      => ['Hồng', 'Trắng', 'Tím'],
                'sizes'       => ['XS', 'S', 'M', 'L'],
                'tags'        => ['xu-huong', 'dao-pho'],
            ],
            [
                'name'        => 'Áo Sweatshirt Trefoil Unisex',
                'slug'        => 'ao-sweatshirt-trefoil-adidas',
                'description' => 'Áo sweatshirt Adidas Originals với logo Trefoil 3 lá biểu tượng thêu nổi trên ngực. Chất liệu French Terry dày vừa, cổ tròn co giãn tốt, gấu và tay bo cổ điển. Item streetwear kinh điển không bao giờ lỗi mốt.',
                'price'       => 790000,
                'thumbnail'   => 'https://images.unsplash.com/photo-1560343090-f0409e92791a?w=800&h=1000&fit=crop&q=80',
                'category'    => 'ao-hoodie-sweatshirt',
                'brand'       => 'Adidas',
                'gender'      => 'unisex',
                'is_featured' => false,
                'views_count' => 275,
                'images'      => [
                    'https://images.unsplash.com/photo-1560343090-f0409e92791a?w=800&h=1000&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1545291730-faff8ca1d4b0?w=800&h=1000&fit=crop&q=80',
                ],
                'colors'      => ['Đen', 'Trắng', 'Xám'],
                'sizes'       => ['S', 'M', 'L', 'XL'],
                'tags'        => ['xu-huong', 'casual', 'giam-gia'],
            ],

            // ─── Áo Khoác ───────────────────────────────────────────────────
            [
                'name'        => 'Áo Khoác Bomber Nam Essential',
                'slug'        => 'ao-khoac-bomber-nam-adidas',
                'description' => 'Áo khoác bomber Adidas kiểu dáng cổ điển, chất liệu nylon chống nước nhẹ. Logo 3 sọc trên tay áo, lớp lót fleece mỏng giữ ấm. Túi bên có khóa zip bảo vệ đồ vật. Phù hợp cho mùa thu đông.',
                'price'       => 1290000,
                'thumbnail'   => 'https://images.unsplash.com/photo-1551537482-f2075a1d41f2?w=800&h=1000&fit=crop&q=80',
                'category'    => 'ao-khoac',
                'brand'       => 'Adidas',
                'gender'      => 'men',
                'is_featured' => true,
                'views_count' => 321,
                'images'      => [
                    'https://images.unsplash.com/photo-1551537482-f2075a1d41f2?w=800&h=1000&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1547949003-9792a18a2601?w=800&h=1000&fit=crop&q=80',
                ],
                'colors'      => ['Đen', 'Xanh navy', 'Xám'],
                'sizes'       => ['M', 'L', 'XL'],
                'tags'        => ['xu-huong', 'the-thao'],
            ],
            [
                'name'        => 'Áo Phao Nữ Ultra Light Down',
                'slug'        => 'ao-phao-nu-ultra-light-uniqlo',
                'description' => 'Áo phao Uniqlo Ultra Light Down trọng lượng cực nhẹ chỉ 200g nhưng giữ ấm vượt trội nhờ lông vũ 90% white goose down. Gấp gọn vào túi kèm theo, lớp vỏ nylon siêu mỏng chống gió và kháng nước nhẹ.',
                'price'       => 990000,
                'thumbnail'   => 'https://images.unsplash.com/photo-1547721064-da6cfb341d50?w=800&h=1000&fit=crop&q=80',
                'category'    => 'ao-khoac',
                'brand'       => 'Uniqlo',
                'gender'      => 'women',
                'is_featured' => true,
                'views_count' => 489,
                'images'      => [
                    'https://images.unsplash.com/photo-1547721064-da6cfb341d50?w=800&h=1000&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1512436991641-6745cdb1723f?w=800&h=1000&fit=crop&q=80',
                ],
                'colors'      => ['Đen', 'Hồng', 'Be'],
                'sizes'       => ['S', 'M', 'L'],
                'tags'        => ['mua-dong', 'cao-cap', 'best-seller'],
            ],
            [
                'name'        => 'Áo Khoác Gió Chống Nước Resolve',
                'slug'        => 'ao-khoac-gio-chong-nuoc-tnf',
                'description' => 'Áo khoác gió The North Face Resolve với công nghệ DryVent™ chống nước hoàn toàn. Trọng lượng siêu nhẹ gấp gọn vào túi. Mũ đính liền điều chỉnh được. Lý tưởng cho leo núi, trekking và du lịch ngoài trời.',
                'price'       => 2490000,
                'thumbnail'   => 'https://images.unsplash.com/photo-1551698618-1dfe5d97d256?w=800&h=1000&fit=crop&q=80',
                'category'    => 'ao-khoac',
                'brand'       => 'The North Face',
                'gender'      => 'unisex',
                'is_featured' => true,
                'views_count' => 398,
                'images'      => [
                    'https://images.unsplash.com/photo-1551698618-1dfe5d97d256?w=800&h=1000&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1521150932951-303a95503ed3?w=800&h=1000&fit=crop&q=80',
                ],
                'colors'      => ['Đen', 'Xanh dương', 'Cam'],
                'sizes'       => ['S', 'M', 'L', 'XL'],
                'tags'        => ['da-ngoai', 'cao-cap'],
            ],

            // ─── Áo Len ─────────────────────────────────────────────────────
            [
                'name'        => 'Áo Len Cổ Tròn Nam Merino',
                'slug'        => 'ao-len-co-tron-nam-merino-uniqlo',
                'description' => 'Áo len nam Uniqlo từ sợi len Merino Extra Fine 100% mịn và nhẹ, không gây ngứa. Cổ tròn gọn gàng dễ phối, giữ ấm tốt trong mùa lạnh. Có thể mặc đơn hoặc layering bên trong áo khoác, blazer.',
                'price'       => 590000,
                'thumbnail'   => 'https://images.unsplash.com/photo-1576566588028-4147f3842f27?w=800&h=1000&fit=crop&q=80',
                'category'    => 'ao-len',
                'brand'       => 'Uniqlo',
                'gender'      => 'men',
                'is_featured' => false,
                'views_count' => 165,
                'images'      => [
                    'https://images.unsplash.com/photo-1576566588028-4147f3842f27?w=800&h=1000&fit=crop&q=80',
                ],
                'colors'      => ['Xám', 'Đen', 'Xanh navy', 'Nâu'],
                'sizes'       => ['S', 'M', 'L', 'XL'],
                'tags'        => ['mua-dong', 'cong-so'],
            ],
            [
                'name'        => 'Áo Len Cổ Lọ Nữ Premium Lambswool',
                'slug'        => 'ao-len-co-lo-nu-uniqlo',
                'description' => 'Áo len cổ lọ Uniqlo làm từ len lambswool cao cấp, cực kỳ mềm mại và không gây ngứa da. Cổ lọ cao giữ ấm vùng cổ hiệu quả. Form body vừa vặn tôn dáng, màu sắc trung tính dễ phối.',
                'price'       => 690000,
                'thumbnail'   => 'https://images.unsplash.com/photo-1604176354204-926bd6f1cb6e?w=800&h=1000&fit=crop&q=80',
                'category'    => 'ao-len',
                'brand'       => 'Uniqlo',
                'gender'      => 'women',
                'is_featured' => false,
                'views_count' => 228,
                'images'      => [
                    'https://images.unsplash.com/photo-1604176354204-926bd6f1cb6e?w=800&h=1000&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1564859228273-274232fdb516?w=800&h=1000&fit=crop&q=80',
                ],
                'colors'      => ['Be', 'Trắng', 'Xám', 'Hồng'],
                'sizes'       => ['S', 'M', 'L'],
                'tags'        => ['mua-dong', 'cao-cap'],
            ],

            // ─── Áo Blazer ──────────────────────────────────────────────────
            [
                'name'        => 'Áo Blazer Nam Slim Fit',
                'slug'        => 'ao-blazer-nam-slim-zara',
                'description' => 'Áo blazer Zara Man form slim fit hiện đại, chất liệu pha len cao cấp kháng nhăn. Thiết kế 2 cúc, ve áo đơn giản thanh lịch. Phù hợp cho công sở, sự kiện quan trọng hoặc hẹn hò.',
                'price'       => 1190000,
                'thumbnail'   => 'https://images.unsplash.com/photo-1617196034735-f6d5605f8b95?w=800&h=1000&fit=crop&q=80',
                'category'    => 'ao-blazer',
                'brand'       => 'Zara',
                'gender'      => 'men',
                'is_featured' => false,
                'views_count' => 164,
                'images'      => [
                    'https://images.unsplash.com/photo-1617196034735-f6d5605f8b95?w=800&h=1000&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1507679799987-c73779587ccf?w=800&h=1000&fit=crop&q=80',
                ],
                'colors'      => ['Đen', 'Xám', 'Nâu'],
                'sizes'       => ['M', 'L', 'XL'],
                'tags'        => ['cong-so', 'cao-cap'],
            ],
            [
                'name'        => 'Áo Blazer Nữ Oversize Thanh Lịch',
                'slug'        => 'ao-blazer-nu-oversize-zara',
                'description' => 'Áo blazer nữ Zara dáng oversize hiện đại, vai suông tạo silhouette thời thượng. Chất vải tweed pha len mềm, lót bên trong. Hai túi bên ngoài, một nút cài phía trước. Phối cùng quần ống rộng hoặc đầm cho phong cách thanh lịch.',
                'price'       => 1090000,
                'thumbnail'   => 'https://images.unsplash.com/photo-1591369822096-ffd140ec948f?w=800&h=1000&fit=crop&q=80',
                'category'    => 'ao-blazer',
                'brand'       => 'Zara',
                'gender'      => 'women',
                'is_featured' => true,
                'views_count' => 356,
                'images'      => [
                    'https://images.unsplash.com/photo-1591369822096-ffd140ec948f?w=800&h=1000&fit=crop&q=80',
                ],
                'colors'      => ['Be', 'Đen', 'Trắng'],
                'sizes'       => ['S', 'M', 'L'],
                'tags'        => ['cong-so', 'xu-huong', 'cao-cap'],
            ],

            // ═══════════════════════════════════════════════════════════════════
            // NHÓM QUẦN
            // ═══════════════════════════════════════════════════════════════════

            // ─── Quần Jeans ──────────────────────────────────────────────────
            [
                'name'        => 'Quần Jeans Nam Slim Fit 511',
                'slug'        => 'quan-jeans-nam-slim-511-levis',
                'description' => "Quần jeans Levi's 511 Slim Fit kinh điển với form ôm vừa vặn. Chất liệu denim cao cấp 99% cotton pha 1% elastane, đường may nổi màu vàng đặc trưng, khóa kéo YKK chắc chắn. Phù hợp mọi dịp từ đi học, đi làm đến dạo phố.",
                'price'       => 990000,
                'thumbnail'   => 'https://images.unsplash.com/photo-1542272604-787c3835535d?w=800&h=1000&fit=crop&q=80',
                'category'    => 'quan-jeans',
                'brand'       => "Levi's",
                'gender'      => 'men',
                'is_featured' => false,
                'views_count' => 289,
                'images'      => [
                    'https://images.unsplash.com/photo-1542272604-787c3835535d?w=800&h=1000&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1543087903-1ac2ec7aa8c5?w=800&h=1000&fit=crop&q=80',
                ],
                'colors'      => ['Xanh navy', 'Đen'],
                'sizes'       => ['S', 'M', 'L', 'XL'],
                'tags'        => ['best-seller', 'dao-pho'],
            ],
            [
                'name'        => 'Quần Jeans Nữ Skinny 721 High Rise',
                'slug'        => 'quan-jeans-nu-skinny-721-levis',
                'description' => "Quần jeans Levi's 721 form skinny ôm sát cùng cạp cao tôn vóc dáng, kéo dài đôi chân. Chất denim co giãn 4 chiều đảm bảo thoải mái. Đường wash vintage tạo độ phai màu tự nhiên thời trang.",
                'price'       => 850000,
                'thumbnail'   => 'https://images.unsplash.com/photo-1541099649105-f69ad21f3246?w=800&h=1000&fit=crop&q=80',
                'category'    => 'quan-jeans',
                'brand'       => "Levi's",
                'gender'      => 'women',
                'is_featured' => false,
                'views_count' => 387,
                'images'      => [
                    'https://images.unsplash.com/photo-1541099649105-f69ad21f3246?w=800&h=1000&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1544441893-675973e31985?w=800&h=1000&fit=crop&q=80',
                ],
                'colors'      => ['Xanh navy', 'Đen'],
                'sizes'       => ['XS', 'S', 'M', 'L'],
                'tags'        => ['best-seller', 'dao-pho'],
            ],

            // ─── Quần Short ─────────────────────────────────────────────────
            [
                'name'        => 'Quần Short Thể Thao Dri-FIT Nam',
                'slug'        => 'quan-short-the-thao-nam-nike',
                'description' => 'Quần short thể thao Nike Dri-FIT với công nghệ thấm hút mồ hôi tối ưu. Vải nhẹ co giãn 4 chiều, cạp chun dây rút. Túi bên có khóa zip bảo vệ điện thoại. Phù hợp cho chạy bộ, gym hay các hoạt động ngoài trời.',
                'price'       => 390000,
                'thumbnail'   => 'https://images.unsplash.com/photo-1509631928351-a73fc7e0c9a0?w=800&h=1000&fit=crop&q=80',
                'category'    => 'quan-short',
                'brand'       => 'Nike',
                'gender'      => 'men',
                'is_featured' => false,
                'views_count' => 310,
                'images'      => [
                    'https://images.unsplash.com/photo-1509631928351-a73fc7e0c9a0?w=800&h=1000&fit=crop&q=80',
                ],
                'colors'      => ['Đen', 'Xám', 'Xanh dương'],
                'sizes'       => ['S', 'M', 'L', 'XL'],
                'tags'        => ['the-thao', 'mua-he'],
            ],
            [
                'name'        => 'Quần Short Nữ Linen Cao Cấp',
                'slug'        => 'quan-short-nu-linen-zara',
                'description' => 'Quần short nữ Zara chất liệu linen tự nhiên thoáng mát, cạp cao tôn dáng. Phom rộng nhẹ tạo sự thoải mái khi mặc cả ngày. Phối cùng áo thun, áo sơ mi hoặc croptop cho set đồ hè hoàn hảo.',
                'price'       => 490000,
                'thumbnail'   => 'https://images.unsplash.com/photo-1591195853828-11db59a44f6b?w=800&h=1000&fit=crop&q=80',
                'category'    => 'quan-short',
                'brand'       => 'Zara',
                'gender'      => 'women',
                'is_featured' => false,
                'views_count' => 176,
                'images'      => [
                    'https://images.unsplash.com/photo-1591195853828-11db59a44f6b?w=800&h=1000&fit=crop&q=80',
                ],
                'colors'      => ['Be', 'Trắng', 'Xanh rêu'],
                'sizes'       => ['XS', 'S', 'M', 'L'],
                'tags'        => ['mua-he', 'dao-pho'],
            ],

            // ─── Quần Tây ───────────────────────────────────────────────────
            [
                'name'        => 'Quần Tây Nam Âu Slim Fit',
                'slug'        => 'quan-tay-nam-slim-zara',
                'description' => 'Quần tây Zara Man form slim fit hiện đại, đường may tinh xảo. Chất liệu pha len polyester cao cấp kháng nhăn, thoáng khí. Cạp âu có dây đai, khóa kéo ẩn thanh lịch. Phù hợp cho công sở và sự kiện trang trọng.',
                'price'       => 790000,
                'thumbnail'   => 'https://images.unsplash.com/photo-1473966968600-fa801b869a1a?w=800&h=1000&fit=crop&q=80',
                'category'    => 'quan-tay',
                'brand'       => 'Zara',
                'gender'      => 'men',
                'is_featured' => false,
                'views_count' => 182,
                'images'      => [
                    'https://images.unsplash.com/photo-1473966968600-fa801b869a1a?w=800&h=1000&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1594938298603-c8148c4b4357?w=800&h=1000&fit=crop&q=80',
                ],
                'colors'      => ['Đen', 'Xám', 'Nâu'],
                'sizes'       => ['M', 'L', 'XL'],
                'tags'        => ['cong-so', 'cao-cap'],
            ],
            [
                'name'        => 'Quần Tây Nữ Ống Suông Công Sở',
                'slug'        => 'quan-tay-nu-ong-suong-hm',
                'description' => 'Quần tây nữ H&M dáng ống suông hiện đại, cạp cao tôn dáng. Chất vải polyester pha viscose mềm mịn, kháng nhăn tốt. Đường ly ủi phẳng, túi xéo hai bên tiện dụng. Phù hợp mặc đi làm, phỏng vấn hoặc sự kiện.',
                'price'       => 590000,
                'thumbnail'   => 'https://images.unsplash.com/photo-1594938298603-c8148c4b4357?w=800&h=1000&fit=crop&q=80',
                'category'    => 'quan-tay',
                'brand'       => 'H&M',
                'gender'      => 'women',
                'is_featured' => false,
                'views_count' => 143,
                'images'      => [
                    'https://images.unsplash.com/photo-1594938298603-c8148c4b4357?w=800&h=1000&fit=crop&q=80',
                ],
                'colors'      => ['Đen', 'Be', 'Xám'],
                'sizes'       => ['XS', 'S', 'M', 'L'],
                'tags'        => ['cong-so', 'hang-moi'],
            ],

            // ─── Quần Jogger ────────────────────────────────────────────────
            [
                'name'        => 'Quần Jogger Tech Fleece Nam',
                'slug'        => 'quan-jogger-tech-fleece-nike',
                'description' => 'Quần jogger Nike Tech Fleece công nghệ vải 2 lớp nhẹ mà vẫn ấm, form tapered hẹp dần tôn dáng. Cạp chun dây rút tiện lợi, hai túi bên sâu rộng và túi phía sau có khóa zip. Phù hợp tập gym, chạy bộ hay dạo phố.',
                'price'       => 890000,
                'thumbnail'   => 'https://images.unsplash.com/photo-1552902865-b72c031ac5ea?w=800&h=1000&fit=crop&q=80',
                'category'    => 'quan-jogger',
                'brand'       => 'Nike',
                'gender'      => 'men',
                'is_featured' => true,
                'views_count' => 412,
                'images'      => [
                    'https://images.unsplash.com/photo-1552902865-b72c031ac5ea?w=800&h=1000&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1618354691438-25bc04584c23?w=800&h=1000&fit=crop&q=80',
                ],
                'colors'      => ['Đen', 'Xám', 'Xanh navy'],
                'sizes'       => ['S', 'M', 'L', 'XL'],
                'tags'        => ['the-thao', 'best-seller'],
            ],
            [
                'name'        => 'Quần Jogger Nữ Thun Cotton Co Giãn',
                'slug'        => 'quan-jogger-nu-cotton-hm',
                'description' => 'Quần jogger nữ H&M chất thun cotton co giãn thoải mái, cạp chun dây rút điều chỉnh vòng eo. Gấu quần bo ôm gọn gàng, hai túi bên tiện lợi. Phù hợp mặc ở nhà, đi tập hay dạo phố cuối tuần.',
                'price'       => 350000,
                'thumbnail'   => 'https://images.unsplash.com/photo-1506629082955-511b1aa562c8?w=800&h=1000&fit=crop&q=80',
                'category'    => 'quan-jogger',
                'brand'       => 'H&M',
                'gender'      => 'women',
                'is_featured' => false,
                'views_count' => 205,
                'images'      => [
                    'https://images.unsplash.com/photo-1506629082955-511b1aa562c8?w=800&h=1000&fit=crop&q=80',
                ],
                'colors'      => ['Xám', 'Đen', 'Hồng'],
                'sizes'       => ['XS', 'S', 'M', 'L'],
                'tags'        => ['casual', 'the-thao'],
            ],

            // ═══════════════════════════════════════════════════════════════════
            // NHÓM ĐẦM & VÁY (gender: women)
            // ═══════════════════════════════════════════════════════════════════

            // ─── Đầm ────────────────────────────────────────────────────────
            [
                'name'        => 'Đầm Maxi Hoa Nhí Nữ',
                'slug'        => 'dam-maxi-hoa-nhi-zara',
                'description' => 'Đầm maxi Zara với họa tiết hoa nhí nhỏ xinh, chất liệu viscose mềm nhẹ rũ tự nhiên. Thiết kế cổ V nhẹ, tay bồng nhẹ tạo điểm nhấn tinh tế. Dài qua gối, phù hợp đi biển, dạo phố hoặc tiệc ngoài trời.',
                'price'       => 690000,
                'discount'    => 15,
                'thumbnail'   => 'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?w=800&h=1000&fit=crop&q=80',
                'category'    => 'dam',
                'brand'       => 'Zara',
                'gender'      => 'women',
                'is_featured' => true,
                'views_count' => 474,
                'images'      => [
                    'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?w=800&h=1000&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1496747611176-887f0a8a08d8?w=800&h=1000&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1572804013427-4d7ca7268217?w=800&h=1000&fit=crop&q=80',
                ],
                'colors'      => ['Hồng', 'Be', 'Trắng'],
                'sizes'       => ['S', 'M', 'L'],
                'tags'        => ['mua-he', 'hang-moi', 'giam-gia'],
            ],
            [
                'name'        => 'Đầm Body Midi Nữ',
                'slug'        => 'dam-body-midi-nu-zara',
                'description' => 'Đầm body midi Zara ôm sát cơ thể tôn đường cong, chất liệu jersey co giãn cao cấp. Dài qua đầu gối, phù hợp cho dạ tiệc, sự kiện trang trọng hoặc đi chơi buổi tối. Có thể phối cùng blazer cho phong cách công sở.',
                'price'       => 790000,
                'thumbnail'   => 'https://images.unsplash.com/photo-1495385794501-b51567a20da5?w=800&h=1000&fit=crop&q=80',
                'category'    => 'dam',
                'brand'       => 'Zara',
                'gender'      => 'women',
                'is_featured' => true,
                'views_count' => 456,
                'images'      => [
                    'https://images.unsplash.com/photo-1495385794501-b51567a20da5?w=800&h=1000&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1572804013427-4d7ca7268217?w=800&h=1000&fit=crop&q=80',
                ],
                'colors'      => ['Đen', 'Đỏ', 'Be'],
                'sizes'       => ['XS', 'S', 'M', 'L'],
                'tags'        => ['dao-pho', 'xu-huong', 'cao-cap'],
            ],
            [
                'name'        => 'Đầm Sơ Mi Nữ Cổ Điển',
                'slug'        => 'dam-so-mi-nu-co-dien-hm',
                'description' => 'Đầm sơ mi nữ H&M dáng suông thanh lịch, hàng nút phía trước, thắt đai eo tạo điểm nhấn. Chất cotton pha mềm mại, thoáng khí. Phù hợp mặc đi làm, đi hẹn hoặc dạo phố cuối tuần.',
                'price'       => 550000,
                'thumbnail'   => 'https://images.unsplash.com/photo-1502716119720-b23a1e3b1b16?w=800&h=1000&fit=crop&q=80',
                'category'    => 'dam',
                'brand'       => 'H&M',
                'gender'      => 'women',
                'is_featured' => false,
                'views_count' => 198,
                'images'      => [
                    'https://images.unsplash.com/photo-1502716119720-b23a1e3b1b16?w=800&h=1000&fit=crop&q=80',
                ],
                'colors'      => ['Xanh dương', 'Trắng', 'Be'],
                'sizes'       => ['S', 'M', 'L'],
                'tags'        => ['cong-so', 'casual'],
            ],

            // ─── Váy ────────────────────────────────────────────────────────
            [
                'name'        => 'Váy Tennis Nữ Thể Thao',
                'slug'        => 'vay-tennis-nu-adidas',
                'description' => 'Váy tennis Adidas hai trong một với quần short bên trong, chất liệu Aeroready thấm hút mồ hôi nhanh. Cạp chun co giãn, viền xếp ly nhẹ tạo dáng xòe năng động. Phù hợp cho sân tennis, yoga, gym hay dạo phố.',
                'price'       => 550000,
                'thumbnail'   => 'https://images.unsplash.com/photo-1568252542512-9fe8fe9c87bb?w=800&h=1000&fit=crop&q=80',
                'category'    => 'vay',
                'brand'       => 'Adidas',
                'gender'      => 'women',
                'is_featured' => false,
                'views_count' => 210,
                'images'      => [
                    'https://images.unsplash.com/photo-1568252542512-9fe8fe9c87bb?w=800&h=1000&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1520013817300-1f4c1cb245ef?w=800&h=1000&fit=crop&q=80',
                ],
                'colors'      => ['Trắng', 'Đen', 'Hồng'],
                'sizes'       => ['XS', 'S', 'M', 'L'],
                'tags'        => ['the-thao', 'mua-he'],
            ],
            [
                'name'        => 'Váy Midi Xếp Ly Thanh Lịch',
                'slug'        => 'vay-midi-xep-ly-zara',
                'description' => 'Váy midi Zara xếp ly toàn thân sang trọng, chất vải chiffon nhẹ nhàng bay bổng. Cạp chun co giãn thoải mái, chiều dài qua gối thanh lịch. Phối cùng áo sơ mi hoặc áo len cho phong cách Hàn Quốc nữ tính.',
                'price'       => 590000,
                'thumbnail'   => 'https://images.unsplash.com/photo-1583496661160-fb5886a0aaaa?w=800&h=1000&fit=crop&q=80',
                'category'    => 'vay',
                'brand'       => 'Zara',
                'gender'      => 'women',
                'is_featured' => true,
                'views_count' => 334,
                'images'      => [
                    'https://images.unsplash.com/photo-1583496661160-fb5886a0aaaa?w=800&h=1000&fit=crop&q=80',
                ],
                'colors'      => ['Be', 'Đen', 'Xanh rêu'],
                'sizes'       => ['S', 'M', 'L'],
                'tags'        => ['cong-so', 'xu-huong'],
            ],

            // ═══════════════════════════════════════════════════════════════════
            // NHÓM PHỤ KIỆN (gender: unisex)
            // ═══════════════════════════════════════════════════════════════════

            // ─── Mũ & Nón ───────────────────────────────────────────────────
            [
                'name'        => 'Mũ Lưỡi Trai MLB Logo Classic',
                'slug'        => 'mu-luoi-trai-mlb-logo-classic',
                'description' => 'Mũ lưỡi trai MLB với logo NY thêu nổi kinh điển, chất cotton twill dày dặn giữ form tốt. Khóa điều chỉnh phía sau phù hợp mọi kích cỡ đầu. Lưỡi trai cong che nắng hiệu quả. Item phụ kiện streetwear không thể thiếu.',
                'price'       => 450000,
                'thumbnail'   => 'https://images.unsplash.com/photo-1588850561407-ed78c334e67a?w=800&h=1000&fit=crop&q=80',
                'category'    => 'mu-non',
                'brand'       => 'MLB',
                'gender'      => 'unisex',
                'is_featured' => true,
                'views_count' => 398,
                'images'      => [
                    'https://images.unsplash.com/photo-1588850561407-ed78c334e67a?w=800&h=1000&fit=crop&q=80',
                ],
                'colors'      => ['Đen', 'Trắng', 'Xanh navy'],
                'sizes'       => ['M', 'L'],
                'tags'        => ['xu-huong', 'best-seller'],
            ],
            [
                'name'        => 'Nón Bucket Nike Sportswear',
                'slug'        => 'non-bucket-nike-sportswear',
                'description' => 'Nón bucket Nike Sportswear dáng rộng vành che nắng tốt, chất nylon nhẹ chống nước nhẹ. Logo Swoosh thêu tinh tế phía trước. Thiết kế gọn gàng có thể gấp lại khi không sử dụng. Phù hợp đi biển, dã ngoại hay dạo phố.',
                'price'       => 390000,
                'thumbnail'   => 'https://images.unsplash.com/photo-1556306535-0f09a537f0a3?w=800&h=1000&fit=crop&q=80',
                'category'    => 'mu-non',
                'brand'       => 'Nike',
                'gender'      => 'unisex',
                'is_featured' => false,
                'views_count' => 167,
                'images'      => [
                    'https://images.unsplash.com/photo-1556306535-0f09a537f0a3?w=800&h=1000&fit=crop&q=80',
                ],
                'colors'      => ['Đen', 'Be', 'Xanh rêu'],
                'sizes'       => ['M', 'L'],
                'tags'        => ['mua-he', 'casual'],
            ],

            // ─── Túi Xách ───────────────────────────────────────────────────
            [
                'name'        => 'Túi Tote Vải Canvas Unisex',
                'slug'        => 'tui-tote-vai-canvas-converse',
                'description' => 'Túi tote Converse chất liệu canvas dày bền, logo All Star in nổi bật. Dung tích lớn đủ chứa laptop, sách vở và đồ cá nhân. Quai xách dài có thể đeo vai thoải mái. Phong cách casual phù hợp đi học, đi làm hay dạo phố.',
                'price'       => 350000,
                'thumbnail'   => 'https://images.unsplash.com/photo-1544816155-12df9643f363?w=800&h=1000&fit=crop&q=80',
                'category'    => 'tui-xach',
                'brand'       => 'Converse',
                'gender'      => 'unisex',
                'is_featured' => false,
                'views_count' => 231,
                'images'      => [
                    'https://images.unsplash.com/photo-1544816155-12df9643f363?w=800&h=1000&fit=crop&q=80',
                ],
                'colors'      => ['Đen', 'Trắng', 'Be'],
                'sizes'       => ['M'],
                'tags'        => ['casual', 'dao-pho'],
            ],
            [
                'name'        => 'Balo Thể Thao Phase',
                'slug'        => 'balo-the-thao-phase-puma',
                'description' => 'Balo Puma Phase thiết kế năng động, chất polyester bền chống nước nhẹ. Ngăn chính rộng rãi chứa laptop 15 inch, ngăn phụ phía trước tiện dụng. Quai đeo đệm mút êm vai, dây kéo YKK chắc chắn. Phù hợp đi học, đi tập hoặc du lịch ngắn ngày.',
                'price'       => 490000,
                'thumbnail'   => 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?w=800&h=1000&fit=crop&q=80',
                'category'    => 'tui-xach',
                'brand'       => 'Puma',
                'gender'      => 'unisex',
                'is_featured' => false,
                'views_count' => 178,
                'images'      => [
                    'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?w=800&h=1000&fit=crop&q=80',
                ],
                'colors'      => ['Đen', 'Xanh navy'],
                'sizes'       => ['M'],
                'tags'        => ['the-thao', 'da-ngoai'],
            ],

            // ─── Thắt Lưng ──────────────────────────────────────────────────
            [
                'name'        => 'Thắt Lưng Da Bò Thật Khóa Kim Loại',
                'slug'        => 'that-lung-da-bo-khoa-kim-loai-zara',
                'description' => 'Thắt lưng Zara da bò thật 100% cao cấp, bề mặt nhám tự nhiên bền đẹp theo thời gian. Khóa kim loại hợp kim kẽm không gỉ, mặt khóa vuông thanh lịch. Rộng 3.5cm phù hợp với quần tây, jeans. Sản phẩm unisex dùng cho cả nam và nữ.',
                'price'       => 390000,
                'thumbnail'   => 'https://images.unsplash.com/photo-1624222247344-550fb60583dc?w=800&h=1000&fit=crop&q=80',
                'category'    => 'that-lung',
                'brand'       => 'Zara',
                'gender'      => 'unisex',
                'is_featured' => false,
                'views_count' => 124,
                'images'      => [
                    'https://images.unsplash.com/photo-1624222247344-550fb60583dc?w=800&h=1000&fit=crop&q=80',
                ],
                'colors'      => ['Đen', 'Nâu'],
                'sizes'       => ['M', 'L', 'XL'],
                'tags'        => ['cong-so', 'cao-cap'],
            ],
            [
                'name'        => 'Thắt Lưng Vải Dù Khóa Nhựa Thể Thao',
                'slug'        => 'that-lung-vai-du-khoa-nhua-nike',
                'description' => 'Thắt lưng Nike chất liệu vải dù bền chắc, khóa nhựa chữ nhật đen nhám thể thao. Trọng lượng siêu nhẹ, không kêu khi qua cổng an ninh. Rộng 3cm, điều chỉnh vô cấp phù hợp mọi vòng eo. Lý tưởng cho phong cách thể thao, dã ngoại.',
                'price'       => 250000,
                'thumbnail'   => 'https://images.unsplash.com/photo-1553704571-c32d20e6c74c?w=800&h=1000&fit=crop&q=80',
                'category'    => 'that-lung',
                'brand'       => 'Nike',
                'gender'      => 'unisex',
                'is_featured' => false,
                'views_count' => 89,
                'images'      => [
                    'https://images.unsplash.com/photo-1553704571-c32d20e6c74c?w=800&h=1000&fit=crop&q=80',
                ],
                'colors'      => ['Đen', 'Xanh navy', 'Xám'],
                'sizes'       => ['M', 'L'],
                'tags'        => ['the-thao', 'casual'],
            ],

            // ═══════════════════════════════════════════════════════════════════
            // SẢN PHẨM BỔ SUNG ĐA DẠNG
            // ═══════════════════════════════════════════════════════════════════

            [
                'name'        => 'Áo Thun Nam Puma Essential Logo',
                'slug'        => 'ao-thun-nam-puma-essential-logo',
                'description' => 'Áo thun nam Puma Essential với logo Puma Cat in lớn phía trước, chất cotton pha polyester thoáng mát. Cổ tròn, tay ngắn, form regular fit thoải mái. Phù hợp cho tập luyện thể thao hoặc mặc hàng ngày.',
                'price'       => 350000,
                'discount'    => 20,
                'thumbnail'   => 'https://images.unsplash.com/photo-1618354691373-d851c5c3a990?w=800&h=1000&fit=crop&q=80',
                'category'    => 'ao-thun',
                'brand'       => 'Puma',
                'gender'      => 'men',
                'is_featured' => false,
                'views_count' => 156,
                'images'      => [
                    'https://images.unsplash.com/photo-1618354691373-d851c5c3a990?w=800&h=1000&fit=crop&q=80',
                ],
                'colors'      => ['Đen', 'Trắng', 'Đỏ'],
                'sizes'       => ['S', 'M', 'L', 'XL'],
                'tags'        => ['the-thao', 'giam-gia'],
            ],
            [
                'name'        => 'Áo Sơ Mi Nam Oxford Classic',
                'slug'        => 'ao-so-mi-nam-oxford-hm',
                'description' => 'Áo sơ mi nam H&M chất liệu Oxford dày dặn, bề mặt vải dệt kiểu rổ đặc trưng. Cổ button-down, túi ngực trái, form regular fit. Một chiếc áo đa năng phù hợp từ công sở đến casual khi xắn tay áo lên.',
                'price'       => 390000,
                'thumbnail'   => 'https://images.unsplash.com/photo-1602810318383-e386cc2a3ccf?w=800&h=1000&fit=crop&q=80',
                'category'    => 'ao-so-mi',
                'brand'       => 'H&M',
                'gender'      => 'men',
                'is_featured' => false,
                'views_count' => 213,
                'images'      => [
                    'https://images.unsplash.com/photo-1602810318383-e386cc2a3ccf?w=800&h=1000&fit=crop&q=80',
                ],
                'colors'      => ['Xanh dương', 'Trắng', 'Hồng'],
                'sizes'       => ['S', 'M', 'L', 'XL'],
                'tags'        => ['cong-so', 'casual'],
            ],
            [
                'name'        => 'Quần Jeans Nam Straight Fit',
                'slug'        => 'quan-jeans-nam-straight-hm',
                'description' => 'Quần jeans nam H&M dáng straight fit cổ điển, ống suông thoải mái. Chất denim 100% cotton dày dặn, wash nhẹ tạo độ mềm tự nhiên. Năm túi truyền thống, khóa kéo phía trước. Phù hợp cho mọi dịp.',
                'price'       => 590000,
                'thumbnail'   => 'https://images.unsplash.com/photo-1542272604-787c3835535d?w=800&h=1000&fit=crop&q=80',
                'category'    => 'quan-jeans',
                'brand'       => 'H&M',
                'gender'      => 'men',
                'is_featured' => false,
                'views_count' => 167,
                'images'      => [
                    'https://images.unsplash.com/photo-1542272604-787c3835535d?w=800&h=1000&fit=crop&q=80',
                ],
                'colors'      => ['Xanh dương', 'Đen', 'Xám'],
                'sizes'       => ['S', 'M', 'L', 'XL'],
                'tags'        => ['casual', 'dao-pho'],
            ],
            [
                'name'        => 'Áo Polo Nam New Balance Athletics',
                'slug'        => 'ao-polo-nam-nb-athletics',
                'description' => 'Áo polo nam New Balance dòng Athletics với chất liệu Dry nhẹ thoáng, thấm hút mồ hôi nhanh. Logo NB thêu tinh tế trên ngực. Cổ bẻ hai cúc, form regular fit thoải mái. Phù hợp tập thể thao hoặc mặc hàng ngày.',
                'price'       => 490000,
                'discount'    => 10,
                'thumbnail'   => 'https://images.unsplash.com/photo-1600269452121-4f2416e55c28?w=800&h=1000&fit=crop&q=80',
                'category'    => 'ao-polo',
                'brand'       => 'New Balance',
                'gender'      => 'men',
                'is_featured' => false,
                'views_count' => 132,
                'images'      => [
                    'https://images.unsplash.com/photo-1600269452121-4f2416e55c28?w=800&h=1000&fit=crop&q=80',
                ],
                'colors'      => ['Xanh navy', 'Đen', 'Xám'],
                'sizes'       => ['M', 'L', 'XL'],
                'tags'        => ['the-thao', 'giam-gia'],
            ],
            [
                'name'        => 'Đầm Wrap Nữ Hoa Vintage',
                'slug'        => 'dam-wrap-nu-hoa-vintage-hm',
                'description' => 'Đầm wrap nữ H&M kiểu đắp chéo tôn eo, họa tiết hoa vintage lãng mạn. Chất viscose mềm rũ thoáng mát, tay dài nhún nhẹ. Thắt đai eo bên hông tạo điểm nhấn. Phù hợp cho buổi hẹn hò, tiệc trà hay dạo phố mùa thu.',
                'price'       => 490000,
                'thumbnail'   => 'https://images.unsplash.com/photo-1496747611176-887f0a8a08d8?w=800&h=1000&fit=crop&q=80',
                'category'    => 'dam',
                'brand'       => 'H&M',
                'gender'      => 'women',
                'is_featured' => false,
                'views_count' => 267,
                'images'      => [
                    'https://images.unsplash.com/photo-1496747611176-887f0a8a08d8?w=800&h=1000&fit=crop&q=80',
                ],
                'colors'      => ['Đỏ', 'Xanh rêu', 'Nâu'],
                'sizes'       => ['S', 'M', 'L'],
                'tags'        => ['dao-pho', 'xu-huong'],
            ],
            [
                'name'        => 'Váy Jean Chữ A Mini',
                'slug'        => 'vay-jean-chu-a-mini-levis',
                'description' => "Váy jean Levi's dáng chữ A mini trẻ trung, chất denim dày dặn co giãn nhẹ. Hàng nút đồng phía trước, cạp cao tôn vóc dáng. Wash nhẹ tạo độ vintage tự nhiên. Phối cùng áo thun hoặc áo sơ mi đều đẹp.",
                'price'       => 690000,
                'thumbnail'   => 'https://images.unsplash.com/photo-1583496661160-fb5886a0aaaa?w=800&h=1000&fit=crop&q=80',
                'category'    => 'vay',
                'brand'       => "Levi's",
                'gender'      => 'women',
                'is_featured' => false,
                'views_count' => 189,
                'images'      => [
                    'https://images.unsplash.com/photo-1583496661160-fb5886a0aaaa?w=800&h=1000&fit=crop&q=80',
                ],
                'colors'      => ['Xanh dương', 'Đen'],
                'sizes'       => ['XS', 'S', 'M', 'L'],
                'tags'        => ['casual', 'dao-pho'],
            ],
        ];
    }
}
