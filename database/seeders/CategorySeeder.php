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
                'name'     => 'Nam',
                'slug'     => 'nam',
                'children' => [
                    ['name' => 'Áo thun',    'slug' => 'nam-ao-thun',    'outfit_type' => 'top',       'style_group' => 'casual'],
                    ['name' => 'Áo sơ mi',   'slug' => 'nam-ao-so-mi',   'outfit_type' => 'top',       'style_group' => 'smart_casual'],
                    ['name' => 'Áo polo',    'slug' => 'nam-ao-polo',    'outfit_type' => 'top',       'style_group' => 'smart_casual'],
                    ['name' => 'Áo hoodie',  'slug' => 'nam-ao-hoodie',  'outfit_type' => 'top',       'style_group' => 'casual'],
                    ['name' => 'Áo khoác',   'slug' => 'nam-ao-khoac',   'outfit_type' => 'outerwear', 'style_group' => 'casual'],
                    ['name' => 'Áo blazer',  'slug' => 'nam-ao-blazer',  'outfit_type' => 'outerwear', 'style_group' => 'formal'],
                    ['name' => 'Quần jeans', 'slug' => 'nam-quan-jeans', 'outfit_type' => 'bottom',    'style_group' => 'smart_casual'],
                    ['name' => 'Quần tây',   'slug' => 'nam-quan-tay',   'outfit_type' => 'bottom',    'style_group' => 'formal'],
                    ['name' => 'Quần short', 'slug' => 'nam-quan-short', 'outfit_type' => 'bottom',    'style_group' => 'sporty'],
                    ['name' => 'Quần jogger','slug' => 'nam-quan-jogger','outfit_type' => 'bottom',    'style_group' => 'sporty'],
                ],
            ],
            [
                'name'     => 'Nữ',
                'slug'     => 'nu',
                'children' => [
                    ['name' => 'Áo thun',    'slug' => 'nu-ao-thun',    'outfit_type' => 'top',       'style_group' => 'casual'],
                    ['name' => 'Áo sơ mi',   'slug' => 'nu-ao-so-mi',   'outfit_type' => 'top',       'style_group' => 'smart_casual'],
                    ['name' => 'Áo polo',    'slug' => 'nu-ao-polo',    'outfit_type' => 'top',       'style_group' => 'smart_casual'],
                    ['name' => 'Áo hoodie',  'slug' => 'nu-ao-hoodie',  'outfit_type' => 'top',       'style_group' => 'casual'],
                    ['name' => 'Áo khoác',   'slug' => 'nu-ao-khoac',   'outfit_type' => 'outerwear', 'style_group' => 'casual'],
                    ['name' => 'Áo blazer',  'slug' => 'nu-ao-blazer',  'outfit_type' => 'outerwear', 'style_group' => 'formal'],
                    ['name' => 'Áo len',     'slug' => 'nu-ao-len',     'outfit_type' => 'top',       'style_group' => 'casual'],
                    ['name' => 'Quần jeans', 'slug' => 'nu-quan-jeans', 'outfit_type' => 'bottom',    'style_group' => 'smart_casual'],
                    ['name' => 'Quần tây',   'slug' => 'nu-quan-tay',   'outfit_type' => 'bottom',    'style_group' => 'formal'],
                    ['name' => 'Đầm',        'slug' => 'nu-dam',        'outfit_type' => 'dress',     'style_group' => 'smart_casual'],
                    ['name' => 'Váy',        'slug' => 'nu-vay',        'outfit_type' => 'skirt',     'style_group' => 'casual'],
                ],
            ],
        ];

        foreach ($structure as $parentData) {
            $parent = Category::updateOrCreate(
                ['slug' => $parentData['slug']],
                [
                    'name'      => $parentData['name'],
                    'parent_id' => null,
                    'status'    => true,
                ]
            );

            foreach ($parentData['children'] as $child) {
                Category::updateOrCreate(
                    ['slug' => $child['slug']],
                    [
                        'name'        => $child['name'],
                        'parent_id'   => $parent->id,
                        'outfit_type' => $child['outfit_type'],
                        'style_group' => $child['style_group'],
                        'status'      => true,
                    ]
                );
            }
        }
    }
}