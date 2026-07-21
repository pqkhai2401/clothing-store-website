<?php

use App\Enums\OrderSource;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('orders')) {
            return;
        }

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('address_id')->constrained('addresses');
            $table->foreignId('payment_method_id')->constrained('payment_methods');
            $table->foreignId('voucher_id')->nullable()->constrained('vouchers')->nullOnDelete();
            // Phân biệt đơn khách tự đặt qua website (mặc định) với đơn admin tạo thủ công
            // (vd. đặt hộ khách qua điện thoại/tại quầy).
            $table->string('source')->default(OrderSource::ONLINE->value);
            // Chỉ có giá trị khi source = admin — ghi lại nhân sự đã tạo đơn để tra soát khi cần.
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('order_code')->unique();
            $table->unsignedBigInteger('payos_order_code')->nullable()->unique();
            $table->json('payos_payload')->nullable();
            $table->string('momo_order_id')->nullable()->unique();
            $table->json('momo_payload')->nullable();
            $table->string('phone');
            $table->text('note')->nullable();
            $table->decimal('total_money', 15, 2);
            $table->decimal('shipping_fee', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->enum('status', OrderStatus::values())->default(OrderStatus::PENDING->value);
            $table->enum('payment_status', PaymentStatus::values())->default(PaymentStatus::UNPAID->value);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Mọi filter/thống kê admin (buildFilteredQuery, buildStatsData) đều WHERE theo
            // status/payment_status/created_at — cần index tổng hợp để tránh full table scan.
            $table->index(['status', 'payment_status', 'created_at'], 'orders_status_payment_created_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
