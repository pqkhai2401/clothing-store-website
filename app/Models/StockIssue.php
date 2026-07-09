<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockIssue extends Model implements \OwenIt\Auditing\Contracts\Auditable
{
    use HasFactory, SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    public const STATUS_DRAFT     = 'draft';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    public const ISSUE_TYPE_SALE            = 'sale';
    public const ISSUE_TYPE_RETURN_SUPPLIER = 'return_supplier';
    public const ISSUE_TYPE_ADJUSTMENT      = 'adjustment';
    public const ISSUE_TYPE_DAMAGED         = 'damaged';
    public const ISSUE_TYPE_TRANSFER        = 'transfer';

    // Chỉ loại xuất kho này mới cho phép Admin tự chỉnh đơn giá xuất; các loại còn lại luôn chốt theo giá vốn hệ thống.
    public const REASON_TYPES_ALLOWING_PRICE_EDIT = [self::ISSUE_TYPE_RETURN_SUPPLIER];

    public const ISSUE_TYPE_LABELS = [
        self::ISSUE_TYPE_SALE            => 'Xuất bán hàng',
        self::ISSUE_TYPE_RETURN_SUPPLIER => 'Xuất trả nhà cung cấp',
        self::ISSUE_TYPE_ADJUSTMENT      => 'Điều chỉnh giảm sau kiểm kê',
        self::ISSUE_TYPE_DAMAGED         => 'Xuất hủy hàng lỗi / hư hỏng',
        self::ISSUE_TYPE_TRANSFER        => 'Xuất điều chuyển kho',
    ];

    protected $fillable = [
        'code',
        'issue_type',
        'warehouse_id',
        'order_id',
        'reason',
        'note',
        'status',
        'total_amount',
        'total_quantity',
        'total_cost_amount',
        'total_sale_amount',
        'created_by',
        'confirmed_by',
        'confirmed_at',
        'cancelled_by',
        'cancelled_at',
        'issued_at',
        'adjusted_by',
        'adjusted_at',
        'adjustment_reason',
        'adjustment_goods_receipt_id',
    ];

    protected $casts = [
        'total_amount'      => 'decimal:2',
        'total_quantity'    => 'integer',
        'total_cost_amount' => 'decimal:2',
        'total_sale_amount' => 'decimal:2',
        'issued_at'         => 'datetime',
        'confirmed_at'      => 'datetime',
        'cancelled_at'      => 'datetime',
        'adjusted_at'       => 'datetime',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    public function confirmer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    public function canceller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function adjuster(): BelongsTo
    {
        return $this->belongsTo(User::class, 'adjusted_by');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function adjustmentGoodsReceipt(): BelongsTo
    {
        return $this->belongsTo(GoodsReceipt::class, 'adjustment_goods_receipt_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(StockIssueItem::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(StockIssueLog::class);
    }

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function allowsPriceEdit(): bool
    {
        return in_array($this->issue_type, self::REASON_TYPES_ALLOWING_PRICE_EDIT, true);
    }
}
