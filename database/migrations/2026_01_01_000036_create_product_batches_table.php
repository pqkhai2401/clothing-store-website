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
            $table->string('batch_code', 50)->unique();
            $table->foreignId('product_variant_id')->constrained('product_variants')->cascadeOnDelete();
            $table->foreignId('goods_receipt_item_id')->nullable()->constrained('goods_receipt_items')->nullOnDelete();
            $table->unsignedInteger('quantity_import');
            $table->unsignedInteger('quantity_remaining');
            $table->decimal('cost_price', 15, 2)->default(0);
            $table->date('manufacture_date')->nullable();
            $table->date('expired_date')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->string('status', 20)->default('active');
            $table->timestamps();

            $table->index(['product_variant_id', 'status']);
            $table->index(['product_variant_id', 'received_at', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_batches');
    }
};
