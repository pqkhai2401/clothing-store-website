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
                    ['name' => 'Áo thun',    'slug' => 'nam-ao-thun'],
                    ['name' => 'Áo sơ mi',   'slug' => 'nam-ao-so-mi'],
                    ['name' => 'Áo polo',    'slug' => 'nam-ao-polo'],
                    ['name' => 'Áo hoodie',  'slug' => 'nam-ao-hoodie'],
                    ['name' => 'Áo khoác',   'slug' => 'nam-ao-khoac'],
                    ['name' => 'Áo blazer',  'slug' => 'nam-ao-blazer'],
                    ['name' => 'Quần jeans', 'slug' => 'nam-quan-jeans'],
                    ['name' => 'Quần tây',   'slug' => 'nam-quan-tay'],
                    ['name' => 'Quần short', 'slug' => 'nam-quan-short'],
                    ['name' => 'Quần jogger','slug' => 'nam-quan-jogger'],
                ],
            ],
            [
                'name'     => 'Nữ',
                'slug'     => 'nu',
                'children' => [
                    ['name' => 'Áo thun',    'slug' => 'nu-ao-thun'],
                    ['name' => 'Áo sơ mi',   'slug' => 'nu-ao-so-mi'],
                    ['name' => 'Áo polo',    'slug' => 'nu-ao-polo'],
                    ['name' => 'Áo hoodie',  'slug' => 'nu-ao-hoodie'],
                    ['name' => 'Áo khoác',   'slug' => 'nu-ao-khoac'],
                    ['name' => 'Áo len',     'slug' => 'nu-ao-len'],
                    ['name' => 'Quần jeans', 'slug' => 'nu-quan-jeans'],
                    ['name' => 'Quần tây',   'slug' => 'nu-quan-tay'],
                    ['name' => 'Đầm',        'slug' => 'nu-dam'],
                    ['name' => 'Váy',        'slug' => 'nu-vay'],
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
                        'name'      => $child['name'],
                        'parent_id' => $parent->id,
                        'status'    => true,
                    ]
                );
            }
        }
    }
}
