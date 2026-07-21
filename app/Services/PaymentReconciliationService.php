<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Ghi nhận các bất thường thanh toán cần CON NGƯỜI đối soát/hoàn tiền. Không tự động
 * xử lý tiền (hoàn tiền là thao tác thủ công), chỉ đảm bảo hệ thống KHÔNG im lặng bỏ qua.
 */
class PaymentReconciliationService
{
    /**
     * Cổng thanh toán xác nhận đã thu tiền cho một đơn ĐÃ HỦY — thường xảy ra khi khách
     * đặt đơn mới (đơn online cũ bị supersede → hủy, đã nhả kho + voucher) nhưng vẫn quét
     * QR/đường link cũ để trả tiền.
     *
     * KHÔNG thể tự đánh dấu đơn này là "đã thanh toán" (sẽ tạo trạng thái mâu thuẫn
     * cancelled + paid, và có thể oversell vì kho đã được nhả cho đơn khác). Thay vào đó
     * ghi một bản ghi cảnh báo vào bảng `audits` để hiện lên trang Nhật ký hoạt động, giúp
     * admin phát hiện và liên hệ khách hoàn tiền/đối soát.
     */
    /**
     * Đơn ĐÃ THU TIỀN nhưng bị HỦY (admin hủy đơn đang xử lý/đang giao, khách đổi ý...).
     * Kho đã được nhả, nhưng TIỀN VẪN ĐANG GIỮ CỦA KHÁCH → phải hoàn lại.
     *
     * Hệ thống KHÔNG tự chuyển tiền (không tích hợp API hoàn tiền của cổng). Việc cần làm là
     * bảo đảm khoản này KHÔNG bị bỏ quên âm thầm: ghi một bản ghi vào `audits` để hiện lên
     * trang Nhật ký hoạt động, admin thấy và chủ động hoàn tiền cho khách.
     */
    public function flagRefundRequired(Order $order, string $reason): void
    {
        Log::critical('Đơn ĐÃ THANH TOÁN bị hủy — CẦN HOÀN TIỀN cho khách.', [
            'order_id'   => $order->id,
            'order_code' => $order->order_code,
            'amount'     => $order->total_money,
            'reason'     => $reason,
        ]);

        // Idempotent: một đơn chỉ cần MỘT bản ghi "cần hoàn tiền".
        $exists = DB::table('audits')
            ->where('event', 'refund_required')
            ->where('auditable_type', Order::class)
            ->where('auditable_id', $order->id)
            ->exists();

        if ($exists) {
            return;
        }

        DB::table('audits')->insert([
            'user_type'      => null,
            'user_id'        => auth()->id(),
            'event'          => 'refund_required',
            'auditable_type' => Order::class,
            'auditable_id'   => $order->id,
            'old_values'     => json_encode([], JSON_UNESCAPED_UNICODE),
            'new_values'     => json_encode([
                'order_code'     => $order->order_code,
                'payment_method' => $order->paymentMethod?->name,
                'amount'         => (string) $order->total_money,
                'reason'         => $reason,
                'message'        => 'Đơn đã thanh toán nhưng bị hủy — cần hoàn tiền cho khách.',
            ], JSON_UNESCAPED_UNICODE),
            'url'            => request()->fullUrl(),
            'ip_address'     => request()->ip(),
            'user_agent'     => substr((string) request()->userAgent(), 0, 1023),
            'tags'           => 'payment,refund',
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);
    }

    /**
     * Admin đã hoàn tiền xong cho một đơn — ghi vết để đối soát về sau (ai làm, lúc nào,
     * bao nhiêu, hoàn bằng cách nào). Cảnh báo "Cần hoàn tiền" sẽ tự tắt vì payment_status
     * đã chuyển sang 'refunded'.
     */
    public function logRefundCompleted(Order $order, ?string $note = null): void
    {
        Log::info('Đã hoàn tiền cho đơn hàng.', [
            'order_id'   => $order->id,
            'order_code' => $order->order_code,
            'amount'     => $order->refund_amount,
            'by'         => auth()->id(),
            'note'       => $note,
        ]);

        DB::table('audits')->insert([
            'user_type'      => auth()->id() ? \App\Models\User::class : null,
            'user_id'        => auth()->id(),
            'event'          => 'refund_completed',
            'auditable_type' => Order::class,
            'auditable_id'   => $order->id,
            'old_values'     => json_encode(['payment_status' => 'paid'], JSON_UNESCAPED_UNICODE),
            'new_values'     => json_encode([
                'payment_status' => 'refunded',
                'order_code'     => $order->order_code,
                'amount'         => (string) $order->refund_amount,
                'refunded_at'    => optional($order->refunded_at)->toDateTimeString(),
                'note'           => $note,
                'message'        => 'Admin xác nhận đã hoàn tiền cho khách.',
            ], JSON_UNESCAPED_UNICODE),
            'url'            => request()->fullUrl(),
            'ip_address'     => request()->ip(),
            'user_agent'     => substr((string) request()->userAgent(), 0, 1023),
            'tags'           => 'payment,refund',
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);
    }

    public function flagCancelledButPaid(Order $order, string $gateway, ?string $reference): void
    {
        Log::critical('Thanh toán vào đơn ĐÃ HỦY — cần đối soát/hoàn tiền.', [
            'order_id'   => $order->id,
            'order_code' => $order->order_code,
            'gateway'    => $gateway,
            'reference'  => $reference,
            'amount'     => $order->total_money,
        ]);

        // Idempotent: webhook + poll (hoặc cổng gọi lại nhiều lần) có thể cùng chạm tới —
        // chỉ giữ MỘT bản ghi đối soát cho mỗi đơn để admin không thấy trùng.
        $exists = DB::table('audits')
            ->where('event', 'payment_reconcile')
            ->where('auditable_type', Order::class)
            ->where('auditable_id', $order->id)
            ->exists();

        if ($exists) {
            return;
        }

        DB::table('audits')->insert([
            'user_type'      => null,
            'user_id'        => null,
            'event'          => 'payment_reconcile',
            'auditable_type' => Order::class,
            'auditable_id'   => $order->id,
            'old_values'     => json_encode([], JSON_UNESCAPED_UNICODE),
            'new_values'     => json_encode([
                'order_code' => $order->order_code,
                'gateway'    => $gateway,
                'reference'  => $reference,
                'amount'     => (string) $order->total_money,
                'message'    => 'Khách đã thanh toán cho đơn ĐÃ HỦY — cần liên hệ hoàn tiền/đối soát.',
            ], JSON_UNESCAPED_UNICODE),
            'url'            => request()->fullUrl(),
            'ip_address'     => request()->ip(),
            'user_agent'     => substr((string) request()->userAgent(), 0, 1023),
            'tags'           => 'payment,reconcile',
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);
    }
}
