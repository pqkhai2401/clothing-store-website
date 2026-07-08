<?php

namespace App\Jobs;

use App\Models\Review;
use App\Services\GeminiModerationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * =============================================================================
 *  JOB CHẠY NGẦM: KIỂM DUYỆT ĐÁNH GIÁ BẰNG AI
 * =============================================================================
 *
 * Job này được đẩy vào Laravel Queue ngay khi khách hàng gửi đánh giá. Nó sẽ
 * được worker (php artisan queue:work) xử lý BẤT ĐỒNG BỘ ở phía sau, giúp khách
 * hàng không phải chờ đợi thời gian gọi API của Gemini.
 *
 * Luồng xử lý:
 *   1. Lấy nội dung bình luận của review hiện tại.
 *   2. Gọi GeminiModerationService để phân tích.
 *   3. Cập nhật status / ai_score / ai_reason vào bảng reviews.
 *   4. Nếu có bất kỳ lỗi nào (mạng, API, parse JSON...) -> chuyển review sang
 *      trạng thái 'flagged' để Admin duyệt tay, TRÁNH làm sập hàng đợi.
 */
class ProcessReviewModeration implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Số lần thử lại tối đa nếu Job ném exception.
     * Đặt = 3 để phòng lỗi mạng tạm thời khi gọi Gemini.
     */
    public int $tries = 3;

    /**
     * Thời gian tối đa (giây) cho phép Job chạy trước khi bị coi là timeout.
     */
    public int $timeout = 60;

    /**
     * Constructor nhận vào bản ghi Review cần kiểm duyệt.
     *
     * Nhờ trait SerializesModels, Laravel chỉ lưu KHÓA CHÍNH (id) của review
     * xuống hàng đợi rồi tự động truy vấn lại bản ghi mới nhất khi Job chạy
     * -> luôn làm việc trên dữ liệu cập nhật nhất.
     */
    public function __construct(
        public Review $review
    ) {
    }

    /**
     * Nội dung công việc chính, được worker gọi khi tới lượt xử lý.
     *
     * GeminiModerationService được "type-hint" ở tham số -> Laravel tự động
     * khởi tạo và tiêm (Dependency Injection) vào cho ta.
     */
    public function handle(GeminiModerationService $moderationService): void
    {
        try {
            // 1. Gọi AI phân tích nội dung bình luận.
            $result = $moderationService->moderate($this->review->comment);

            // 2. Cập nhật kết quả kiểm duyệt vào Database.
            //    $result đã được Service chuẩn hóa: status hợp lệ, score 0..100.
            $this->review->update([
                'status'    => $result['status'],
                'ai_score'  => $result['confidence_score'],
                'ai_reason' => $result['reason'],
            ]);

            // 3. Ghi log để tiện theo dõi hoạt động kiểm duyệt tự động.
            Log::info('Đã kiểm duyệt đánh giá bằng AI', [
                'review_id' => $this->review->id,
                'status'    => $result['status'],
                'ai_score'  => $result['confidence_score'],
            ]);
        } catch (Throwable $e) {
            // 4. Bọc try-catch để "an toàn tuyệt đối":
            //    Dù Gemini lỗi hay JSON hỏng, ta KHÔNG để Job văng exception làm
            //    hỏng hàng đợi. Thay vào đó, chuyển review sang 'flagged' để con
            //    người xem xét thủ công, đồng thời lưu lại lý do lỗi.
            Log::error('Lỗi khi kiểm duyệt đánh giá bằng AI', [
                'review_id' => $this->review->id,
                'error'     => $e->getMessage(),
            ]);

            $this->review->update([
                'status'    => Review::STATUS_FLAGGED,
                'ai_score'  => 0,
                'ai_reason' => 'Hệ thống AI gặp sự cố khi kiểm duyệt, cần Admin duyệt thủ công.',
            ]);
        }
    }

    /**
     * Được gọi khi Job thất bại hoàn toàn (đã hết số lần thử lại $tries).
     * Chốt chặn cuối cùng: bảo đảm review không bao giờ kẹt ở trạng thái 'pending'.
     */
    public function failed(Throwable $exception): void
    {
        Log::critical('Job kiểm duyệt đánh giá thất bại hoàn toàn', [
            'review_id' => $this->review->id,
            'error'     => $exception->getMessage(),
        ]);

        // Truy vấn lại để chắc chắn cập nhật đúng bản ghi.
        Review::where('id', $this->review->id)->update([
            'status'    => Review::STATUS_FLAGGED,
            'ai_score'  => 0,
            'ai_reason' => 'Kiểm duyệt tự động thất bại nhiều lần, cần Admin xử lý.',
        ]);
    }
}
