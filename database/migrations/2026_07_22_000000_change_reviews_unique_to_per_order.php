<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
        // Idempotent: nếu unique mới đã tồn tại (migration đã chạy trước đó) thì bỏ qua,
        // tránh lỗi "Duplicate key name" khi migrate bị chạy lại do lệch trạng thái.
        if ($this->indexExists('reviews', 'reviews_user_id_product_id_order_id_unique')) {
            return;
        }

        Schema::table('reviews', function (Blueprint $table) {
            // Thêm unique mới TRƯỚC. Vì cũng bắt đầu bằng user_id nên nó có thể
            // thay index cũ phục vụ khóa ngoại user_id -> mới drop được index cũ.
            $table->unique(['user_id', 'product_id', 'order_id']);
        });

        // Chỉ gỡ unique cũ nếu còn tồn tại.
        if ($this->indexExists('reviews', 'reviews_user_id_product_id_unique')) {
            Schema::table('reviews', function (Blueprint $table) {
                $table->dropUnique(['user_id', 'product_id']);
            });
        }
    }

    private function indexExists(string $table, string $index): bool
    {
        return DB::table('information_schema.statistics')
            ->where('table_schema', DB::raw('DATABASE()'))
            ->where('table_name', $table)
            ->where('index_name', $index)
            ->exists();
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
