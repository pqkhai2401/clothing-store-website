<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('goods_receipts', function (Blueprint $table) {
            $table->foreignId('adjusted_by')->nullable()->after('deleted_by')->constrained('users')->nullOnDelete();
            $table->timestamp('adjusted_at')->nullable()->after('adjusted_by');
            $table->text('adjustment_reason')->nullable()->after('adjusted_at');
            $table->foreignId('adjustment_stock_issue_id')->nullable()->after('adjustment_reason')->constrained('stock_issues')->nullOnDelete();
        });

        Schema::table('stock_issues', function (Blueprint $table) {
            $table->foreignId('adjusted_by')->nullable()->after('deleted_by')->constrained('users')->nullOnDelete();
            $table->timestamp('adjusted_at')->nullable()->after('adjusted_by');
            $table->text('adjustment_reason')->nullable()->after('adjusted_at');
            $table->foreignId('adjustment_goods_receipt_id')->nullable()->after('adjustment_reason')->constrained('goods_receipts')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('stock_issues', function (Blueprint $table) {
            $table->dropConstrainedForeignId('adjustment_goods_receipt_id');
            $table->dropColumn(['adjusted_by', 'adjusted_at', 'adjustment_reason']);
        });

        Schema::table('goods_receipts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('adjustment_stock_issue_id');
            $table->dropColumn(['adjusted_by', 'adjusted_at', 'adjustment_reason']);
        });
    }
};
