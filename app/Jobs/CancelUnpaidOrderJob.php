<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\OrderCancellationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * =============================================================================
 *  JOB CHẠY NGẦM: TỰ HỦY ĐƠN THANH TOÁN ONLINE QUÁ HẠN (Hold & Release)
 * =============================================================================
 *
 * Được hẹn giờ ngay lúc khách checkout bằng đơn PayOS/MoMo:
 *   CancelUnpaidOrderJob::dispatch($order)->delay(now()->addMinutes(PayosService::EXPIRE_MINUTES))
 *
 * Mốc trễ khớp ĐÚNG hạn sống của mã QR (30 phút) — nếu hủy sớm hơn, khách thanh toán
 * ở phút cuối sẽ trả tiền cho một đơn đã bị hủy.
 *
 * Luồng xử lý khi tới hạn:
 *   1. Gọi OrderCancellationService::cancelPendingUnpaid().
 *   2. Service khoá đơn và KIỂM TRA LẠI trạng thái:
 *      - Đơn đã thanh toán / đã xử lý / đã hủy  -> bỏ qua, không làm gì (trả false).
 *      - Vẫn pending + unpaid                   -> hủy đơn, nhả kho về đúng lô, nhả voucher.
 *
 * CHỈ hẹn job cho đơn cổng online. Đơn COD vốn luôn 'unpaid' cho tới khi giao xong,
 * nếu hẹn job sẽ bị tự hủy oan.
 */
class CancelUnpaidOrderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Thử lại tối đa 3 lần phòng lỗi DB/deadlock tạm thời.
     */
    public int $tries = 3;

    /**
     * Thời gian tối đa (giây) cho phép Job chạy trước khi bị coi là timeout.
     */
    public int $timeout = 60;

    /**
     * Nhờ trait SerializesModels, hàng đợi chỉ lưu id của đơn rồi truy vấn lại bản ghi
     * MỚI NHẤT khi Job chạy -> luôn kiểm tra trạng thái cập nhật nhất.
     */
    public function __construct(
        public Order $order
    ) {
    }

    public function handle(OrderCancellationService $cancellationService): void
    {
        try {
            // Đơn có thể đã bị người dùng ĐỔI SANG phương thức offline (COD/chuyển khoản) sau khi
            // job này được hẹn giờ lúc tạo đơn online — lúc đó đơn không còn "chờ QR hết hạn" nữa,
            // job phải bỏ qua (nếu không sẽ hủy oan một đơn COD hợp lệ đang chờ giao).
            $order = $this->order->fresh(['paymentMethod']);

            if (! $order || ! ($order->paymentMethod?->isOnlineGateway() ?? false)) {
                Log::info('Bỏ qua hủy đơn quá hạn: đơn không còn là thanh toán online (đã đổi phương thức).', [
                    'order_id' => $this->order->id,
                ]);

                return;
            }

            $cancelled = $cancellationService->cancelPendingUnpaid(
                $order,
                'Quá hạn thanh toán online (30 phút)'
            );

            if (! $cancelled) {
                Log::info('Bỏ qua hủy đơn quá hạn: đơn đã được thanh toán hoặc xử lý trước đó.', [
                    'order_id' => $this->order->id,
                ]);
            }
        } catch (Throwable $e) {
            // Không nuốt lỗi: ném lại để hàng đợi retry (tries = 3). Việc nhả kho quan trọng,
            // và cron `orders:expire-stale-payos` vẫn là lưới an toàn nếu job thất bại hẳn.
            Log::error('Lỗi khi tự hủy đơn quá hạn thanh toán.', [
                'order_id' => $this->order->id,
                'error'    => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Được gọi khi Job thất bại hoàn toàn (hết $tries).
     * Không cố hủy đơn ở đây — cron `orders:expire-stale-payos` (chạy mỗi 5 phút)
     * sẽ dọn nốt bằng đúng service idempotent này.
     */
    public function failed(Throwable $exception): void
    {
        Log::critical('Job tự hủy đơn quá hạn thất bại hoàn toàn — chờ cron dọn.', [
            'order_id' => $this->order->id,
            'error'    => $exception->getMessage(),
        ]);
    }
}
