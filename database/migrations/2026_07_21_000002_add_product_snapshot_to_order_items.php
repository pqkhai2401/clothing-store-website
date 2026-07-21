<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Snapshot thông tin sản phẩm TẠI THỜI ĐIỂM ĐẶT HÀNG vào order_items. Trước đây hóa đơn
        // đọc tên/SKU/màu/size động qua product_variant_id → admin đổi tên sản phẩm sau khi khách
        // đã mua thì hóa đơn CŨ cũng đổi theo (sai tính toàn vẹn chứng từ kế toán). Giá (unit_price)
        // vốn đã được snapshot; nay bổ sung nốt các trường mô tả.
        if (! Schema::hasColumn('order_items', 'product_name')) {
            Schema::table('order_items', function (Blueprint $table) {
                $table->string('product_name')->nullable()->after('product_variant_id');
                $table->string('variant_sku')->nullable()->after('product_name');
                $table->string('color_name')->nullable()->after('variant_sku');
                $table->string('size_name')->nullable()->after('color_name');
            });
        }

        // Backfill đơn cũ bằng dữ liệu catalog HIỆN TẠI làm mốc (đơn cũ không có lịch sử tên nên
        // đây là snapshot trung thực nhất có thể) — từ đây về sau các trường này đóng băng, không
        // đổi nữa. JOIN thô (không qua Eloquent) nên bắt cả sản phẩm/biến thể đã xóa mềm.
        DB::statement("
            UPDATE order_items oi
            JOIN product_variants pv ON pv.id = oi.product_variant_id
            LEFT JOIN products p ON p.id = pv.product_id
            LEFT JOIN colors c ON c.id = pv.color_id
            LEFT JOIN sizes s ON s.id = pv.size_id
            SET oi.product_name = p.name,
                oi.variant_sku  = pv.sku,
                oi.color_name   = c.name,
                oi.size_name    = s.name
            WHERE oi.product_name IS NULL
        ");
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['product_name', 'variant_sku', 'color_name', 'size_name']);
        });
    }
};
