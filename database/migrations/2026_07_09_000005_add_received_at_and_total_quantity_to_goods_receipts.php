<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('goods_receipts', function (Blueprint $table) {
            if (!Schema::hasColumn('goods_receipts', 'received_at')) {
                $table->timestamp('received_at')->nullable()->after('completed_at');
            }
            if (!Schema::hasColumn('goods_receipts', 'total_quantity')) {
                $table->integer('total_quantity')->default(0)->after('total_amount');
            }
        });
    }

    public function down(): void
    {
        Schema::table('goods_receipts', function (Blueprint $table) {
            $columns = [];
            if (Schema::hasColumn('goods_receipts', 'received_at')) {
                $columns[] = 'received_at';
            }
            if (Schema::hasColumn('goods_receipts', 'total_quantity')) {
                $columns[] = 'total_quantity';
            }
            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
