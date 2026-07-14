<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // orderId gửi sang MoMo (duy nhất) — sinh mới mỗi lần hiển thị QR để tạo lại mã.
            $table->string('momo_order_id')->nullable()->unique()->after('payos_payload');
            // Lưu response gần nhất của MoMo để đối soát.
            $table->json('momo_payload')->nullable()->after('momo_order_id');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['momo_order_id', 'momo_payload']);
        });
    }
};
