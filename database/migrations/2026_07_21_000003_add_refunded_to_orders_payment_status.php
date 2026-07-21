<?php

use App\Enums\PaymentStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Bổ sung trạng thái thanh toán "refunded" (đã hoàn tiền) cho bảng orders.
 *
 * Vì sao cần: khi một đơn ĐÃ THU TIỀN bị hủy, tiền vẫn đang giữ của khách nên hệ thống
 * cảnh báo "Cần hoàn tiền". Sau khi admin hoàn tiền thủ công (chuyển khoản/dashboard cổng),
 * phải có trạng thái để đánh dấu đã xử lý xong — nếu không cảnh báo sẽ hiện mãi.
 *
 * Cột là MySQL ENUM nên bắt buộc ALTER mới ghi được giá trị mới. Dùng raw SQL thay cho
 * ->change() để không phụ thuộc doctrine/dbal và giữ nguyên default.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement(
            "ALTER TABLE `orders` MODIFY `payment_status` ENUM('unpaid','paid','refunded') NOT NULL DEFAULT '"
            . PaymentStatus::UNPAID->value . "'"
        );
    }

    public function down(): void
    {
        // Đưa các đơn đã hoàn tiền về 'paid' trước, nếu không việc thu hẹp ENUM sẽ làm mất dữ liệu.
        DB::table('orders')->where('payment_status', 'refunded')->update(['payment_status' => 'paid']);

        DB::statement(
            "ALTER TABLE `orders` MODIFY `payment_status` ENUM('unpaid','paid') NOT NULL DEFAULT '"
            . PaymentStatus::UNPAID->value . "'"
        );
    }
};
