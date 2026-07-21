<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_issue_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_issue_id')->constrained('stock_issues')->cascadeOnDelete();
            // restrictOnDelete: giống product_variant_id bên dưới — dòng chứng từ xuất kho (lịch
            // sử kế toán) không được phép mất theo khi xóa cứng sản phẩm.
            $table->foreignId('product_id')->nullable()->constrained('products')->restrictOnDelete();
            // restrictOnDelete: xoá cứng 1 ProductVariant không được phép kéo theo mất lịch sử
            // xuất kho — tầng ứng dụng (ProductController::productForceDeleteBlocker) chặn và
            // báo lỗi thân thiện trước khi chạm tới ràng buộc DB này.
            $table->foreignId('product_variant_id')->constrained('product_variants')->restrictOnDelete();
            $table->unsignedInteger('quantity');
            $table->decimal('cost_price', 15, 2)->default(0);
            $table->decimal('sale_price', 15, 2)->default(0);
            $table->decimal('total_cost', 15, 2)->default(0);
            $table->decimal('total_sale', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_issue_items');
    }
};
