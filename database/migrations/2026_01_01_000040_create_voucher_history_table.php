<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('voucher_history')) {
            return;
        }

        Schema::create('voucher_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('voucher_id')->constrained('vouchers')->cascadeOnDelete();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->timestamp('used_at')->useCurrent();

            $table->unique(['user_id', 'voucher_id'], 'voucher_history_user_voucher_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('voucher_history');
    }
};
