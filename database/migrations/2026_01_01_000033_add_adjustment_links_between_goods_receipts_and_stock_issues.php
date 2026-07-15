<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('goods_receipts', function (Blueprint $table) {
            $table->foreignId('adjustment_stock_issue_id')
                ->nullable()
                ->after('adjustment_reason')
                ->constrained('stock_issues')
                ->nullOnDelete();
        });

        Schema::table('stock_issues', function (Blueprint $table) {
            $table->foreignId('adjustment_goods_receipt_id')
                ->nullable()
                ->after('adjustment_reason')
                ->constrained('goods_receipts')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('goods_receipts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('adjustment_stock_issue_id');
        });

        Schema::table('stock_issues', function (Blueprint $table) {
            $table->dropConstrainedForeignId('adjustment_goods_receipt_id');
        });
    }
};
