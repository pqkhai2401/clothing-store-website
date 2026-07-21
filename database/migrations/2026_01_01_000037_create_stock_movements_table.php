<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('stock_movements')) {
            return;
        }

        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            // restrictOnDelete: xoá cứng 1 ProductVariant không được phép kéo theo mất sổ cái
            // biến động kho — tầng ứng dụng (ProductController::productForceDeleteBlocker) chặn
            // và báo lỗi thân thiện trước khi chạm tới ràng buộc DB này.
            $table->foreignId('product_variant_id')->constrained('product_variants')->restrictOnDelete();
            $table->foreignId('product_batch_id')->nullable()->constrained('product_batches')->nullOnDelete();
            $table->string('reference_type', 50);
            $table->unsignedBigInteger('reference_id');
            $table->string('movement_type', 30);
            $table->integer('quantity');
            $table->decimal('unit_cost', 15, 2)->default(0);
            $table->integer('before_quantity');
            $table->integer('after_quantity');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['reference_type', 'reference_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
