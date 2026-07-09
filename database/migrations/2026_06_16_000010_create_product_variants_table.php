<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('color_id')->constrained('colors')->cascadeOnDelete();
            $table->foreignId('size_id')->constrained('sizes')->cascadeOnDelete();
            $table->integer('stock')->default(0);
            $table->string('status', 20)->default('Active');
            $table->string('image')->nullable();
            $table->string('sku')->unique();
            $table->decimal('cost_price', 18, 2)->default(0);
            $table->decimal('sale_price', 18, 2)->default(0);
            $table->timestamps();

            $table->unique(['product_id', 'color_id', 'size_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
