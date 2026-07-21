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
        Schema::table('categories', function (Blueprint $table) {
            // Mức độ trang trọng/phong cách để loại trừ phối đồ xung đột (VD: áo sơ mi
            // "smart_casual" không nên gợi ý quần jogger/short "sporty"). Bổ sung cho
            // outfit_type (đúng loại nhưng có thể vẫn sai phong cách).
            $table->enum('style_group', ['formal', 'smart_casual', 'casual', 'sporty', 'neutral'])
                ->nullable()
                ->after('outfit_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('style_group');
        });
    }
};
