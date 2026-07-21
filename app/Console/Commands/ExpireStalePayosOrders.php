<?php

namespace App\Console\Commands;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Services\OrderCancellationService;
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

    public function __construct(
        private readonly OrderCancellationService $cancellationService
    ) {
        parent::__construct();
    }

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

        // LƯỚI AN TOÀN cho CancelUnpaidOrderJob (phòng khi queue worker chết/job fail hẳn).
        // Dưới mô hình "Hold & Release", đơn pending ĐANG GIỮ KHO nên phải hủy qua service để
        // nhả kho về đúng lô + nhả voucher — không dùng bulk update như trước.
        // Service là atomic + idempotent nên chạy song song với job vẫn KHÔNG hoàn kho 2 lần.
        $staleOrders = Order::query()
            ->where('status', OrderStatus::PENDING->value)
            ->where('payment_status', PaymentStatus::UNPAID->value)
            ->whereIn('payment_method_id', $onlineIds)
            ->where('created_at', '<', now()->subMinutes(self::EXPIRE_MINUTES))
            ->get();

        $count = 0;
        foreach ($staleOrders as $order) {
            if ($this->cancellationService->cancelPendingUnpaid($order, 'Quá hạn thanh toán online (cron dọn)')) {
                $count++;
            }
        }

        $this->info("Đã hủy {$count} đơn thanh toán online quá hạn.");

        return self::SUCCESS;
    }
}
