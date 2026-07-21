<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Yêu cầu hủy đơn do khách gửi khi đơn đã được xác nhận/đang giao (không tự hủy được).
 * Admin duyệt → đơn bị hủy, kho hoàn về đúng lô, và nếu đã thu tiền thì bật cờ "Cần hoàn tiền".
 */
class OrderCancelRequest extends Model
{
    use HasFactory;

    public const STATUS_PENDING  = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    public const STATUS_LABELS = [
        self::STATUS_PENDING  => 'Chờ duyệt',
        self::STATUS_APPROVED => 'Đã duyệt',
        self::STATUS_REJECTED => 'Bị từ chối',
    ];

    /** Trạng thái đơn mà khách được phép gửi yêu cầu hủy (chưa nhận hàng). */
    public const REQUESTABLE_ORDER_STATUSES = ['processing', 'shipping'];

    protected $fillable = [
        'order_id',
        'user_id',
        'reason',
        'status',
        'admin_note',
        'processed_by',
        'processed_at',
    ];

    protected $casts = [
        'processed_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }
}
