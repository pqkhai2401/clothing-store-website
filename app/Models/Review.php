<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Review extends Model implements \OwenIt\Auditing\Contracts\Auditable
{
    use HasFactory, SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    /**
     * Các hằng số trạng thái kiểm duyệt.
     * Dùng hằng số thay cho chuỗi "magic string" để tránh gõ sai và dễ bảo trì.
     */
    public const STATUS_PENDING  = 'pending';   // Chờ AI kiểm duyệt
    public const STATUS_APPROVED = 'approved';  // AI đã duyệt -> hiển thị công khai
    public const STATUS_REJECTED = 'rejected';  // AI từ chối -> ẩn khỏi website
    public const STATUS_FLAGGED  = 'flagged';   // AI nghi ngờ -> chờ Admin duyệt tay

    /**
     * Các trường được phép gán hàng loạt (mass assignment).
     */
    protected $fillable = [
        'user_id',
        'product_id',
        'order_id',
        'rating',
        'comment',
        'status',
        'ai_score',
        'ai_reason',
    ];

    /**
     * Ép kiểu dữ liệu tự động khi đọc/ghi.
     */
    protected $casts = [
        'rating'   => 'integer',
        'ai_score' => 'integer',
    ];

    /**
     * Quan hệ: Đánh giá thuộc về một người dùng.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Quan hệ: Đánh giá thuộc về một sản phẩm.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Quan hệ: Đánh giá gắn với đơn hàng mà khách đã mua sản phẩm.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Scope truy vấn: chỉ lấy các đánh giá đã được DUYỆT (approved).
     * Cách dùng: Review::approved()->get();
     */
    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_APPROVED);
    }
}
