<?php

namespace App\Http\Controllers\User;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Services\ProfanityFilter;
use Illuminate\Http\JsonResponse;
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
     * Lưu một đánh giá mới cho sản phẩm.
     *
     * CHÍNH SÁCH KIỂM DUYỆT (đồng bộ, phản hồi ngay bằng toast):
     *  - DUYỆT mọi bình luận, bất kể chấm bao nhiêu sao (khen/chê là quyền của khách).
     *  - CHỈ TỪ CHỐI khi bình luận có từ ngữ chửi thề / tục tĩu -> trả toast từ chối.
     *
     * @param  Request         $request
     * @param  int             $productId  ID sản phẩm được đánh giá (lấy từ URL).
     * @param  ProfanityFilter $profanity  Bộ lọc từ ngữ vi phạm (tự inject).
     *
     * Hỗ trợ 2 kiểu phản hồi:
     *  - AJAX (từ trang đơn hàng)       -> trả JSON để hiển thị toast.
     *  - Form thường (trang chi tiết SP) -> redirect back kèm flash message.
     */
    public function store(Request $request, $productId, ProfanityFilter $profanity): JsonResponse|RedirectResponse
    {
        // -------------------------------------------------------------------
        // BƯỚC 1: VALIDATE DỮ LIỆU ĐẦU VÀO
        // - rating: bắt buộc, số nguyên từ 1 đến 5.
        // - comment: bắt buộc, tối đa 1000 ký tự.
        // - order_id: tùy chọn (trang đơn hàng gửi kèm để đánh giá đúng đơn).
        // -------------------------------------------------------------------
        $validated = $request->validate([
            'rating'   => ['required', 'integer', 'min:1', 'max:5'],
            'comment'  => ['required', 'string', 'max:1000'],
            'order_id' => ['nullable', 'integer'],
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
        // BƯỚC 2: XÁC ĐỊNH ĐƠN HÀNG ĐỦ ĐIỀU KIỆN ĐÁNH GIÁ
        // Khách chỉ được đánh giá nếu có đơn "completed" CHỨA sản phẩm này và
        // ĐƠN ĐÓ CHƯA được đánh giá. Nhờ khóa theo đơn hàng, mua lại đơn mới thì
        // được đánh giá tiếp (mỗi đơn 1 lần / sản phẩm).
        //
        // Cấu trúc dữ liệu: order_items -> product_variant -> product_id.
        // -------------------------------------------------------------------
        // Các đơn đã được đánh giá cho sản phẩm này (loại ra khỏi danh sách hợp lệ).
        $reviewedOrderIds = Review::where('user_id', $userId)
            ->where('product_id', $product->id)
            ->pluck('order_id')
            ->filter()   // bỏ các order_id null
            ->all();

        $orderQuery = Order::where('user_id', $userId)
            ->where('status', OrderStatus::COMPLETED->value)
            ->whereHas('orderItems.productVariant', function ($query) use ($product) {
                $query->where('product_id', $product->id);
            })
            ->whereNotIn('id', $reviewedOrderIds);

        // Nếu client gửi kèm order_id -> đánh giá đúng đơn đó.
        if (!empty($validated['order_id'])) {
            $orderQuery->where('id', $validated['order_id']);
        }

        $eligibleOrder = $orderQuery->latest()->first();

        // Không tìm thấy đơn hợp lệ chưa đánh giá.
        if (!$eligibleOrder) {
            // Phân biệt: đã đánh giá hết vs. chưa từng mua -> báo lý do phù hợp.
            $hasPurchased = Order::where('user_id', $userId)
                ->where('status', OrderStatus::COMPLETED->value)
                ->whereHas('orderItems.productVariant', function ($query) use ($product) {
                    $query->where('product_id', $product->id);
                })
                ->exists();

            $message = $hasPurchased
                ? 'Bạn đã đánh giá sản phẩm này cho đơn hàng tương ứng rồi.'
                : 'Bạn cần mua sản phẩm này để có thể đánh giá.';

            return $request->wantsJson()
                ? response()->json(['message' => $message], $hasPurchased ? 409 : 403)
                : back()->with('error', $message);
        }

        // -------------------------------------------------------------------
        // BƯỚC 3: KIỂM DUYỆT NỘI DUNG (ĐỒNG BỘ)
        // Chỉ chặn khi có từ ngữ chửi thề / tục tĩu. Còn lại duyệt hết.
        // -------------------------------------------------------------------
        if ($profanity->containsProfanity($validated['comment'])) {
            $message = 'Bình luận của bạn bị từ chối do vi phạm tiêu chuẩn cộng đồng';

            return $request->wantsJson()
                ? response()->json(['message' => $message], 422)
                : back()->with('error', $message);
        }

        // -------------------------------------------------------------------
        // BƯỚC 4: LƯU ĐÁNH GIÁ VỚI TRẠNG THÁI ĐÃ DUYỆT (approved) NGAY.
        // -------------------------------------------------------------------
        Review::create([
            'user_id'    => $userId,
            'product_id' => $product->id,
            'order_id'   => $eligibleOrder->id,
            'rating'     => $validated['rating'],
            'comment'    => $validated['comment'],
            'status'     => Review::STATUS_APPROVED,
        ]);

        // -------------------------------------------------------------------
        // BƯỚC 5: PHẢN HỒI THÀNH CÔNG
        // -------------------------------------------------------------------
        $message = 'Đánh giá của bạn đã được gửi thành công. Cảm ơn bạn đã đóng góp ý kiến!';

        return $request->wantsJson()
            ? response()->json(['success' => true, 'message' => $message], 201)
            : back()->with('success', $message);
    }
}
