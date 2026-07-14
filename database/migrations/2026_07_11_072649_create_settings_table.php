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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('site_name')->default('HK Store');
            $table->string('logo_path')->nullable();
            $table->string('address')->nullable();
            $table->string('hotline')->nullable();
            $table->string('email')->nullable();
            $table->timestamps();
        });

        DB::table('settings')->insert([
            'site_name' => 'HK Store',
            'logo_path' => null,
            'address' => '65 Huỳnh Thúc Kháng, Sài Gòn, Hồ Chí Minh 50000, Việt Nam',
            'hotline' => '02838212360',
            'email' => 'support@hkstore.vn',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
