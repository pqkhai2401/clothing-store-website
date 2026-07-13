<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Seeder;

class TagSeeder extends Seeder
{
    public function run(): void
    {
        $tags = [
            ['name' => 'Hàng mới',   'slug' => 'hang-moi'],
            ['name' => 'Best Seller', 'slug' => 'best-seller'],
            ['name' => 'Giảm giá',   'slug' => 'giam-gia'],
            ['name' => 'Xu hướng',   'slug' => 'xu-huong'],
            ['name' => 'Mùa xuân',   'slug' => 'mua-xuan'],
            ['name' => 'Mùa hè',     'slug' => 'mua-he'],
            ['name' => 'Mùa thu',    'slug' => 'mua-thu'],
            ['name' => 'Mùa đông',   'slug' => 'mua-dong'],
            ['name' => 'Thể thao',   'slug' => 'the-thao'],
            ['name' => 'Công sở',    'slug' => 'cong-so'],
            ['name' => 'Dạo phố',    'slug' => 'dao-pho'],
            ['name' => 'Dã ngoại',   'slug' => 'da-ngoai'],
            ['name' => 'Casual',     'slug' => 'casual'],
            ['name' => 'Cao cấp',    'slug' => 'cao-cap'],
        ];

        foreach ($tags as $tag) {
            Tag::firstOrCreate(
                ['slug' => $tag['slug']],
                ['name' => $tag['name']]
            );
        }
    }
}
