<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Đổi ràng buộc chống trùng đánh giá:
 *   TRƯỚC: mỗi user chỉ được đánh giá 1 lần / sản phẩm  (user_id, product_id).
 *   SAU  : mỗi user được đánh giá 1 lần / sản phẩm / ĐƠN HÀNG  (user_id, product_id, order_id).
 *
 * Nhờ vậy, khi khách MUA LẠI sản phẩm (đơn hàng completed mới) thì được đánh giá tiếp.
 * Lưu ý: order_id cho phép NULL (đánh giá cũ không gắn đơn) — MySQL coi các NULL là
 * khác nhau nên không chặn các bản ghi cũ.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            // Thêm unique mới TRƯỚC. Vì cũng bắt đầu bằng user_id nên nó có thể
            // thay index cũ phục vụ khóa ngoại user_id -> mới drop được index cũ.
            $table->unique(['user_id', 'product_id', 'order_id']);
            // Gỡ unique cũ (user_id, product_id).
            $table->dropUnique(['user_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            // Thêm lại unique cũ trước để phục vụ khóa ngoại user_id, rồi drop cái mới.
            $table->unique(['user_id', 'product_id']);
            $table->dropUnique(['user_id', 'product_id', 'order_id']);
        });
    }
};
