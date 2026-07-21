<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_issues', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('issue_type', 30)->default('sale');
            $table->foreignId('warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->string('reason');
            $table->text('note')->nullable();
            $table->string('status', 20)->default('draft');
            $table->integer('total_quantity')->default(0);
            $table->decimal('total_cost_amount', 15, 2)->default(0);
            $table->decimal('total_sale_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('confirmed_at')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('issued_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('adjusted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('adjusted_at')->nullable();
            $table->text('adjustment_reason')->nullable();
            $table->foreignId('adjustment_goods_receipt_id')->nullable()->constrained('goods_receipts')->nullOnDelete();
        });

        // goods_receipts.adjustment_stock_issue_id is declared as a plain column in the
        // goods_receipts migration (it runs before this table exists); link it here now
        // that both tables are present.
        Schema::table('goods_receipts', function (Blueprint $table) {
            $table->foreign('adjustment_stock_issue_id')
                ->references('id')->on('stock_issues')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('goods_receipts', function (Blueprint $table) {
            $table->dropForeign(['adjustment_stock_issue_id']);
        });

        Schema::dropIfExists('stock_issues');
    }
};
