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
        if (! $this->hasUsernameUniqueIndex()) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique('users_username_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Intentionally not restoring uniqueness because full names can be duplicated.
    }

    private function hasUsernameUniqueIndex(): bool
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            return DB::table('information_schema.statistics')
                ->whereRaw('table_schema = database()')
                ->where('table_name', 'users')
                ->where('index_name', 'users_username_unique')
                ->exists();
        }

        if ($driver === 'sqlite') {
            return collect(DB::select("PRAGMA index_list('users')"))
                ->contains(fn ($index) => ($index->name ?? null) === 'users_username_unique');
        }

        return true;
    }
};
