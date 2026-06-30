<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bổ sung các trường quản trị size:
     * - category_group: phân nhóm size theo loại sản phẩm.
     * - sort_weight: thứ tự hiển thị nghiệp vụ.
     * - status: 1 hoạt động, 0 ẩn.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('sizes', 'category_group')) {
            Schema::table('sizes', function (Blueprint $table) {
                $table->string('category_group', 50)->default('quan_ao')->after('name');
            });
        }

        if (! Schema::hasColumn('sizes', 'sort_weight')) {
            Schema::table('sizes', function (Blueprint $table) {
                $table->integer('sort_weight')->default(0)->after('category_group');
            });
        }

        if (! Schema::hasColumn('sizes', 'status')) {
            Schema::table('sizes', function (Blueprint $table) {
                $table->tinyInteger('status')->default(1)->after('sort_weight');
            });
        }

        $this->syncDefaultSortWeights();
    }

    private function syncDefaultSortWeights(): void
    {
        $weights = [
            'XXS' => 5,
            'XS' => 10,
            'S' => 20,
            'M' => 30,
            'L' => 40,
            'XL' => 50,
            'XXL' => 60,
            'XXXL' => 70,
            '36' => 360,
            '37' => 370,
            '38' => 380,
            '39' => 390,
            '40' => 400,
            '41' => 410,
            '42' => 420,
            '43' => 430,
            '44' => 440,
            '45' => 450,
        ];

        DB::table('sizes')
            ->select(['id', 'name'])
            ->orderBy('id')
            ->get()
            ->each(function ($size) use ($weights) {
                $normalizedName = strtoupper(trim((string) $size->name));

                DB::table('sizes')
                    ->where('id', $size->id)
                    ->update([
                        'sort_weight' => $weights[$normalizedName] ?? ((int) $size->id * 10),
                    ]);
            });
    }

    public function down(): void
    {
        foreach (['status', 'sort_weight', 'category_group'] as $column) {
            if (Schema::hasColumn('sizes', $column)) {
                Schema::table('sizes', function (Blueprint $table) use ($column) {
                    $table->dropColumn($column);
                });
            }
        }
    }
};
