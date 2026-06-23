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
        if (Schema::hasColumn('users', 'display_name')) {
            DB::table('users')
                ->select('id', 'username', 'display_name')
                ->orderBy('id')
                ->get()
                ->each(function ($user) {
                    $fullName = trim((string) $user->display_name);

                    if ($fullName === '' || $user->username === 'admin') {
                        return;
                    }

                    $base = mb_substr($fullName, 0, 240);
                    $username = $base;
                    $counter = 2;

                    while (
                        DB::table('users')
                            ->where('username', $username)
                            ->where('id', '<>', $user->id)
                            ->exists()
                    ) {
                        $suffix = ' '.$counter++;
                        $username = mb_substr($base, 0, 255 - mb_strlen($suffix)).$suffix;
                    }

                    DB::table('users')
                        ->where('id', $user->id)
                        ->update(['username' => $username]);
                });
        }

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'display_name')) {
                $table->dropColumn('display_name');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'display_name')) {
                $table->string('display_name')->nullable()->after('username');
            }
        });

        if (Schema::hasColumn('users', 'display_name')) {
            DB::table('users')->update([
                'display_name' => DB::raw('username'),
            ]);
        }
    }
};
