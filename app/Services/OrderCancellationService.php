<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\StockIssue;
use App\Models\StockIssueLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * =============================================================================
 *  HỦY ĐƠN & NHẢ KHO ĐANG GIỮ (Hold & Release)
 * =============================================================================
 *
 * Từ khi áp dụng "Hold & Release", kho bị trừ FIFO ngay lúc checkout với
 * reference_type = 'order'. Do đó MỌI đường hủy đơn đều phải nhả kho, nếu không
 * hàng sẽ bị giữ vĩnh viễn (rò rỉ tồn kho).
 *
 * Gom về một chỗ vì trước đây logic hủy nằm rải rác ở 4 nơi với 3 hành vi khác nhau
 * (admin hủy / khách tự hủy / supersede đơn PayOS cũ / cron quá hạn).
 */
class OrderCancellationService
{
    /**
     * Hủy đơn CHƯA thanh toán và nhả kho — ATOMIC + IDEMPOTENT.
     *
     * Khoá dòng đơn rồi KIỂM TRA LẠI trạng thái trước khi hủy: nếu đơn đã được
     * thanh toán/xử lý/hủy bởi luồng khác (webhook, job, cron, admin) thì bỏ qua.
     * Đây là chốt chặn để job 30' và cron `orders:expire-stale-payos` chạy song song
     * KHÔNG hoàn kho hai lần (restoreByReference gọi 2 lần sẽ cộng khống tồn).
     *
     * @return bool true nếu lần gọi này thực sự hủy đơn; false nếu bỏ qua.
     */
    public function cancelPendingUnpaid(Order $order, string $reason): bool
    {
        return DB::transaction(function () use ($order, $reason) {
            $fresh = Order::whereKey($order->id)->lockForUpdate()->first();

            if (! $fresh
                || $fresh->status !== OrderStatus::PENDING->value
                || $fresh->payment_status !== PaymentStatus::UNPAID->value
            ) {
                return false; // luồng khác đã xử lý trước → không đụng vào
            }

            $fresh->update(['status' => OrderStatus::CANCELLED->value]);

            $this->releaseHold($fresh);
            VoucherService::releaseUsage($fresh->id);

            Log::info('Đã hủy đơn chưa thanh toán và nhả kho.', [
                'order_id'   => $fresh->id,
                'order_code' => $fresh->order_code,
                'reason'     => $reason,
            ]);

            return true;
        });
    }

    /**
     * Nhả toàn bộ kho đang giữ của đơn: cộng trả về ĐÚNG lô đã trừ, đúng giá vốn gốc.
     *
     * Gọi cho cả 2 reference để an toàn với dữ liệu cũ:
     *  - 'order'       → lần trừ lúc checkout (mô hình Hold hiện tại).
     *  - 'stock_issue' → đơn cũ đã trừ theo mô hình trước đây (trừ lúc admin xử lý).
     * Mỗi lời gọi tự no-op nếu không có bút toán export khớp → không cộng khống.
     *
     * Phải nằm trong transaction do phía gọi mở.
     */
    public function releaseHold(Order $order): void
    {
        $batchService = app(InventoryBatchService::class);
        $userId = Auth::id() ?? $order->created_by;

        // Kho giữ từ lúc checkout. Dùng bản tính theo SỐ DƯ (đã xuất − đã hoàn) để:
        //  - idempotent (gọi 2 lần không cộng khống),
        //  - đúng cả khi đơn từng được sửa nội dung (nhả hold cũ → giữ hold mới cùng reference).
        $batchService->restoreOutstandingByReference('order', $order->id, $userId);

        $stockIssue = StockIssue::where('order_id', $order->id)
            ->where('issue_type', StockIssue::ISSUE_TYPE_SALE)
            ->whereIn('status', [StockIssue::STATUS_DRAFT, StockIssue::STATUS_COMPLETED])
            ->first();

        if (! $stockIssue) {
            return;
        }

        // Tương thích ngược: đơn phát sinh trước Hold & Release trừ kho theo phiếu xuất.
        $batchService->restoreByReference('stock_issue', $stockIssue->id, 'stock_issue', $stockIssue->id, $userId);

        $stockIssue->update([
            'status'       => StockIssue::STATUS_CANCELLED,
            'cancelled_by' => $userId ?? 1,
            'cancelled_at' => now(),
        ]);

        StockIssueLog::create([
            'stock_issue_id' => $stockIssue->id,
            'user_id'        => $userId ?? 1,
            'action'         => 'cancelled',
            'message'        => 'Hoàn kho do hủy đơn hàng #' . ($order->order_code ?? $order->id),
        ]);
    }
}
