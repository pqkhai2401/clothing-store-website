<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            if (! Schema::hasColumn('stock_movements', 'product_batch_id')) {
                $table->foreignId('product_batch_id')->nullable()->after('product_variant_id')
                    ->constrained('product_batches')->nullOnDelete()
                    ->comment('Lô hàng bị tác động (null với bút toán tổng hợp)');
            }
            if (! Schema::hasColumn('stock_movements', 'unit_cost')) {
                $table->decimal('unit_cost', 15, 2)->default(0)->after('quantity')
                    ->comment('Giá vốn của lô tại thời điểm biến động — phục vụ tính COGS/lợi nhuận');
            }
        });
    }

    public function down(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            if (Schema::hasColumn('stock_movements', 'product_batch_id')) {
                $table->dropConstrainedForeignId('product_batch_id');
            }
            if (Schema::hasColumn('stock_movements', 'unit_cost')) {
                $table->dropColumn('unit_cost');
            }
        });
    }
};
