<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class GoodsReceipt extends Model implements \OwenIt\Auditing\Contracts\Auditable
{
    use HasFactory, SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    public const STATUS_DRAFT     = 'draft';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_ADJUSTED  = 'adjusted';
    public const STATUS_CANCELLED = 'cancelled';

    public const RECEIPT_TYPE_PURCHASE = 'purchase';
    public const RECEIPT_TYPE_ADJUSTMENT = 'adjustment';
    public const RECEIPT_TYPE_RETURN = 'return';
    public const RECEIPT_TYPE_INITIAL = 'initial';

    public const SOURCE_TYPE_SUPPLIER = 'supplier';
    public const SOURCE_TYPE_INTERNAL = 'internal';

    protected $fillable = [
        'code',
        'receipt_type',
        'source_type',
        'receipt_reason',
        'supplier_id',
        'warehouse_id',
        'note',
        'status',
        'total_amount',
        'total_quantity',
        'created_by',
        'confirmed_by',
        'confirmed_at',
        'cancelled_by',
        'cancelled_at',
        'completed_at',
        'received_at',
        'deleted_by',
        'adjusted_by',
        'adjusted_at',
        'adjustment_reason',
        'adjustment_stock_issue_id',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'confirmed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'completed_at' => 'datetime',
        'adjusted_at' => 'datetime',
        'received_at'  => 'datetime',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

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

    public function adjustmentStockIssue(): BelongsTo
    {
        return $this->belongsTo(StockIssue::class, 'adjustment_stock_issue_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(GoodsReceiptItem::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(GoodsReceiptLog::class);
    }

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isAdjusted(): bool
    {
        return $this->status === self::STATUS_ADJUSTED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }
}
