<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('reviews')) {
            return;
        }

        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->unsignedTinyInteger('rating');
            $table->text('comment');
            $table->enum('status', ['pending', 'approved', 'rejected', 'flagged'])->default('pending');
            $table->unsignedTinyInteger('ai_score')->nullable();
            $table->text('ai_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['user_id', 'product_id']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
