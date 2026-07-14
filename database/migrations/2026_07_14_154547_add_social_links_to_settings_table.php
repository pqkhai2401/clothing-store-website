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
        Schema::table('settings', function (Blueprint $table) {
            $table->string('facebook_url')->nullable()->after('email');
            $table->boolean('facebook_enabled')->default(true)->after('facebook_url');
            $table->string('instagram_url')->nullable()->after('facebook_enabled');
            $table->boolean('instagram_enabled')->default(true)->after('instagram_url');
            $table->string('zalo_url')->nullable()->after('instagram_enabled');
            $table->boolean('zalo_enabled')->default(true)->after('zalo_url');
        });

        DB::table('settings')->update([
            'facebook_url' => 'https://www.facebook.com/kha.rea.19',
            'instagram_url' => 'https://www.instagram.com/kha_rea.19/?hl=en',
            'zalo_url' => 'https://zalo.me/0357989856',
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn([
                'facebook_url',
                'facebook_enabled',
                'instagram_url',
                'instagram_enabled',
                'zalo_url',
                'zalo_enabled',
            ]);
        });
    }
};
