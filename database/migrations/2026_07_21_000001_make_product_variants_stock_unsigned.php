<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tồn kho (stock) là số lượng hàng còn lại — về nghiệp vụ không bao giờ được âm.
        // Trước đây khai báo là int có dấu (int(11)), chỉ chặn âm ở tầng ứng dụng
        // (InventoryBatchService luôn tính lại stock = SUM tồn các lô >= 0). Đổi sang
        // unsigned để có thêm lưới an toàn ở tầng DB: mọi thao tác trực tiếp DB
        // (seeder/phpMyAdmin/migration/ code mới quên đi qua service) ghi số âm sẽ bị
        // MySQL từ chối/kẹp về 0, không âm thầm làm hỏng dữ liệu tồn kho.
        Schema::table('product_variants', function (Blueprint $table) {
            $table->unsignedInteger('stock')->default(0)->change();
        });
    }

    public function down(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->integer('stock')->default(0)->change();
        });
    }
};
