<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Voucher;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VoucherHistorySeeder extends Seeder
{
    /**
     * Sinh lịch sử sử dụng voucher (bảng voucher_history) phục vụ thống kê/kiểm thử.
     * Mỗi bản ghi gắn một đơn hàng có thật với một voucher và đúng chủ đơn (user_id).
     */
    public function run(): void
    {
        $vouchers = Voucher::pluck('id');
        // Chỉ gắn cho các đơn không bị hủy để dữ liệu hợp lý.
        $orders = Order::where('status', '!=', 'cancelled')
            ->inRandomOrder()
            ->limit(30)
            ->get(['id', 'user_id']);

        if ($vouchers->isEmpty() || $orders->isEmpty()) {
            return;
        }

        $rows = [];

        foreach ($orders as $order) {
            // Khoảng 60% đơn được coi là có dùng voucher.
            if (random_int(1, 100) > 60) {
                continue;
            }

            $rows[] = [
                'user_id'    => $order->user_id,
                'voucher_id' => $vouchers->random(),
                'order_id'   => $order->id,
                'used_at'    => now()->subDays(random_int(0, 30)),
            ];
        }

        if (! empty($rows)) {
            DB::table('voucher_history')->insert($rows);
        }
    }
}
