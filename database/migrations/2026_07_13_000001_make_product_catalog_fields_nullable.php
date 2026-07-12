<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE products MODIFY thumbnail VARCHAR(255) NULL');
        DB::statement('ALTER TABLE products MODIFY brand_id BIGINT UNSIGNED NULL');
        DB::statement('ALTER TABLE products MODIFY description LONGTEXT NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE products MODIFY thumbnail VARCHAR(255) NOT NULL');
        DB::statement('ALTER TABLE products MODIFY brand_id BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE products MODIFY description LONGTEXT NOT NULL');
    }
};
