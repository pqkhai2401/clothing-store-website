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
    // Abbreviations for SKU generation
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

            // Product images
            if ($product->wasRecentlyCreated) {
                foreach ($data['images'] as $imageUrl) {
                    ProductImage::create([
                        'product_id' => $product->id,
                        'image'      => $imageUrl,
                    ]);
                }

                // Tags
                $tagIds = collect($data['tags'])
                    ->map(fn ($slug) => $tags[$slug] ?? null)
                    ->filter()
                    ->values()
                    ->toArray();
                $product->tags()->sync($tagIds);

                // Variants: color × size
                foreach ($data['colors'] as $colorName) {
                    $colorId   = $colors[$colorName] ?? null;
                    $colorCode = $this->colorAbbr[$colorName] ?? 'UNK';

                    if (! $colorId) {
                        continue;
                    }

                    foreach ($data['sizes'] as $sizeName) {
                        $sizeId = $sizes[$sizeName] ?? null;

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
            // ─── 1. Áo Thun Nam Basic Tee – Uniqlo ───────────────────────────────
            [
                'name'        => 'Áo Thun Nam Basic Tee',
                'slug'        => 'ao-thun-nam-basic-uniqlo',
                'description' => 'Áo thun nam basic từ Uniqlo làm từ chất liệu cotton 100% cao cấp, mềm mại và thoáng khí. Thiết kế đơn giản với cổ tròn, phù hợp mặc hàng ngày hoặc phối layering. Vải được xử lý chống co rút, giữ form tốt sau nhiều lần giặt. Đường may phẳng mịn, không gây kích ứng da. Sản phẩm lý tưởng cho phong cách casual và minimalist.',
                'price'       => 199000,
                'thumbnail'   => 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?w=800&h=1000&fit=crop&q=80',
                'category'    => 'ao-thun',
                'brand'       => 'Uniqlo',
                'gender'      => 'men',
                'is_featured' => true,
                'views_count' => 4821,
                'images'      => [
                    'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?w=800&h=1000&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1583743814966-8936f5b7be1a?w=800&h=1000&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1562157873-818bc0726f68?w=800&h=1000&fit=crop&q=80',
                ],
                'colors'      => ['Trắng', 'Đen', 'Xám'],
                'sizes'       => ['S', 'M', 'L', 'XL'],
                'tags'        => ['hang-moi', 'casual'],
            ],

            // ─── 2. Áo Polo Nam Dri-FIT – Nike ───────────────────────────────────
            [
                'name'        => 'Áo Polo Nam Dri-FIT Piqué',
                'slug'        => 'ao-polo-nam-dri-fit-nike',
                'description' => 'Áo polo Nike Dri-FIT với công nghệ thấm hút mồ hôi tiên tiến, giúp bạn luôn khô thoáng trong các hoạt động thể thao và giải trí. Chất vải piqué cao cấp tạo cảm giác thoải mái, phong cách năng động. Cổ bẻ hai cúc, tà áo thẳng có thể mặc trong hoặc ngoài quần. Phù hợp cho sân golf, gym hoặc những buổi gặp gỡ thân thiện.',
                'price'       => 450000,
                'thumbnail'   => 'https://images.unsplash.com/photo-1603252109360-909baaf261ae?w=800&h=1000&fit=crop&q=80',
                'category'    => 'ao-polo',
                'brand'       => 'Nike',
                'gender'      => 'men',
                'is_featured' => true,
                'views_count' => 3654,
                'images'      => [
                    'https://images.unsplash.com/photo-1603252109360-909baaf261ae?w=800&h=1000&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1600269452121-4f2416e55c28?w=800&h=1000&fit=crop&q=80',
                ],
                'colors'      => ['Xanh navy', 'Trắng', 'Đen'],
                'sizes'       => ['S', 'M', 'L', 'XL'],
                'tags'        => ['the-thao', 'best-seller'],
            ],

            // ─── 3. Áo Hoodie Classic Unisex – Champion ───────────────────────────
            [
                'name'        => 'Áo Hoodie Classic Unisex',
                'slug'        => 'ao-hoodie-classic-champion',
                'description' => 'Áo hoodie Champion với chất liệu fleece dày dặn, giữ ấm hoàn hảo trong những ngày se lạnh. Logo chữ C thêu nổi bật trên ngực trái là điểm nhấn thương hiệu mang tính biểu tượng. Túi kangaroo rộng rãi, dây rút mũ điều chỉnh được. Thiết kế unisex phù hợp cho cả nam lẫn nữ. Lý tưởng cho phong cách streetwear và casual.',
                'price'       => 650000,
                'thumbnail'   => 'https://images.unsplash.com/photo-1556821840-3a63f15732ce?w=800&h=1000&fit=crop&q=80',
                'category'    => 'ao-hoodie-sweatshirt',
                'brand'       => 'Champion',
                'gender'      => 'unisex',
                'is_featured' => true,
                'views_count' => 5120,
                'images'      => [
                    'https://images.unsplash.com/photo-1556821840-3a63f15732ce?w=800&h=1000&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=800&h=1000&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1591047139829-d91aecb6caea?w=800&h=1000&fit=crop&q=80',
                ],
                'colors'      => ['Xám', 'Đen', 'Hồng'],
                'sizes'       => ['S', 'M', 'L', 'XL'],
                'tags'        => ['xu-huong', 'casual'],
            ],

            // ─── 4. Quần Jeans Nam Slim Fit 511 – Levi's ─────────────────────────
            [
                'name'        => "Quần Jeans Nam Slim Fit 511",
                'slug'        => 'quan-jeans-nam-slim-511-levis',
                'description' => "Quần jeans Levi's 511 Slim Fit kinh điển với form ôm vừa vặn, không quá chật cũng không quá rộng. Chất liệu denim cao cấp 99% cotton pha 1% elastane mang lại sự thoải mái và độ bền vượt trội. Đường may nổi màu vàng đặc trưng, khóa kéo YKK chắc chắn. Sản phẩm đã qua xử lý wash tạo độ phai màu tự nhiên. Phù hợp mọi dịp từ đi học, đi làm đến dạo phố.",
                'price'       => 990000,
                'thumbnail'   => 'https://images.unsplash.com/photo-1542272604-787c3835535d?w=800&h=1000&fit=crop&q=80',
                'category'    => 'quan-jeans',
                'brand'       => "Levi's",
                'gender'      => 'men',
                'is_featured' => false,
                'views_count' => 2890,
                'images'      => [
                    'https://images.unsplash.com/photo-1542272604-787c3835535d?w=800&h=1000&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1543087903-1ac2ec7aa8c5?w=800&h=1000&fit=crop&q=80',
                ],
                'colors'      => ['Xanh navy', 'Đen'],
                'sizes'       => ['S', 'M', 'L', 'XL'],
                'tags'        => ['best-seller', 'dao-pho'],
            ],

            // ─── 5. Áo Khoác Bomber Nam Essential – Adidas ────────────────────────
            [
                'name'        => 'Áo Khoác Bomber Nam Essential',
                'slug'        => 'ao-khoac-bomber-nam-adidas',
                'description' => 'Áo khoác bomber Adidas với kiểu dáng cổ điển được làm mới bằng chất liệu nylon chống nước nhẹ. Logo 3 sọc Adidas trên tay áo tạo điểm nhấn thương hiệu. Lớp lót fleece mỏng giữ ấm mà không gây nặng nề. Túi bên có khóa zip bảo vệ đồ vật. Thiết kế phù hợp cho mùa thu đông hoặc những ngày se lạnh quanh năm.',
                'price'       => 1290000,
                'thumbnail'   => 'https://images.unsplash.com/photo-1551537482-f2075a1d41f2?w=800&h=1000&fit=crop&q=80',
                'category'    => 'ao-khoac',
                'brand'       => 'Adidas',
                'gender'      => 'men',
                'is_featured' => true,
                'views_count' => 3210,
                'images'      => [
                    'https://images.unsplash.com/photo-1551537482-f2075a1d41f2?w=800&h=1000&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1591047139829-d91aecb6caea?w=800&h=1000&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1547949003-9792a18a2601?w=800&h=1000&fit=crop&q=80',
                ],
                'colors'      => ['Đen', 'Xanh navy', 'Xám'],
                'sizes'       => ['M', 'L', 'XL'],
                'tags'        => ['xu-huong', 'the-thao'],
            ],

            // ─── 6. Đầm Maxi Hoa Nhí Nữ – Zara ──────────────────────────────────
            [
                'name'        => 'Đầm Maxi Hoa Nhí Nữ',
                'slug'        => 'dam-maxi-hoa-nhi-zara',
                'description' => 'Đầm maxi Zara với họa tiết hoa nhí nhỏ xinh, tạo nên vẻ nữ tính và thanh lịch. Chất liệu vải viscose mềm nhẹ, rũ tự nhiên ôm theo dáng người. Thiết kế cổ V nhẹ, tay bồng nhẹ tạo điểm nhấn tinh tế. Dài qua gối, phù hợp đi biển, dạo phố hoặc tiệc ngoài trời. Đây là sản phẩm bán chạy nhất trong bộ sưu tập hè 2025.',
                'price'       => 690000,
                'thumbnail'   => 'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?w=800&h=1000&fit=crop&q=80',
                'category'    => 'dam',
                'brand'       => 'Zara',
                'gender'      => 'women',
                'is_featured' => true,
                'views_count' => 6740,
                'images'      => [
                    'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?w=800&h=1000&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1496747611176-887f0a8a08d8?w=800&h=1000&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1572804013427-4d7ca7268217?w=800&h=1000&fit=crop&q=80',
                ],
                'colors'      => ['Hồng', 'Be', 'Trắng'],
                'sizes'       => ['S', 'M', 'L'],
                'tags'        => ['mua-he', 'hang-moi', 'giam-gia'],
            ],

            // ─── 7. Áo Croptop Nữ Cúc Bọc – H&M ─────────────────────────────────
            [
                'name'        => 'Áo Croptop Nữ Cúc Bọc',
                'slug'        => 'ao-croptop-nu-cuc-boc-hm',
                'description' => 'Áo croptop H&M với hàng cúc bọc vải dọc thân tạo điểm nhấn thời trang độc đáo. Chất cotton pha linen nhẹ mát, thoáng khí lý tưởng cho mùa hè. Thân áo ngắn, dễ phối cùng quần jeans, quần short hay chân váy. Cổ tròn rộng thoải mái, tay áo không tay hoặc tay lỡ. Phong cách trẻ trung, năng động cho các bạn gái yêu thích thời trang đường phố.',
                'price'       => 250000,
                'thumbnail'   => 'https://images.unsplash.com/photo-1539109136881-3be0616acf4b?w=800&h=1000&fit=crop&q=80',
                'category'    => 'ao-thun',
                'brand'       => 'H&M',
                'gender'      => 'women',
                'is_featured' => false,
                'views_count' => 2340,
                'images'      => [
                    'https://images.unsplash.com/photo-1539109136881-3be0616acf4b?w=800&h=1000&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1485518882345-15568b007407?w=800&h=1000&fit=crop&q=80',
                ],
                'colors'      => ['Trắng', 'Đen', 'Hồng'],
                'sizes'       => ['XS', 'S', 'M'],
                'tags'        => ['xu-huong', 'mua-he'],
            ],

            // ─── 8. Quần Jeans Nữ Skinny 721 – Levi's ────────────────────────────
            [
                'name'        => "Quần Jeans Nữ Skinny 721 High Rise",
                'slug'        => 'quan-jeans-nu-skinny-721-levis',
                'description' => "Quần jeans Levi's 721 với form skinny ôm sát cùng cạp cao tôn vóc dáng, kéo dài đôi chân. Chất denim co giãn 4 chiều đảm bảo thoải mái cho mọi chuyển động. Đường wash vintage tạo độ phai màu tự nhiên rất thời trang. Cạp cao che khuyết điểm vòng 2 hiệu quả. Phù hợp phối cùng áo thun, áo croptop hay blazer cho vô vàn set đồ khác nhau.",
                'price'       => 850000,
                'thumbnail'   => 'https://images.unsplash.com/photo-1541099649105-f69ad21f3246?w=800&h=1000&fit=crop&q=80',
                'category'    => 'quan-jeans',
                'brand'       => "Levi's",
                'gender'      => 'women',
                'is_featured' => false,
                'views_count' => 3870,
                'images'      => [
                    'https://images.unsplash.com/photo-1541099649105-f69ad21f3246?w=800&h=1000&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1544441893-675973e31985?w=800&h=1000&fit=crop&q=80',
                ],
                'colors'      => ['Xanh navy', 'Đen'],
                'sizes'       => ['XS', 'S', 'M', 'L'],
                'tags'        => ['best-seller', 'dao-pho'],
            ],

            // ─── 9. Áo Sơ Mi Nữ Linen Premium – Zara ────────────────────────────
            [
                'name'        => 'Áo Sơ Mi Nữ Linen Premium',
                'slug'        => 'ao-so-mi-nu-linen-zara',
                'description' => 'Áo sơ mi nữ Zara từ chất liệu linen tự nhiên cao cấp, thoáng mát và thân thiện với da. Thiết kế oversize nhẹ tạo dáng suôn đẹp, có thể buộc vạt trước hoặc mặc trong quần. Cổ áo sơ mi cổ điển, tay áo dài có thể xắn lên. Màu sắc trung tính dễ phối đồ. Lý tưởng cho môi trường công sở hoặc những buổi gặp gỡ quan trọng.',
                'price'       => 490000,
                'thumbnail'   => 'https://images.unsplash.com/photo-1434389677669-e08b4cac3105?w=800&h=1000&fit=crop&q=80',
                'category'    => 'ao-so-mi',
                'brand'       => 'Zara',
                'gender'      => 'women',
                'is_featured' => false,
                'views_count' => 1950,
                'images'      => [
                    'https://images.unsplash.com/photo-1434389677669-e08b4cac3105?w=800&h=1000&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1487222477894-8943e31ef7b2?w=800&h=1000&fit=crop&q=80',
                ],
                'colors'      => ['Trắng', 'Be', 'Xanh dương'],
                'sizes'       => ['S', 'M', 'L'],
                'tags'        => ['cong-so', 'cao-cap', 'giam-gia'],
            ],

            // ─── 10. Váy Tennis Nữ Thể Thao – Adidas ─────────────────────────────
            [
                'name'        => 'Váy Tennis Nữ Thể Thao',
                'slug'        => 'vay-tennis-nu-adidas',
                'description' => 'Váy tennis Adidas thiết kế hai trong một với quần short bên trong giúp vận động tự do. Chất liệu Aeroready thấm hút mồ hôi nhanh, duy trì cảm giác khô thoáng. Cạp chun co giãn thoải mái, túi nhỏ giấu mặt sau tiện dụng. Viền xếp ly nhẹ tạo dáng váy xòe năng động. Không chỉ phù hợp cho sân tennis mà còn lý tưởng cho yoga, gym hay đi dạo.',
                'price'       => 550000,
                'thumbnail'   => 'https://images.unsplash.com/photo-1568252542512-9fe8fe9c87bb?w=800&h=1000&fit=crop&q=80',
                'category'    => 'vay',
                'brand'       => 'Adidas',
                'gender'      => 'women',
                'is_featured' => false,
                'views_count' => 2100,
                'images'      => [
                    'https://images.unsplash.com/photo-1568252542512-9fe8fe9c87bb?w=800&h=1000&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1520013817300-1f4c1cb245ef?w=800&h=1000&fit=crop&q=80',
                ],
                'colors'      => ['Trắng', 'Đen', 'Hồng'],
                'sizes'       => ['XS', 'S', 'M', 'L'],
                'tags'        => ['the-thao', 'mua-he'],
            ],

            // ─── 11. Áo Sweatshirt Trefoil Unisex – Adidas ───────────────────────
            [
                'name'        => 'Áo Sweatshirt Trefoil Unisex',
                'slug'        => 'ao-sweatshirt-trefoil-adidas',
                'description' => 'Áo sweatshirt Adidas Originals với logo Trefoil 3 lá biểu tượng được thêu nổi trên ngực. Chất liệu french terry dày vừa, mặt trong có lông mỏng giữ ấm. Cổ tròn co giãn tốt, gấu và tay bo cổ điển. Thiết kế unisex phù hợp cả nam và nữ. Đây là item streetwear kinh điển không bao giờ lỗi mốt, dễ phối với jeans, jogger hay bất kỳ chiếc quần nào.',
                'price'       => 790000,
                'thumbnail'   => 'https://images.unsplash.com/photo-1560343090-f0409e92791a?w=800&h=1000&fit=crop&q=80',
                'category'    => 'ao-hoodie-sweatshirt',
                'brand'       => 'Adidas',
                'gender'      => 'unisex',
                'is_featured' => false,
                'views_count' => 2750,
                'images'      => [
                    'https://images.unsplash.com/photo-1560343090-f0409e92791a?w=800&h=1000&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1545291730-faff8ca1d4b0?w=800&h=1000&fit=crop&q=80',
                ],
                'colors'      => ['Đen', 'Trắng', 'Xám'],
                'sizes'       => ['S', 'M', 'L', 'XL'],
                'tags'        => ['xu-huong', 'casual', 'giam-gia'],
            ],

            // ─── 12. Quần Jogger Tech Fleece – Nike ──────────────────────────────
            [
                'name'        => 'Quần Jogger Tech Fleece',
                'slug'        => 'quan-jogger-tech-fleece-nike',
                'description' => 'Quần jogger Nike Tech Fleece với công nghệ vải 2 lớp nhẹ mà vẫn ấm, là sự kết hợp hoàn hảo giữa thể thao và streetwear. Form dáng tapered hẹp dần từ trên xuống gấu, tôn dáng đẹp. Cạp chun dây rút tiện lợi, hai túi bên sâu rộng và túi phía sau có khóa zip. Gấu quần ôm gọn tạo điểm nhấn. Phù hợp tập gym, chạy bộ hay đi dạo.',
                'price'       => 890000,
                'thumbnail'   => 'https://images.unsplash.com/photo-1552902865-b72c031ac5ea?w=800&h=1000&fit=crop&q=80',
                'category'    => 'quan-jogger',
                'brand'       => 'Nike',
                'gender'      => 'unisex',
                'is_featured' => true,
                'views_count' => 4120,
                'images'      => [
                    'https://images.unsplash.com/photo-1552902865-b72c031ac5ea?w=800&h=1000&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1618354691438-25bc04584c23?w=800&h=1000&fit=crop&q=80',
                ],
                'colors'      => ['Đen', 'Xám', 'Xanh navy'],
                'sizes'       => ['S', 'M', 'L', 'XL'],
                'tags'        => ['the-thao', 'best-seller'],
            ],

            // ─── 13. Áo Thun Trơn Cổ Tròn Unisex – Uniqlo ───────────────────────
            [
                'name'        => 'Áo Thun Trơn Cổ Tròn Unisex',
                'slug'        => 'ao-thun-tron-unisex-uniqlo',
                'description' => 'Áo thun trơn Uniqlo – item tủ cơ bản không thể thiếu trong mọi tủ đồ. Chất cotton supima cao cấp mịn như lụa, cực kỳ thoáng khí và bền màu sau nhiều lần giặt. Cổ tròn vừa phải không bị giãn theo thời gian. Sản phẩm có đa dạng màu sắc dễ phối đồ. Phù hợp cho cả nam và nữ với nhiều size đa dạng từ XS đến XXL.',
                'price'       => 199000,
                'thumbnail'   => 'https://images.unsplash.com/photo-1583743814966-8936f5b7be1a?w=800&h=1000&fit=crop&q=80',
                'category'    => 'ao-thun',
                'brand'       => 'Uniqlo',
                'gender'      => 'unisex',
                'is_featured' => false,
                'views_count' => 7350,
                'images'      => [
                    'https://images.unsplash.com/photo-1583743814966-8936f5b7be1a?w=800&h=1000&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?w=800&h=1000&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1562157873-818bc0726f68?w=800&h=1000&fit=crop&q=80',
                ],
                'colors'      => ['Đen', 'Trắng', 'Xám', 'Xanh navy', 'Đỏ'],
                'sizes'       => ['XS', 'S', 'M', 'L', 'XL', 'XXL'],
                'tags'        => ['hang-moi', 'casual', 'best-seller'],
            ],

            // ─── 14. Áo Khoác Gió Chống Nước – The North Face ────────────────────
            [
                'name'        => 'Áo Khoác Gió Chống Nước Resolve',
                'slug'        => 'ao-khoac-gio-chong-nuoc-tnf',
                'description' => 'Áo khoác gió The North Face Resolve với công nghệ DryVent™ chống nước hoàn toàn, thoát ẩm tốt ngay cả khi vận động mạnh. Trọng lượng siêu nhẹ có thể gấp gọn vào túi nhỏ. Các đường may dán kín ngăn nước thấm vào. Mũ đính liền điều chỉnh được, gấu và cổ tay bo co giãn. Sản phẩm cao cấp lý tưởng cho leo núi, trekking và du lịch ngoài trời.',
                'price'       => 2490000,
                'thumbnail'   => 'https://images.unsplash.com/photo-1551698618-1dfe5d97d256?w=800&h=1000&fit=crop&q=80',
                'category'    => 'ao-khoac',
                'brand'       => 'The North Face',
                'gender'      => 'unisex',
                'is_featured' => true,
                'views_count' => 3980,
                'images'      => [
                    'https://images.unsplash.com/photo-1551698618-1dfe5d97d256?w=800&h=1000&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1521150932951-303a95503ed3?w=800&h=1000&fit=crop&q=80',
                ],
                'colors'      => ['Đen', 'Xanh dương', 'Cam'],
                'sizes'       => ['S', 'M', 'L', 'XL'],
                'tags'        => ['da-ngoai', 'cao-cap', 'giam-gia'],
            ],

            // ─── 15. Áo Blazer Nam Slim Fit – Zara ───────────────────────────────
            [
                'name'        => 'Áo Blazer Nam Slim Fit',
                'slug'        => 'ao-blazer-nam-slim-zara',
                'description' => 'Áo blazer Zara Man với form slim fit hiện đại, ôm body đẹp mà không gò bó. Chất liệu pha len cao cấp giữ form tốt, kháng nhăn tốt. Thiết kế 2 cúc, ve áo đơn giản thanh lịch. Túi ngực giả và 2 túi có nắp bên ngoài. Phù hợp cho công sở, sự kiện quan trọng hoặc hẹn hò. Dễ phối với áo sơ mi, áo thun hay quần tây.',
                'price'       => 1190000,
                'thumbnail'   => 'https://images.unsplash.com/photo-1617196034735-f6d5605f8b95?w=800&h=1000&fit=crop&q=80',
                'category'    => 'ao-blazer',
                'brand'       => 'Zara',
                'gender'      => 'men',
                'is_featured' => false,
                'views_count' => 1640,
                'images'      => [
                    'https://images.unsplash.com/photo-1617196034735-f6d5605f8b95?w=800&h=1000&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1507679799987-c73779587ccf?w=800&h=1000&fit=crop&q=80',
                ],
                'colors'      => ['Đen', 'Xám', 'Nâu'],
                'sizes'       => ['M', 'L', 'XL'],
                'tags'        => ['cong-so', 'cao-cap'],
            ],

            // ─── 16. Áo Len Cổ Lọ Nữ Premium – Uniqlo ───────────────────────────
            [
                'name'        => 'Áo Len Cổ Lọ Nữ Premium Lambswool',
                'slug'        => 'ao-len-co-lo-nu-uniqlo',
                'description' => 'Áo len cổ lọ Uniqlo làm từ len lambswool cao cấp, cực kỳ mềm mại và không gây ngứa da. Cổ lọ cao giữ ấm vùng cổ hiệu quả trong mùa đông. Form body vừa vặn tôn dáng, không tạo cảm giác phồng to. Màu sắc trung tính dễ phối với mọi loại trang phục. Sản phẩm có thể giặt máy ở chế độ nhẹ, dễ bảo quản.',
                'price'       => 690000,
                'thumbnail'   => 'https://images.unsplash.com/photo-1604176354204-926bd6f1cb6e?w=800&h=1000&fit=crop&q=80',
                'category'    => 'ao-len',
                'brand'       => 'Uniqlo',
                'gender'      => 'women',
                'is_featured' => false,
                'views_count' => 2280,
                'images'      => [
                    'https://images.unsplash.com/photo-1604176354204-926bd6f1cb6e?w=800&h=1000&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1564859228273-274232fdb516?w=800&h=1000&fit=crop&q=80',
                ],
                'colors'      => ['Be', 'Trắng', 'Xám', 'Hồng'],
                'sizes'       => ['S', 'M', 'L'],
                'tags'        => ['mua-dong', 'cao-cap'],
            ],

            // ─── 17. Quần Short Thể Thao Dri-FIT Nam – Nike ──────────────────────
            [
                'name'        => 'Quần Short Thể Thao Dri-FIT Nam',
                'slug'        => 'quan-short-the-thao-nam-nike',
                'description' => 'Quần short thể thao Nike Dri-FIT với công nghệ thấm hút mồ hôi tối ưu, duy trì cảm giác khô ráo khi tập luyện. Vải nhẹ co giãn 4 chiều không hạn chế chuyển động. Cạp chun dây rút điều chỉnh vòng eo. Túi bên có khóa zip bảo vệ điện thoại và chìa khóa. Độ dài đến giữa đùi phù hợp cho chạy bộ, gym, bóng đá hay các hoạt động ngoài trời.',
                'price'       => 390000,
                'thumbnail'   => 'https://images.unsplash.com/photo-1509631928351-a73fc7e0c9a0?w=800&h=1000&fit=crop&q=80',
                'category'    => 'quan-short',
                'brand'       => 'Nike',
                'gender'      => 'men',
                'is_featured' => false,
                'views_count' => 3100,
                'images'      => [
                    'https://images.unsplash.com/photo-1509631928351-a73fc7e0c9a0?w=800&h=1000&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1519861531473-77a0de6a4c23?w=800&h=1000&fit=crop&q=80',
                ],
                'colors'      => ['Đen', 'Xám', 'Xanh dương'],
                'sizes'       => ['S', 'M', 'L', 'XL'],
                'tags'        => ['the-thao', 'mua-he', 'giam-gia'],
            ],

            // ─── 18. Đầm Body Midi Nữ – Zara ─────────────────────────────────────
            [
                'name'        => 'Đầm Body Midi Nữ',
                'slug'        => 'dam-body-midi-nu-zara',
                'description' => 'Đầm body midi Zara với thiết kế ôm sát cơ thể tôn lên vẻ quyến rũ và thanh lịch. Chất liệu jersey co giãn cao cấp ôm theo từng đường cong, mang lại cảm giác như thứ hai trên da. Dài qua đầu gối, phù hợp cho dạ tiệc, sự kiện trang trọng hoặc đi chơi buổi tối. Cổ tròn hoặc cổ V tùy lựa chọn. Có thể phối cùng blazer hay áo khoác ngắn để mặc công sở.',
                'price'       => 790000,
                'thumbnail'   => 'https://images.unsplash.com/photo-1495385794501-b51567a20da5?w=800&h=1000&fit=crop&q=80',
                'category'    => 'dam',
                'brand'       => 'Zara',
                'gender'      => 'women',
                'is_featured' => true,
                'views_count' => 4560,
                'images'      => [
                    'https://images.unsplash.com/photo-1495385794501-b51567a20da5?w=800&h=1000&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1572804013427-4d7ca7268217?w=800&h=1000&fit=crop&q=80',
                ],
                'colors'      => ['Đen', 'Đỏ', 'Be'],
                'sizes'       => ['XS', 'S', 'M', 'L'],
                'tags'        => ['dao-pho', 'xu-huong', 'cao-cap'],
            ],

            // ─── 19. Quần Tây Nam Âu Slim Fit – Zara ─────────────────────────────
            [
                'name'        => 'Quần Tây Nam Âu Slim Fit',
                'slug'        => 'quan-tay-nam-slim-zara',
                'description' => 'Quần tây Zara Man với form slim fit hiện đại, đường may tinh xảo thể hiện đẳng cấp. Chất liệu pha len polyester cao cấp kháng nhăn, thoáng khí và dễ bảo quản. Cạp âu có dây đai, khóa kéo ẩn phía trước thanh lịch. Túi xéo hai bên và túi mổ sau. Phù hợp cho môi trường công sở, sự kiện hoặc khi cần ăn mặc trang trọng.',
                'price'       => 790000,
                'thumbnail'   => 'https://images.unsplash.com/photo-1473966968600-fa801b869a1a?w=800&h=1000&fit=crop&q=80',
                'category'    => 'quan-tay',
                'brand'       => 'Zara',
                'gender'      => 'men',
                'is_featured' => false,
                'views_count' => 1820,
                'images'      => [
                    'https://images.unsplash.com/photo-1473966968600-fa801b869a1a?w=800&h=1000&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1594938298603-c8148c4b4357?w=800&h=1000&fit=crop&q=80',
                ],
                'colors'      => ['Đen', 'Xám', 'Nâu'],
                'sizes'       => ['M', 'L', 'XL'],
                'tags'        => ['cong-so', 'cao-cap', 'giam-gia'],
            ],

            // ─── 20. Áo Phao Nữ Ultra Light Down – Uniqlo ────────────────────────
            [
                'name'        => 'Áo Phao Nữ Ultra Light Down',
                'slug'        => 'ao-phao-nu-ultra-light-uniqlo',
                'description' => 'Áo phao Uniqlo Ultra Light Down nổi tiếng với trọng lượng cực nhẹ chỉ 200g nhưng giữ ấm vượt trội nhờ lông vũ 90% white goose down. Có thể gấp gọn vào túi kèm theo, tiện mang theo mọi lúc mọi nơi. Lớp vỏ ngoài bằng nylon siêu mỏng chống gió và kháng nước nhẹ. Thiết kế không mũ gọn gàng, phù hợp lót bên trong hoặc mặc ngoài. Màu sắc đa dạng, thời trang cả 4 mùa.',
                'price'       => 990000,
                'thumbnail'   => 'https://images.unsplash.com/photo-1547721064-da6cfb341d50?w=800&h=1000&fit=crop&q=80',
                'category'    => 'ao-khoac',
                'brand'       => 'Uniqlo',
                'gender'      => 'women',
                'is_featured' => true,
                'views_count' => 5890,
                'images'      => [
                    'https://images.unsplash.com/photo-1547721064-da6cfb341d50?w=800&h=1000&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1512436991641-6745cdb1723f?w=800&h=1000&fit=crop&q=80',
                ],
                'colors'      => ['Đen', 'Hồng', 'Be'],
                'sizes'       => ['S', 'M', 'L'],
                'tags'        => ['mua-dong', 'cao-cap', 'best-seller'],
            ],
        ];
    }
}
