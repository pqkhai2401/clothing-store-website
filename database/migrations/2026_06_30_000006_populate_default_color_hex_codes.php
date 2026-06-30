<?php

use App\Models\Color;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('colors') || ! Schema::hasColumn('colors', 'hex_code')) {
            return;
        }

        foreach (Color::defaultHexMap() as $name => $hexCode) {
            DB::table('colors')
                ->where('name', $name)
                ->whereNull('hex_code')
                ->update([
                    'hex_code' => $hexCode,
                    'updated_at' => now(),
                ]);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('colors') || ! Schema::hasColumn('colors', 'hex_code')) {
            return;
        }

        foreach (Color::defaultHexMap() as $name => $hexCode) {
            DB::table('colors')
                ->where('name', $name)
                ->where('hex_code', $hexCode)
                ->update([
                    'hex_code' => null,
                    'updated_at' => now(),
                ]);
        }
    }
};
