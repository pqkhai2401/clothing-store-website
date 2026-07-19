<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Đổi FK product_variant_id trên các bảng LỊCH SỬ KHO (goods_receipt_items,
 * stock_issue_items, stock_movements) từ cascadeOnDelete sang restrictOnDelete.
 *
 * Trước: xoá cứng 1 ProductVariant sẽ tự động xoá theo TOÀN BỘ lịch sử
 * nhập/xuất/sổ cái liên quan tới nó — mất dữ liệu kế toán kho vĩnh viễn.
 * Sau: MySQL sẽ từ chối xoá cứng variant nếu còn dòng lịch sử nào tham chiếu
 * tới nó. Tầng ứng dụng (ProductController::productForceDeleteBlocker) được
 * bổ sung kiểm tra tương ứng trong cùng đợt sửa để trả về thông báo lỗi thân
 * thiện thay vì để lộ lỗi ràng buộc khoá ngoại thô từ database.
 */
return new class extends Migration
{
    private array $tables = [
        'goods_receipt_items',
        'stock_issue_items',
        'stock_movements',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->dropForeign(['product_variant_id']);
            });
            Schema::table($table, function (Blueprint $t) {
                $t->foreign('product_variant_id')
                    ->references('id')->on('product_variants')
                    ->restrictOnDelete();
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->dropForeign(['product_variant_id']);
            });
            Schema::table($table, function (Blueprint $t) {
                $t->foreign('product_variant_id')
                    ->references('id')->on('product_variants')
                    ->cascadeOnDelete();
            });
        }
    }
};
