<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Merge duplicate sizes: group by name, keep lowest id, redirect FKs
        $duplicates = DB::table('sizes')
            ->whereNull('deleted_at')
            ->select('name', DB::raw('MIN(id) as keep_id'), DB::raw('COUNT(*) as cnt'))
            ->groupBy('name')
            ->having('cnt', '>', 1)
            ->get();

        foreach ($duplicates as $dup) {
            $removeIds = DB::table('sizes')
                ->whereNull('deleted_at')
                ->where('name', $dup->name)
                ->where('id', '!=', $dup->keep_id)
                ->pluck('id');

            // Re-point product_variants to the kept size
            DB::table('product_variants')
                ->whereIn('size_id', $removeIds)
                ->update(['size_id' => $dup->keep_id]);

            // Soft-delete the duplicates
            DB::table('sizes')
                ->whereIn('id', $removeIds)
                ->update(['deleted_at' => now()]);
        }

        // 2. Drop category_group column
        Schema::table('sizes', function (Blueprint $table) {
            $table->dropColumn('category_group');
        });
    }

    public function down(): void
    {
        Schema::table('sizes', function (Blueprint $table) {
            $table->string('category_group', 50)->default('quan_ao')->after('name');
        });
    }
};
