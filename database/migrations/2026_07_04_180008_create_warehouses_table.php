<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
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

        // Kho mặc định của hệ thống (được nhiều nghiệp vụ kho tham chiếu qua Warehouse::getDefault()).
        DB::table('warehouses')->insert([
            'name'       => 'Kho chính',
            'address'    => '123 Nguyễn Thị Minh Khai',
            'city'       => 'TP. Hồ Chí Minh',
            'is_default' => true,
            'status'     => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouses');
    }
};
