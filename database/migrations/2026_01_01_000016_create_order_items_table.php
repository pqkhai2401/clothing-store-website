<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('product_variant_id')->constrained('product_variants');
            // Snapshot thông tin sản phẩm TẠI THỜI ĐIỂM ĐẶT HÀNG. Nếu đọc động qua product_variant_id
            // thì admin đổi tên/SKU sau khi khách đã mua sẽ làm hóa đơn CŨ đổi theo (sai toàn vẹn
            // chứng từ). Giá (unit_price) vốn đã snapshot; các trường mô tả này cũng đóng băng như vậy.
            $table->string('product_name')->nullable();
            $table->string('variant_sku')->nullable();
            $table->string('color_name')->nullable();
            $table->string('size_name')->nullable();
            $table->decimal('unit_price', 15, 2);
            $table->unsignedInteger('quantity');
            $table->timestamps();

            $table->unique(['order_id', 'product_variant_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
