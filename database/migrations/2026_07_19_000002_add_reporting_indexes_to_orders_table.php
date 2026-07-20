<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Mọi filter/thống kê admin (buildFilteredQuery, buildStatsData) đều WHERE theo status/
 * payment_status/created_at — bảng orders trước đây không có index nào trên các cột này
 * (chỉ có unique key), nên khi dữ liệu lớn dần các truy vấn này là full table scan.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->index(['status', 'payment_status', 'created_at'], 'orders_status_payment_created_idx');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_status_payment_created_idx');
        });
    }
};
