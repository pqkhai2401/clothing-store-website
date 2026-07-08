<?php

namespace App\Http\Controllers\User;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Jobs\ProcessReviewModeration;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * =============================================================================
 *  CONTROLLER XỬ LÝ ĐÁNH GIÁ SẢN PHẨM (phía khách hàng)
 * =============================================================================
 */
class ReviewController extends Controller
{
    /**
     * Lưu một đánh giá mới cho sản phẩm và đẩy Job kiểm duyệt AI vào hàng đợi.
     *
     * @param  Request  $request
     * @param  int      $productId  ID sản phẩm được đánh giá (lấy từ URL).
     */
    public function store(Request $request, $productId): RedirectResponse
    {
        // -------------------------------------------------------------------
        // BƯỚC 1: VALIDATE DỮ LIỆU ĐẦU VÀO
        // - rating: bắt buộc, số nguyên từ 1 đến 5.
        // - comment: bắt buộc, tối đa 1000 ký tự.
        // -------------------------------------------------------------------
        $validated = $request->validate([
            'rating'  => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['required', 'string', 'max:1000'],
        ], [
            'rating.required'  => 'Vui lòng chọn số sao đánh giá.',
            'rating.min'       => 'Số sao đánh giá không hợp lệ.',
            'rating.max'       => 'Số sao đánh giá không hợp lệ.',
            'comment.required' => 'Vui lòng nhập nội dung đánh giá.',
            'comment.max'      => 'Nội dung đánh giá không được vượt quá 1000 ký tự.',
        ]);

        // Bảo đảm sản phẩm tồn tại (nếu không sẽ trả 404).
        $product = Product::findOrFail($productId);
        $userId  = Auth::id();

        // -------------------------------------------------------------------
        // BƯỚC 2: KIỂM TRA ĐIỀU KIỆN BẢO MẬT (QUYỀN ĐÁNH GIÁ)
        // Khách chỉ được đánh giá nếu THỰC SỰ sở hữu một đơn hàng đã "completed"
        // (Đã hoàn thành) CHỨA sản phẩm này.
        //
        // Lưu ý cấu trúc dữ liệu: order_items KHÔNG trỏ thẳng tới product mà trỏ
        // tới product_variant -> nên phải lọc qua quan hệ productVariant.product_id.
        // -------------------------------------------------------------------
        $eligibleOrder = Order::where('user_id', $userId)
            ->where('status', OrderStatus::COMPLETED->value)
            ->whereHas('orderItems.productVariant', function ($query) use ($product) {
                $query->where('product_id', $product->id);
            })
            ->latest()
            ->first();

        // Nếu không tìm thấy đơn hàng hợp lệ -> chặn, trả về thông báo lỗi.
        if (!$eligibleOrder) {
            return back()->with('error', 'Bạn cần mua sản phẩm này để có thể đánh giá.');
        }

        // -------------------------------------------------------------------
        // BƯỚC 3: CHỐNG ĐÁNH GIÁ TRÙNG
        // Mỗi user chỉ đánh giá 1 lần / sản phẩm (khớp ràng buộc UNIQUE ở DB).
        // -------------------------------------------------------------------
        $alreadyReviewed = Review::where('user_id', $userId)
            ->where('product_id', $product->id)
            ->exists();

        if ($alreadyReviewed) {
            return back()->with('error', 'Bạn đã đánh giá sản phẩm này rồi.');
        }

        // -------------------------------------------------------------------
        // BƯỚC 4: LƯU ĐÁNH GIÁ VỚI TRẠNG THÁI MẶC ĐỊNH 'pending' (CHỜ DUYỆT)
        // -------------------------------------------------------------------
        $review = Review::create([
            'user_id'    => $userId,
            'product_id' => $product->id,
            'order_id'   => $eligibleOrder->id,
            'rating'     => $validated['rating'],
            'comment'    => $validated['comment'],
            'status'     => Review::STATUS_PENDING,
        ]);

        // -------------------------------------------------------------------
        // BƯỚC 5: ĐẨY TÁC VỤ KIỂM DUYỆT AI VÀO HÀNG ĐỢI (chạy ngầm)
        // -> Trả kết quả về cho khách NGAY LẬP TỨC, không bắt họ chờ gọi API.
        // -------------------------------------------------------------------
        ProcessReviewModeration::dispatch($review);

        // -------------------------------------------------------------------
        // BƯỚC 6: PHẢN HỒI THÀNH CÔNG
        // -------------------------------------------------------------------
        return back()->with(
            'success',
            'Đánh giá của bạn đã được gửi thành công và đang được hệ thống kiểm duyệt tự động!'
        );
    }
}
