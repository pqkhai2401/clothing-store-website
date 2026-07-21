<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('site_name')->default('HK Store');
            $table->string('logo_path')->nullable();
            $table->string('address')->nullable();
            $table->string('hotline')->nullable();
            $table->string('email')->nullable();
            $table->string('facebook_url')->nullable();
            $table->boolean('facebook_enabled')->default(true);
            $table->string('instagram_url')->nullable();
            $table->boolean('instagram_enabled')->default(true);
            $table->string('zalo_url')->nullable();
            $table->boolean('zalo_enabled')->default(true);
            $table->timestamps();
        });

        DB::table('settings')->insert([
            'site_name' => 'HK Store',
            'logo_path' => null,
            'address' => '65 Huỳnh Thúc Kháng, Sài Gòn, Hồ Chí Minh 50000, Việt Nam',
            'hotline' => '02838212360',
            'email' => 'support@hkstore.vn',
            'facebook_url' => 'https://www.facebook.com/kha.rea.19',
            'facebook_enabled' => true,
            'instagram_url' => 'https://www.instagram.com/kha_rea.19/?hl=en',
            'instagram_enabled' => true,
            'zalo_url' => 'https://zalo.me/0357989856',
            'zalo_enabled' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
