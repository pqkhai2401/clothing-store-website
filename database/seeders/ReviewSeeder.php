<?php

namespace Database\Seeders;

use App\Enums\OrderStatus;
use App\Models\OrderItem;
use App\Models\Review;
use Illuminate\Database\Seeder;

/**
 * Sinh đánh giá sản phẩm dựa trên các ĐƠN HÀNG ĐÃ HOÀN TẤT thật (OrderSeeder
 * chạy trước seeder này) — đúng luồng nghiệp vụ thật: chỉ khách đã mua và
 * nhận hàng mới được review. Không phải khách nào mua xong cũng review nên
 * chỉ ~55% dòng đơn hoàn tất được chọn ngẫu nhiên để sinh đánh giá.
 *
 * status/ai_score/ai_reason được set thẳng để mô phỏng kết quả sau khi đã
 * qua lớp kiểm duyệt AI (xem GeminiModerationService) — phần lớn approved,
 * một số nhỏ pending/flagged/rejected cho đúng phân bố thật.
 */
class ReviewSeeder extends Seeder
{
    /** @var array<int, string[]> Mẫu bình luận theo số sao — chèn %s = tên sản phẩm. */
    private array $templatesByRating = [
        5 => [
            '%s chất lượng rất tốt, đúng như mô tả, đường may chắc chắn. Chắc chắn sẽ ủng hộ shop dài dài!',
            'Quá ưng ý với %s! Vải mát, form chuẩn, giao hàng nhanh và đóng gói cẩn thận.',
            'Đây là lần thứ 2 mình mua %s, chất lượng vẫn ổn định như lần đầu. 5 sao xứng đáng.',
            '%s đẹp hơn cả ảnh trên web, mặc lên rất ưng. Nhân viên tư vấn nhiệt tình nữa.',
        ],
        4 => [
            '%s ổn, chất liệu tốt, chỉ hơi lâu giao một chút nhưng đóng gói kỹ.',
            'Nhìn chung hài lòng với %s, form đẹp. Trừ 1 sao vì màu thực tế nhạt hơn ảnh chút xíu.',
            '%s mặc thoải mái, đáng tiền. Sẽ cân nhắc mua thêm màu khác.',
        ],
        3 => [
            '%s tạm ổn, đúng mô tả nhưng chất vải mỏng hơn mình nghĩ.',
            'Bình thường, %s không có gì nổi bật nhưng cũng không tệ so với giá tiền.',
        ],
        2 => [
            '%s hơi thất vọng, size lệch so với bảng size trên web, phải đổi lại.',
            'Chất lượng %s chưa như kỳ vọng, đường chỉ có vài chỗ chưa gọn.',
        ],
        1 => [
            '%s không giống hình, chất vải khác hẳn mô tả. Rất tiếc vì đã đặt.',
        ],
    ];

    /** @var array<int, int> Trọng số phân bố rating (tổng 100). */
    private array $ratingWeights = [5 => 45, 4 => 32, 3 => 13, 2 => 7, 1 => 3];

    public function run(): void
    {
        if (Review::count() > 0) {
            return;
        }

        $completedItems = OrderItem::whereHas('order', fn ($q) => $q->where('status', OrderStatus::COMPLETED->value))
            ->with(['order:id,user_id', 'productVariant.product:id,name'])
            ->get();

        if ($completedItems->isEmpty()) {
            return;
        }

        // Gộp theo (user_id, product_id) để không vi phạm unique(user_id, product_id).
        $pairs = $completedItems
            ->filter(fn (OrderItem $item) => $item->productVariant && $item->productVariant->product)
            ->unique(fn (OrderItem $item) => $item->order->user_id.'-'.$item->productVariant->product_id)
            ->values();

        foreach ($pairs as $item) {
            // ~55% đơn hoàn tất thực sự được khách quay lại đánh giá.
            if (random_int(1, 100) > 55) {
                continue;
            }

            $rating = $this->randomRating();
            $productName = $item->productVariant->product->name;
            $templates = $this->templatesByRating[$rating];
            $comment = sprintf($templates[array_rand($templates)], $productName);

            [$status, $aiScore, $aiReason] = $this->moderationOutcome($rating);

            Review::firstOrCreate(
                ['user_id' => $item->order->user_id, 'product_id' => $item->productVariant->product_id],
                [
                    'order_id' => $item->order_id,
                    'rating' => $rating,
                    'comment' => $comment,
                    'status' => $status,
                    'ai_score' => $aiScore,
                    'ai_reason' => $aiReason,
                ]
            );
        }
    }

    private function randomRating(): int
    {
        $total = array_sum($this->ratingWeights);
        $roll = random_int(1, $total);
        $cumulative = 0;

        foreach ($this->ratingWeights as $rating => $weight) {
            $cumulative += $weight;
            if ($roll <= $cumulative) {
                return $rating;
            }
        }

        return 5;
    }

    /**
     * @return array{0: string, 1: int, 2: ?string}
     */
    private function moderationOutcome(int $rating): array
    {
        $roll = random_int(1, 100);

        if ($roll <= 90) {
            return [Review::STATUS_APPROVED, random_int(80, 100), null];
        }

        if ($roll <= 95) {
            return [Review::STATUS_PENDING, null, null];
        }

        if ($roll <= 98) {
            return [Review::STATUS_FLAGGED, random_int(40, 65), 'Nội dung nghi ngờ chứa từ ngữ không phù hợp, chờ admin duyệt tay.'];
        }

        return [Review::STATUS_REJECTED, random_int(0, 30), 'Nội dung không liên quan đến sản phẩm hoặc vi phạm quy định bình luận.'];
    }
}
