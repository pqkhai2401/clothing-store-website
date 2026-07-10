<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Dọn các bản ghi trùng (cùng user_id + voucher_id) trước khi thêm unique index,
        // giữ lại bản ghi có id nhỏ nhất (lần sử dụng đầu tiên) để migration không fail.
        $duplicateIds = DB::table('voucher_history as vh1')
            ->join('voucher_history as vh2', function ($join) {
                $join->on('vh1.user_id', '=', 'vh2.user_id')
                    ->on('vh1.voucher_id', '=', 'vh2.voucher_id')
                    ->on('vh1.id', '>', 'vh2.id');
            })
            ->pluck('vh1.id')
            ->unique()
            ->all();

        if (! empty($duplicateIds)) {
            DB::table('voucher_history')->whereIn('id', $duplicateIds)->delete();
        }

        Schema::table('voucher_history', function (Blueprint $table) {
            // Mỗi user chỉ được dùng một voucher đúng một lần — chốt ở tầng DB để chống race-condition
            // (bổ trợ cho lockForUpdate + recheck trong CheckoutController::store).
            $table->unique(['user_id', 'voucher_id'], 'voucher_history_user_voucher_unique');
        });
    }

    public function down(): void
    {
        Schema::table('voucher_history', function (Blueprint $table) {
            $table->dropUnique('voucher_history_user_voucher_unique');
        });
    }
};
