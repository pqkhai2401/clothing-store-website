<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Chuẩn hóa nhóm size theo danh mục shop đang bán hiện tại.
     * Không xóa size cũ để tránh mất dữ liệu biến thể; chỉ ẩn các size giày nếu còn tồn tại.
     */
    public function up(): void
    {
        if (! Schema::hasTable('sizes') || ! Schema::hasColumn('sizes', 'category_group')) {
            return;
        }

        DB::table('sizes')
            ->where('category_group', 'quan_ao')
            ->update(['category_group' => 'ao_nam']);

        DB::table('sizes')
            ->where('category_group', 'giay_dep')
            ->update(['status' => 0]);
    }

    public function down(): void
    {
        if (! Schema::hasTable('sizes') || ! Schema::hasColumn('sizes', 'category_group')) {
            return;
        }

        DB::table('sizes')
            ->where('category_group', 'ao_nam')
            ->update(['category_group' => 'quan_ao']);
    }
};
