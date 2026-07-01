<?php

namespace Database\Seeders;

use App\Models\Size;
use Illuminate\Database\Seeder;

class SizeSeeder extends Seeder
{
    public function run(): void
    {
        $sizes = $this->catalogSizes();

        foreach ($sizes as $size) {
            Size::updateOrCreate(
                [
                    'name' => $size['name'],
                    'category_group' => $size['category_group'],
                ],
                $size + ['status' => 1]
            );
        }
    }

    private function catalogSizes(): array
    {
        return [
            // Cụm 1: Nhóm áo nam, form nam cần dải lớn hơn.
            ['name' => 'S', 'category_group' => 'ao_nam', 'sort_weight' => 20],
            ['name' => 'M', 'category_group' => 'ao_nam', 'sort_weight' => 30],
            ['name' => 'L', 'category_group' => 'ao_nam', 'sort_weight' => 40],
            ['name' => 'XL', 'category_group' => 'ao_nam', 'sort_weight' => 50],
            ['name' => 'XXL', 'category_group' => 'ao_nam', 'sort_weight' => 60],
            ['name' => 'XXXL', 'category_group' => 'ao_nam', 'sort_weight' => 70],

            // Cụm 1: Nhóm áo nữ, cần dải nhỏ cho croptop và form ôm.
            ['name' => 'XXS', 'category_group' => 'ao_nu', 'sort_weight' => 5],
            ['name' => 'XS', 'category_group' => 'ao_nu', 'sort_weight' => 10],
            ['name' => 'S', 'category_group' => 'ao_nu', 'sort_weight' => 20],
            ['name' => 'M', 'category_group' => 'ao_nu', 'sort_weight' => 30],
            ['name' => 'L', 'category_group' => 'ao_nu', 'sort_weight' => 40],
            ['name' => 'XL', 'category_group' => 'ao_nu', 'sort_weight' => 50],

            // Cụm 2: Quần nam quy đổi từ size số sang size chữ.
            ['name' => 'S', 'category_group' => 'quan_nam', 'sort_weight' => 20],
            ['name' => 'M', 'category_group' => 'quan_nam', 'sort_weight' => 30],
            ['name' => 'L', 'category_group' => 'quan_nam', 'sort_weight' => 40],
            ['name' => 'XL', 'category_group' => 'quan_nam', 'sort_weight' => 50],

            // Cụm 2: Quần/váy nữ theo vòng eo.
            ['name' => 'XS', 'category_group' => 'quan_vay_nu', 'sort_weight' => 10],
            ['name' => 'S', 'category_group' => 'quan_vay_nu', 'sort_weight' => 20],
            ['name' => 'M', 'category_group' => 'quan_vay_nu', 'sort_weight' => 30],
            ['name' => 'L', 'category_group' => 'quan_vay_nu', 'sort_weight' => 40],

            // Cụm 3: Đầm nữ.
            ['name' => 'XS', 'category_group' => 'dam_nu', 'sort_weight' => 10],
            ['name' => 'S', 'category_group' => 'dam_nu', 'sort_weight' => 20],
            ['name' => 'M', 'category_group' => 'dam_nu', 'sort_weight' => 30],
            ['name' => 'L', 'category_group' => 'dam_nu', 'sort_weight' => 40],
        ];
    }
}