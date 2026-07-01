<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ẩn các size không thuộc bảng size hiện tại của shop.
     */
    public function up(): void
    {
        if (! Schema::hasTable('sizes')) {
            return;
        }

        DB::table('sizes')->update(['status' => 0, 'updated_at' => now()]);

        foreach ($this->catalogSizes() as $size) {
            DB::table('sizes')
                ->where('name', $size['name'])
                ->where('category_group', $size['category_group'])
                ->update([
                    'sort_weight' => $size['sort_weight'],
                    'status' => 1,
                    'updated_at' => now(),
                ]);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('sizes')) {
            return;
        }

        DB::table('sizes')->update(['status' => 1, 'updated_at' => now()]);
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
