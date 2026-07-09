<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tạo bảng kho hàng
        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255)->comment('Tên kho');
            $table->string('address', 500)->nullable()->comment('Địa chỉ kho');
            $table->string('city', 100)->nullable()->comment('Thành phố');
            $table->string('phone', 20)->nullable()->comment('Số điện thoại kho');
            $table->boolean('is_default')->default(false)->comment('Kho mặc định');
            $table->boolean('status')->default(true)->comment('Trạng thái hoạt động');
            $table->timestamps();
        });

        // 2. Seed kho mặc định ngay trong migration
        DB::table('warehouses')->insert([
            'name'       => 'Kho chính',
            'address'    => '123 Nguyễn Thị Minh Khai',
            'city'       => 'TP. Hồ Chí Minh',
            'is_default' => true,
            'status'     => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $defaultWarehouseId = DB::table('warehouses')->where('is_default', true)->value('id');

        // 3. Thêm warehouse_id vào goods_receipts
        Schema::table('goods_receipts', function (Blueprint $table) {
            $table->foreignId('warehouse_id')
                ->nullable()
                ->after('source_type')
                ->constrained('warehouses')
                ->nullOnDelete();
        });

        // 4. Gắn tất cả phiếu nhập cũ vào kho mặc định
        if ($defaultWarehouseId) {
            DB::table('goods_receipts')
                ->whereNull('warehouse_id')
                ->update(['warehouse_id' => $defaultWarehouseId]);
        }
    }

    public function down(): void
    {
        Schema::table('goods_receipts', function (Blueprint $table) {
            if (Schema::hasColumn('goods_receipts', 'warehouse_id')) {
                $table->dropConstrainedForeignId('warehouse_id');
            }
        });

        Schema::dropIfExists('warehouses');
    }
};
