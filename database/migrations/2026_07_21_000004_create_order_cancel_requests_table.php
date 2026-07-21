<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Yêu cầu hủy đơn do KHÁCH gửi.
 *
 * Khách chỉ tự hủy được đơn chưa thanh toán và còn "Chờ xác nhận". Với đơn đã được xác nhận
 * (hoặc đang giao) — nhất là đơn đã trả tiền — việc hủy kéo theo hoàn kho và HOÀN TIỀN, nên
 * không cho tự hủy mà phải qua admin duyệt. Bảng này lưu yêu cầu + kết quả xử lý để hai bên
 * cùng thấy và có vết đối soát.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('order_cancel_requests')) {
            return;
        }

        Schema::create('order_cancel_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('reason');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('admin_note')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            // Truy vấn chính: đếm/liệt kê yêu cầu đang chờ duyệt, và tra yêu cầu của 1 đơn.
            $table->index(['status', 'created_at']);
            $table->index(['order_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_cancel_requests');
    }
};
