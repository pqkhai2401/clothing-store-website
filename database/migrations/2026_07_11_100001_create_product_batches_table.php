<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_batches', function (Blueprint $table) {
            $table->id();
            $table->string('batch_code', 50)->unique()->comment('Mã lô hàng');
            $table->foreignId('product_variant_id')->constrained('product_variants')->cascadeOnDelete();
            $table->foreignId('goods_receipt_item_id')->nullable()
                ->constrained('goods_receipt_items')->nullOnDelete()
                ->comment('Dòng phiếu nhập khai sinh ra lô (null nếu tồn đầu kỳ / điều chỉnh)');
            $table->unsignedInteger('quantity_import')->comment('SL nhập ban đầu (bất biến)');
            $table->unsignedInteger('quantity_remaining')->comment('SL còn lại — giảm dần khi xuất');
            $table->decimal('cost_price', 15, 2)->default(0)->comment('Giá vốn của lô (bất biến)');
            $table->date('manufacture_date')->nullable();
            $table->date('expired_date')->nullable();
            $table->timestamp('received_at')->nullable()->comment('Mốc thời gian nhập — dùng sắp xếp FIFO');
            $table->string('status', 20)->default('active')->comment('active | depleted | locked | expired');
            $table->timestamps();

            // FIFO: lọc lô còn bán được + sắp theo thời gian nhập
            $table->index(['product_variant_id', 'status']);
            $table->index(['product_variant_id', 'received_at', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_batches');
    }
};
