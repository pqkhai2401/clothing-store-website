<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Bổ sung số tiền hoàn thực tế + thời điểm hoàn cho các đơn ĐÃ HỦY & ĐÃ THU TIỀN.
 *
 * Vì sao cần: trước đây markRefunded() chỉ đổi payment_status -> 'refunded' và ghi log vào
 * bảng `audits` (không có cột riêng để tra nhanh). Nếu hoàn 1 phần (ví dụ trừ phí giao dịch)
 * thì không có nơi lưu số tiền hoàn thực tế khác với total_money.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('orders', 'refund_amount')) {
            return;
        }

        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('refund_amount', 12, 2)->nullable()->after('payment_status');
            $table->timestamp('refunded_at')->nullable()->after('refund_amount');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['refund_amount', 'refunded_at']);
        });
    }
};
