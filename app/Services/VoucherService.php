<?php

namespace App\Services;

use App\Models\Voucher;
use App\Models\VoucherHistory;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

/**
 * Quy tắc áp dụng mã giảm giá dùng chung cho mọi nơi có thể áp voucher (checkout của khách,
 * API preview ở giỏ hàng, và admin tạo đơn thủ công) — gom về một chỗ để 3 nơi không tự
 * lặp lại (và có nguy cơ lệch luật với nhau) như trước đây.
 */
class VoucherService
{
    /**
     * Kiểm tra mã giảm giá có hợp lệ để áp cho đơn hàng subtotal này không.
     *
     * @throws ValidationException với key 'voucher_code' nếu không hợp lệ.
     */
    public static function validate(string $code, float $subtotal, ?int $userId): Voucher
    {
        $voucher = Voucher::where('code', $code)->first();

        if (! $voucher || ! $voucher->status) {
            throw ValidationException::withMessages([
                'voucher_code' => 'Mã giảm giá không tồn tại hoặc đã bị vô hiệu hóa.',
            ]);
        }

        $now = Carbon::now();
        if ($now->lt($voucher->start_date) || $now->gt($voucher->end_date)) {
            throw ValidationException::withMessages([
                'voucher_code' => 'Mã giảm giá đã hết hạn hoặc chưa đến thời gian sử dụng.',
            ]);
        }

        if ($voucher->used_count >= $voucher->quantity) {
            throw ValidationException::withMessages([
                'voucher_code' => 'Mã giảm giá đã hết lượt sử dụng.',
            ]);
        }

        if ($subtotal < (float) $voucher->min_order_amount) {
            throw ValidationException::withMessages([
                'voucher_code' => 'Đơn hàng chưa đạt giá trị tối thiểu để áp dụng mã giảm giá này.',
            ]);
        }

        // Khách hàng chưa xác định (đang nhập khách mới ở admin) thì chắc chắn chưa từng dùng
        // mã nào — bỏ qua kiểm tra này, không phải lỗ hổng vì user thực sự chưa tồn tại.
        if ($userId) {
            $alreadyUsed = VoucherHistory::where('user_id', $userId)
                ->where('voucher_id', $voucher->id)
                ->exists();

            if ($alreadyUsed) {
                throw ValidationException::withMessages([
                    'voucher_code' => 'Khách hàng này đã sử dụng mã giảm giá này rồi.',
                ]);
            }
        }

        return $voucher;
    }

    /**
     * Tính số tiền được giảm — % (có trần max_discount_amount nếu có) hoặc số tiền cố định,
     * không bao giờ vượt quá subtotal.
     */
    public static function calculateDiscount(Voucher $voucher, float $subtotal): float
    {
        if ($voucher->type === 'percentage') {
            $discountAmount = $subtotal * ((float) $voucher->value / 100);

            if ($voucher->max_discount_amount !== null) {
                $discountAmount = min($discountAmount, (float) $voucher->max_discount_amount);
            }
        } else {
            $discountAmount = (float) $voucher->value;
        }

        return min($discountAmount, $subtotal);
    }

    /**
     * Khóa dòng voucher + ghi nhận lượt dùng NGAY LÚC TẠO ĐƠN (trong transaction) — chống
     * race-condition giữa lúc validate() (chạy ngoài transaction, không lock) và lúc tạo đơn
     * thật sự: 2 request cùng lúc có thể cùng vượt qua validate() rồi cùng dùng vượt quantity,
     * hoặc cùng user dùng trùng 1 mã 2 lần.
     *
     * @throws ValidationException với key 'voucher_code' nếu bị giành mất suất trong lúc chờ lock.
     */
    public static function lockAndRecordUsage(int $voucherId, int $userId, int $orderId): void
    {
        $lockedVoucher = Voucher::whereKey($voucherId)->lockForUpdate()->first();

        if (! $lockedVoucher || $lockedVoucher->used_count >= $lockedVoucher->quantity) {
            throw ValidationException::withMessages([
                'voucher_code' => 'Mã giảm giá đã hết lượt sử dụng.',
            ]);
        }

        $alreadyUsed = VoucherHistory::where('user_id', $userId)
            ->where('voucher_id', $lockedVoucher->id)
            ->exists();

        if ($alreadyUsed) {
            throw ValidationException::withMessages([
                'voucher_code' => 'Khách hàng này đã sử dụng mã giảm giá này rồi.',
            ]);
        }

        VoucherHistory::create([
            'user_id'    => $userId,
            'voucher_id' => $lockedVoucher->id,
            'order_id'   => $orderId,
            'used_at'    => now(),
        ]);

        $lockedVoucher->increment('used_count');
    }

    /**
     * Giải phóng lượt dùng voucher khi đơn hàng bị hủy.
     */
    public static function releaseUsage(int $orderId): void
    {
        $history = VoucherHistory::where('order_id', $orderId)->first();
        if (! $history) {
            return;
        }

        $voucher = Voucher::whereKey($history->voucher_id)->lockForUpdate()->first();
        if ($voucher) {
            $voucher->decrement('used_count', 1);
        }

        $history->delete();
    }
}
