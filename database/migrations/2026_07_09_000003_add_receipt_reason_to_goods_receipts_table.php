<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('goods_receipts', function (Blueprint $table) {
            if (! Schema::hasColumn('goods_receipts', 'receipt_reason')) {
                $table->string('receipt_reason', 500)->nullable()->after('source_type')
                    ->comment('Lý do nhập kho (dùng cho phiếu điều chỉnh, trả hàng, đầu kỳ)');
            }
        });
    }

    public function down(): void
    {
        Schema::table('goods_receipts', function (Blueprint $table) {
            if (Schema::hasColumn('goods_receipts', 'receipt_reason')) {
                $table->dropColumn('receipt_reason');
            }
        });
    }
};
