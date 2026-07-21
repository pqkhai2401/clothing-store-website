<?php

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
        if (Schema::hasColumn('categories', 'outfit_type')) {
            return;
        }

        Schema::table('categories', function (Blueprint $table) {
            // Nhóm trang phục để xác định luật phối đồ (top phối với bottom/outerwear/
            // accessory, dress chỉ phối outerwear/accessory...). NULL = danh mục cha
            // (Nam/Nữ/Phụ kiện), không gắn với sản phẩm trực tiếp.
            $table->enum('outfit_type', ['top', 'bottom', 'skirt', 'dress', 'outerwear', 'accessory'])
                ->nullable()
                ->after('parent_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('outfit_type');
        });
    }
};
