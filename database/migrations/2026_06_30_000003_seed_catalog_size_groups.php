<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Seed bộ size theo 3 cụm vận hành:
     * 1. Áo nam/nữ.
     * 2. Quần nam và quần/váy nữ.
     * 3. Đầm nữ.
     */
    public function up(): void
    {
        if (! Schema::hasTable('sizes')) {
            return;
        }

        DB::table('sizes')->update(['status' => 0, 'updated_at' => now()]);

        foreach ($this->catalogSizes() as $size) {
            DB::table('sizes')->updateOrInsert(
                [
                    'name' => $size['name'],
                    'category_group' => $size['category_group'],
                ],
                [
                    'sort_weight' => $size['sort_weight'],
                    'status' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

    }

    public function down(): void
    {
        if (! Schema::hasTable('sizes')) {
            return;
        }

        DB::table('sizes')
            ->whereIn('category_group', ['ao_nu', 'quan_nam', 'quan_vay_nu', 'dam_nu'])
            ->delete();
    }

    private function catalogSizes(): array
    {
        return [
            ['name' => 'S', 'category_group' => 'ao_nam', 'sort_weight' => 20],
            ['name' => 'M', 'category_group' => 'ao_nam', 'sort_weight' => 30],
            ['name' => 'L', 'category_group' => 'ao_nam', 'sort_weight' => 40],
            ['name' => 'XL', 'category_group' => 'ao_nam', 'sort_weight' => 50],
            ['name' => 'XXL', 'category_group' => 'ao_nam', 'sort_weight' => 60],
            ['name' => 'XXXL', 'category_group' => 'ao_nam', 'sort_weight' => 70],

            ['name' => 'XXS', 'category_group' => 'ao_nu', 'sort_weight' => 5],
            ['name' => 'XS', 'category_group' => 'ao_nu', 'sort_weight' => 10],
            ['name' => 'S', 'category_group' => 'ao_nu', 'sort_weight' => 20],
            ['name' => 'M', 'category_group' => 'ao_nu', 'sort_weight' => 30],
            ['name' => 'L', 'category_group' => 'ao_nu', 'sort_weight' => 40],
            ['name' => 'XL', 'category_group' => 'ao_nu', 'sort_weight' => 50],

            ['name' => 'S', 'category_group' => 'quan_nam', 'sort_weight' => 20],
            ['name' => 'M', 'category_group' => 'quan_nam', 'sort_weight' => 30],
            ['name' => 'L', 'category_group' => 'quan_nam', 'sort_weight' => 40],
            ['name' => 'XL', 'category_group' => 'quan_nam', 'sort_weight' => 50],

            ['name' => 'XS', 'category_group' => 'quan_vay_nu', 'sort_weight' => 10],
            ['name' => 'S', 'category_group' => 'quan_vay_nu', 'sort_weight' => 20],
            ['name' => 'M', 'category_group' => 'quan_vay_nu', 'sort_weight' => 30],
            ['name' => 'L', 'category_group' => 'quan_vay_nu', 'sort_weight' => 40],

            ['name' => 'XS', 'category_group' => 'dam_nu', 'sort_weight' => 10],
            ['name' => 'S', 'category_group' => 'dam_nu', 'sort_weight' => 20],
            ['name' => 'M', 'category_group' => 'dam_nu', 'sort_weight' => 30],
            ['name' => 'L', 'category_group' => 'dam_nu', 'sort_weight' => 40],
        ];
    }
};
