<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Mốc ghi nhận doanh thu theo chuẩn kế toán (thời điểm đơn chuyển "completed"),
            // tách bạch khỏi created_at (ngày đặt) và updated_at (đổi theo mọi lần sửa đơn).
            $table->timestamp('completed_at')->nullable()->after('payment_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('completed_at');
        });
    }
};
