<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration bổ sung các trường phục vụ QUY TRÌNH KIỂM DUYỆT TỰ ĐỘNG BẰNG AI
 * cho bảng `reviews` (bảng đánh giá sản phẩm đã tồn tại sẵn trong dự án).
 *
 * Lý do tách thành một migration riêng (thay vì sửa migration gốc):
 *  - Bảng `reviews` đã được tạo và có thể đã chứa dữ liệu ở môi trường chạy thật.
 *  - Theo đúng quy ước của Laravel, mọi thay đổi cấu trúc bảng sau khi đã deploy
 *    đều nên nằm ở một file migration mới để có thể rollback an toàn.
 */
return new class extends Migration
{
    /**
     * Chạy migration: thêm các cột mới vào bảng `reviews`.
     */
    public function up(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            // Liên kết tới đơn hàng đã mua sản phẩm này.
            // -> Dùng để chứng minh khách hàng THỰC SỰ đã mua hàng trước khi đánh giá.
            // nullable() để tương thích với các bản ghi đánh giá cũ (nếu có) chưa gắn đơn hàng.
            $table->foreignId('order_id')
                ->nullable()
                ->after('product_id')
                ->constrained('orders')
                ->nullOnDelete();

            // Trạng thái kiểm duyệt của đánh giá:
            //  - pending : Chờ AI xử lý (trạng thái mặc định ngay khi khách gửi).
            //  - approved: AI đã duyệt -> hiển thị công khai.
            //  - rejected: AI từ chối (tục tĩu, công kích, spam quảng cáo...) -> ẩn.
            //  - flagged : AI không chắc chắn -> chờ Admin duyệt tay.
            $table->enum('status', ['pending', 'approved', 'rejected', 'flagged'])
                ->default('pending')
                ->after('comment');

            // Độ tin cậy (%) của AI đối với quyết định đã đưa ra (0 - 100).
            $table->unsignedTinyInteger('ai_score')
                ->nullable()
                ->after('status');

            // Lý do AI đưa ra quyết định (bằng tiếng Việt) — lưu lại để Admin đối chiếu.
            $table->text('ai_reason')
                ->nullable()
                ->after('ai_score');

            // Đánh index cho cột status để truy vấn danh sách "approved" trên trang
            // chi tiết sản phẩm được nhanh hơn.
            $table->index('status');
        });
    }

    /**
     * Rollback migration: gỡ bỏ các cột đã thêm.
     */
    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            // Gỡ khóa ngoại + cột order_id.
            $table->dropForeign(['order_id']);
            $table->dropColumn('order_id');

            // Gỡ index và các cột kiểm duyệt.
            $table->dropIndex(['status']);
            $table->dropColumn(['status', 'ai_score', 'ai_reason']);
        });
    }
};
