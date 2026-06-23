<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Database\Seeder;

class WishlistSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            'customer1@example.com' => [
                'ao-khoac-gio-chong-nuoc-tnf',
                'quan-tay-nam-slim-zara',
                'ao-blazer-nam-slim-zara',
                'quan-jogger-tech-fleece-nike',
            ],
            'customer2@example.com' => [
                'dam-body-midi-nu-zara',
                'ao-len-co-lo-nu-uniqlo',
                'ao-so-mi-nu-linen-zara',
                'ao-phao-nu-ultra-light-uniqlo',
            ],
            'customer3@example.com' => [
                'ao-hoodie-classic-champion',
                'quan-jogger-tech-fleece-nike',
                'ao-thun-tron-unisex-uniqlo',
                'ao-khoac-bomber-nam-adidas',
            ],
        ];

        foreach ($data as $email => $productSlugs) {
            $user = User::where('email', $email)->first();
            if (! $user) {
                continue;
            }

            foreach ($productSlugs as $slug) {
                $product = Product::where('slug', $slug)->first();
                if (! $product) {
                    continue;
                }

                Wishlist::firstOrCreate([
                    'user_id'    => $user->id,
                    'product_id' => $product->id,
                ]);
            }
        }
    }
}
