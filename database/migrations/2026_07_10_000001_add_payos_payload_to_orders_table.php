<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Cache dữ liệu link/QR PayOS (qrCode, checkoutUrl, accountNumber, accountName, bin) lúc tạo,
            // để mở lại trang thanh toán không cần gọi createPaymentLink lần nữa (giữ nguyên mã giao dịch
            // và QR cũ trong khi link còn hiệu lực) — xem PayosController::show().
            $table->json('payos_payload')->nullable()->after('payos_order_code');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('payos_payload');
        });
    }
};
