<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Mã giao dịch gửi sang PayOS (số nguyên, duy nhất) — dùng để đối soát webhook/return.
            $table->unsignedBigInteger('payos_order_code')->nullable()->unique()->after('order_code');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('payos_order_code');
        });
    }
};
