<?php

namespace App\Models;

use OwenIt\Auditing\Models\Audit;

/**
 * Model chỉ-đọc để hiển thị nhật ký hoạt động (audits) trên trang admin.
 *
 * Kế thừa Audit của owen-it/laravel-auditing (đọc cùng bảng `audits`),
 * bổ sung các nhãn tiếng Việt + accessor phục vụ giao diện.
 */
class ActivityLog extends Audit
{
    protected $table = 'audits';

    /**
     * Nhãn tiếng Việt cho từng loại hành động (event).
     */
    public const EVENT_LABELS = [
        'created'      => 'Thêm mới',
        'updated'      => 'Cập nhật',
        'deleted'      => 'Xóa',
        'restored'     => 'Khôi phục',
        'role_updated'   => 'Đổi vai trò',
        'password_reset' => 'Reset mật khẩu',
        'login'          => 'Đăng nhập',
        'logout'         => 'Đăng xuất',
        // Cảnh báo tiền bạc cần con người xử lý (xem PaymentReconciliationService).
        'refund_required'   => 'Cần hoàn tiền',
        'refund_completed'  => 'Đã hoàn tiền',
        'payment_reconcile' => 'Đối soát thanh toán',
    ];

    /**
     * Tone (class hậu tố badge) cho từng loại hành động.
     */
    public const EVENT_TONES = [
        'created'      => 'success',
        'updated'      => 'info',
        'deleted'      => 'danger',
        'restored'     => 'warning',
        'role_updated'   => 'warning',
        'password_reset' => 'warning',
        'login'          => 'neutral',
        'logout'         => 'muted',
        'refund_required'   => 'danger',
        'refund_completed'  => 'success',
        'payment_reconcile' => 'danger',
    ];

    /**
     * Nhãn tiếng Việt cho từng loại đối tượng (auditable_type FQCN).
     */
    public const SUBJECT_LABELS = [
        \App\Models\Product::class      => 'Sản phẩm',
        \App\Models\Order::class        => 'Đơn hàng',
        \App\Models\Voucher::class      => 'Voucher',
        \App\Models\User::class         => 'Tài khoản',
        \App\Models\GoodsReceipt::class => 'Phiếu nhập kho',
        \App\Models\StockIssue::class   => 'Phiếu xuất kho',
        \App\Models\Category::class     => 'Danh mục',
        \App\Models\Brand::class        => 'Thương hiệu',
        \App\Models\Color::class        => 'Màu sắc',
        \App\Models\Size::class         => 'Kích thước',
        \App\Models\Collection::class   => 'Bộ sưu tập',
        \App\Models\Supplier::class     => 'Nhà cung cấp',
        \App\Models\Review::class       => 'Đánh giá',
    ];

    /**
     * Danh sách đối tượng dùng cho bộ lọc (value => nhãn).
     *
     * @return array<string, string>
     */
    public static function subjectOptions(): array
    {
        return self::SUBJECT_LABELS;
    }

    /**
     * Danh sách hành động dùng cho bộ lọc (value => nhãn).
     *
     * @return array<string, string>
     */
    public static function eventOptions(): array
    {
        return self::EVENT_LABELS;
    }

    /* ─────────────── Accessors ─────────────── */

    public function getEventLabelAttribute(): string
    {
        return self::EVENT_LABELS[$this->event] ?? ucfirst((string) $this->event);
    }

    public function getEventToneAttribute(): string
    {
        return self::EVENT_TONES[$this->event] ?? 'neutral';
    }

    public function getSubjectLabelAttribute(): string
    {
        if (! $this->auditable_type) {
            return '—';
        }

        return self::SUBJECT_LABELS[$this->auditable_type] ?? class_basename($this->auditable_type);
    }

    /**
     * Tên người thực hiện thao tác (đọc từ quan hệ user morphTo).
     */
    public function getCauserNameAttribute(): string
    {
        // Ưu tiên quan hệ user (người thực hiện). Với sự kiện đăng xuất,
        // guard đã xoá user nên user_id có thể rỗng — khi đó dùng chính
        // đối tượng bị audit (tài khoản) để hiển thị tên.
        $user = $this->user;

        if (! $user && $this->auditable_type === \App\Models\User::class) {
            $user = $this->auditable;
        }

        if (! $user) {
            return 'Hệ thống';
        }

        return $user->username ?? $user->email ?? ('#' . $user->getKey());
    }

    /**
     * Mô tả ngắn gọn đối tượng bị tác động (mã / tên nếu suy ra được).
     */
    public function getSubjectDescriptionAttribute(): string
    {
        $values = array_merge((array) $this->old_values, (array) $this->new_values);

        foreach (['code', 'name', 'title', 'order_code', 'username', 'sku'] as $key) {
            if (! empty($values[$key]) && is_scalar($values[$key])) {
                return (string) $values[$key];
            }
        }

        if ($this->auditable_id) {
            return '#' . $this->auditable_id;
        }

        return '';
    }
}
