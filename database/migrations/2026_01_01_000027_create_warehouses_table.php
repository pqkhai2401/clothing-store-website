<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('warehouses')) {
            return;
        }

        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('address', 500)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('phone', 20)->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        DB::table('warehouses')->insert([
            'name' => 'Kho chính',
            'address' => '123 Nguyễn Thị Minh Khai',
            'city' => 'TP. Hồ Chí Minh',
            'is_default' => true,
            'status' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('warehouses');
    }
};
