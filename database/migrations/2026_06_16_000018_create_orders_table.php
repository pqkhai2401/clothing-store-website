<?php

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('address_id')->constrained('addresses');
            $table->foreignId('payment_method_id')->constrained('payment_methods');
            $table->string('order_code')->unique();
            $table->string('phone');
            $table->text('note')->nullable();
            $table->decimal('total_money', 15, 2);
            $table->decimal('shipping_fee', 15, 2)->default(0);
            $table->enum('status', OrderStatus::values())->default(OrderStatus::PENDING->value);
            $table->enum('payment_status', PaymentStatus::values())->default(PaymentStatus::UNPAID->value);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
