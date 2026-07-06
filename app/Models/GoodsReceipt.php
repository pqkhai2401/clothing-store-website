<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class GoodsReceipt extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUS_DRAFT     = 'draft';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_ADJUSTED  = 'adjusted';

    protected $fillable = [
        'code',
        'supplier_id',
        'note',
        'status',
        'total_amount',
        'created_by',
        'completed_at',
        'deleted_by',
        'adjusted_by',
        'adjusted_at',
        'adjustment_reason',
        'adjustment_stock_issue_id',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'completed_at' => 'datetime',
        'adjusted_at' => 'datetime',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
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
}
