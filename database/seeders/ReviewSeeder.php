<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        $reviews = [
            // ── Customer 1 ──────────────────────────────────────────────────────
            [
                'user'       => 'customer1@example.com',
                'product'    => 'ao-thun-nam-basic-uniqlo',
                'rating'     => 5,
                'comment'    => 'Áo rất mềm, chất liệu cotton tốt, mặc thoáng mát cả ngày. Đúng size, đường may chắc chắn không bị xổ. Đã mua lần 2 và sẽ tiếp tục ủng hộ!',
            ],
            [
                'user'       => 'customer1@example.com',
                'product'    => 'ao-polo-nam-dri-fit-nike',
                'rating'     => 4,
                'comment'    => 'Vải Dri-FIT thấm hút mồ hôi tốt, mặc đi thể thao hoặc dạo phố đều ổn. Giao hàng nhanh, đóng gói cẩn thận. Màu thực tế đẹp hơn ảnh một chút.',
            ],
            [
                'user'       => 'customer1@example.com',
                'product'    => 'quan-jeans-nam-slim-511-levis',
                'rating'     => 5,
                'comment'    => "Jeans Levi's 511 chất lượng luôn không cần bàn cãi. Form slim fit chuẩn, vải dày dặn bền chắc. Đây là lần thứ 3 tôi mua model này, không bao giờ thất vọng.",
            ],
            [
                'user'       => 'customer1@example.com',
                'product'    => 'ao-hoodie-classic-champion',
                'rating'     => 4,
                'comment'    => 'Hoodie ấm và mềm, mặc rất thoải mái. Logo thêu nổi đẹp, không bong tróc sau giặt. Lưu ý: size hơi to hơn bình thường, nên xuống 1 size.',
            ],
            [
                'user'       => 'customer1@example.com',
                'product'    => 'ao-khoac-gio-chong-nuoc-tnf',
                'rating'     => 5,
                'comment'    => 'Áo khoác nhẹ nhàng nhưng chắn gió và mưa nhỏ rất tốt. Đã mặc leo núi ở Sapa, không thấm nước dù mưa phùn cả ngày. Rất đáng tiền!',
            ],

            // ── Customer 2 ──────────────────────────────────────────────────────
            [
                'user'       => 'customer2@example.com',
                'product'    => 'dam-maxi-hoa-nhi-zara',
                'rating'     => 5,
                'comment'    => 'Đầm đẹp hơn ảnh rất nhiều! Vải viscose mềm mịn, rũ đẹp, không nhăn. Mặc đi biển nhận được vô số lời khen. Shop giao hàng nhanh, đóng gói kỹ.',
            ],
            [
                'user'       => 'customer2@example.com',
                'product'    => 'ao-croptop-nu-cuc-boc-hm',
                'rating'     => 4,
                'comment'    => 'Áo xinh xắn, chất cotton linen mát mẻ rất phù hợp mùa hè. Cúc bọc tuy hơi khó cài nhưng tạo điểm nhấn thời trang. Phối với jeans trông cực trendy.',
            ],
            [
                'user'       => 'customer2@example.com',
                'product'    => 'quan-jeans-nu-skinny-721-levis',
                'rating'     => 5,
                'comment'    => "Levi's 721 là model jeans yêu thích của mình! Cạp cao tôn dáng và che được vòng 2. Co giãn tốt, dễ di chuyển suốt ngày dài. Màu wash không phai sau nhiều lần giặt.",
            ],
            [
                'user'       => 'customer2@example.com',
                'product'    => 'vay-tennis-nu-adidas',
                'rating'     => 4,
                'comment'    => 'Váy thiết kế đẹp, có quần short bên trong tiện dụng khi vận động. Vải thoáng mát, phù hợp tập tennis và yoga. Giao hàng đúng hẹn, sản phẩm y hình.',
            ],
            [
                'user'       => 'customer2@example.com',
                'product'    => 'quan-jogger-tech-fleece-nike',
                'rating'     => 5,
                'comment'    => 'Quần chất lượng cao, form tapered đẹp tôn dáng. Vải tech fleece nhẹ mà vẫn ấm, không bị nặng nề. Túi zip sau tiện lợi đựng điện thoại khi chạy bộ.',
            ],

            // ── Customer 3 ──────────────────────────────────────────────────────
            [
                'user'       => 'customer3@example.com',
                'product'    => 'ao-thun-tron-unisex-uniqlo',
                'rating'     => 5,
                'comment'    => 'Áo thun Uniqlo không bao giờ làm thất vọng. Cotton supima mịn như lụa, không nhăn, không phai màu sau giặt máy. Đã mua cả 5 màu, giá rất hợp lý.',
            ],
            [
                'user'       => 'customer3@example.com',
                'product'    => 'ao-khoac-bomber-nam-adidas',
                'rating'     => 4,
                'comment'    => 'Áo bomber đẹp, chất nylon nhẹ và chống gió tốt. Logo 3 sọc Adidas trên tay áo nổi bật. Lưu ý: nên giặt tay hoặc cho vào túi lưới khi giặt máy.',
            ],
            [
                'user'       => 'customer3@example.com',
                'product'    => 'ao-sweatshirt-trefoil-adidas',
                'rating'     => 5,
                'comment'    => 'Adidas Originals luôn đảm bảo chất lượng xứng tầm thương hiệu. Logo Trefoil thêu sắc nét, vải french terry dày đẹp, bo cổ và gấu không giãn. Mua thêm màu trắng nữa!',
            ],
            [
                'user'       => 'customer3@example.com',
                'product'    => 'ao-blazer-nam-slim-zara',
                'rating'     => 4,
                'comment'    => 'Blazer form slim đẹp, chất liệu pha len kháng nhăn tốt. Mặc đi làm và đi ăn nhận được nhiều lời khen. Lưu ý: không giặt máy, phải giặt khô hoặc tay.',
            ],
            [
                'user'       => 'customer3@example.com',
                'product'    => 'ao-phao-nu-ultra-light-uniqlo',
                'rating'     => 5,
                'comment'    => 'Sản phẩm xuất sắc! Cực kỳ nhẹ chỉ 200g mà ấm vô cùng nhờ lông vũ goose down. Gấp gọn vào túi nhỏ kèm theo rất tiện. Đã mua thêm 1 chiếc làm quà tặng.',
            ],
        ];

        foreach ($reviews as $data) {
            $user    = User::where('email', $data['user'])->first();
            $product = Product::where('slug', $data['product'])->first();

            if (! $user || ! $product) {
                continue;
            }

            Review::firstOrCreate(
                ['user_id' => $user->id, 'product_id' => $product->id],
                ['rating' => $data['rating'], 'comment' => $data['comment']]
            );
        }
    }
}
