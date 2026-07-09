<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Voucher;
use App\Models\VoucherHistory;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VoucherController extends Controller
{
    /**
     * Kiểm tra và áp dụng mã giảm giá cho giỏ hàng.
     */
    public function apply(Request $request): JsonResponse
    {
        $request->validate([
            'code' => ['required', 'string'],
            'subtotal' => ['required', 'numeric', 'min:0'],
        ]);

        $code = $request->input('code');
        $subtotal = (float) $request->input('subtotal');

        $voucher = Voucher::where('code', $code)->first();

        if (! $voucher || ! $voucher->status) {
            return response()->json([
                'success' => false,
                'message' => 'Mã giảm giá không tồn tại hoặc đã bị vô hiệu hóa.',
            ], 422);
        }

        $now = Carbon::now();
        if ($now->lt($voucher->start_date) || $now->gt($voucher->end_date)) {
            return response()->json([
                'success' => false,
                'message' => 'Mã giảm giá đã hết hạn hoặc chưa đến thời gian sử dụng.',
            ], 422);
        }

        if ($voucher->used_count >= $voucher->quantity) {
            return response()->json([
                'success' => false,
                'message' => 'Mã giảm giá đã hết lượt sử dụng.',
            ], 422);
        }

        if ($subtotal < (float) $voucher->min_order_amount) {
            return response()->json([
                'success' => false,
                'message' => 'Đơn hàng chưa đạt giá trị tối thiểu để áp dụng mã giảm giá này.',
            ], 422);
        }

        if (Auth::check()) {
            $alreadyUsed = VoucherHistory::where('user_id', Auth::id())
                ->where('voucher_id', $voucher->id)
                ->exists();

            if ($alreadyUsed) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn đã sử dụng mã giảm giá này rồi.',
                ], 422);
            }
        }

        if ($voucher->type === 'percentage') {
            $discountAmount = $subtotal * ((float) $voucher->value / 100);

            if ($voucher->max_discount_amount !== null) {
                $discountAmount = min($discountAmount, (float) $voucher->max_discount_amount);
            }
        } else {
            $discountAmount = (float) $voucher->value;
        }

        $discountAmount = min($discountAmount, $subtotal);
        $newTotal = $subtotal - $discountAmount;

        return response()->json([
            'success' => true,
            'discount_amount' => round($discountAmount, 2),
            'new_total' => round($newTotal, 2),
        ]);
    }
}
