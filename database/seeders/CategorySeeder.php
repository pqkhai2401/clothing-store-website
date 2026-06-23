<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $structure = [
            [
                'name' => 'Áo',
                'slug' => 'ao',
                'children' => [
                    ['name' => 'Áo thun', 'slug' => 'ao-thun'],
                    ['name' => 'Áo polo', 'slug' => 'ao-polo'],
                    ['name' => 'Áo sơ mi', 'slug' => 'ao-so-mi'],
                    ['name' => 'Áo hoodie & Sweatshirt', 'slug' => 'ao-hoodie-sweatshirt'],
                    ['name' => 'Áo khoác', 'slug' => 'ao-khoac'],
                    ['name' => 'Áo len', 'slug' => 'ao-len'],
                    ['name' => 'Áo blazer', 'slug' => 'ao-blazer'],
                ],
            ],
            [
                'name' => 'Quần',
                'slug' => 'quan',
                'children' => [
                    ['name' => 'Quần jeans', 'slug' => 'quan-jeans'],
                    ['name' => 'Quần short', 'slug' => 'quan-short'],
                    ['name' => 'Quần tây', 'slug' => 'quan-tay'],
                    ['name' => 'Quần jogger', 'slug' => 'quan-jogger'],
                ],
            ],
            [
                'name' => 'Đầm & Váy',
                'slug' => 'dam-vay',
                'children' => [
                    ['name' => 'Đầm', 'slug' => 'dam'],
                    ['name' => 'Váy', 'slug' => 'vay'],
                ],
            ],
            [
                'name' => 'Phụ kiện',
                'slug' => 'phu-kien',
                'children' => [
                    ['name' => 'Mũ & Nón', 'slug' => 'mu-non'],
                    ['name' => 'Túi xách', 'slug' => 'tui-xach'],
                    ['name' => 'Thắt lưng', 'slug' => 'that-lung'],
                ],
            ],
        ];

        foreach ($structure as $parentData) {
            $parent = Category::firstOrCreate(
                ['slug' => $parentData['slug']],
                ['name' => $parentData['name']]
            );

            foreach ($parentData['children'] as $child) {
                Category::firstOrCreate(
                    ['slug' => $child['slug']],
                    ['name' => $child['name'], 'parent_id' => $parent->id]
                );
            }
        }
    }
}
