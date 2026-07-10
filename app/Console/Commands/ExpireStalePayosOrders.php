<?php

namespace App\Console\Commands;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Services\PayosService;
use Illuminate\Console\Command;

class ExpireStalePayosOrders extends Command
{
    /**
     * Link/QR thanh toán online hết hạn sau 30 phút (PayOS & MoMo cùng ngưỡng).
     */
    private const EXPIRE_MINUTES = PayosService::EXPIRE_MINUTES;

    protected $signature = 'orders:expire-stale-payos';

    protected $description = 'Tự hủy các đơn thanh toán online (PayOS/MoMo) pending/unpaid đã quá hạn (30 phút).';

    public function handle(): int
    {
        $onlineIds = PaymentMethod::all()
            ->filter(fn (PaymentMethod $method) => $method->isOnlineGateway())
            ->pluck('id')
            ->all();

        if (empty($onlineIds)) {
            $this->info('Không có phương thức thanh toán online nào — bỏ qua.');

            return self::SUCCESS;
        }

        // Đơn pending/unpaid chưa từng bị trừ kho (kho chỉ trừ khi admin xử lý),
        // nên chỉ đổi status='cancelled', KHÔNG hoàn kho — xem OrderController::cancelOrder.
        $count = Order::query()
            ->where('status', OrderStatus::PENDING->value)
            ->where('payment_status', PaymentStatus::UNPAID->value)
            ->whereIn('payment_method_id', $onlineIds)
            ->where('created_at', '<', now()->subMinutes(self::EXPIRE_MINUTES))
            ->update(['status' => OrderStatus::CANCELLED->value]);

        $this->info("Đã hủy {$count} đơn thanh toán online quá hạn.");

        return self::SUCCESS;
    }
}
