<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockIssue extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUS_DRAFT  = 'draft';
    public const STATUS_ISSUED = 'issued';
    public const STATUS_ADJUSTED = 'adjusted';

    public const REASON_TYPE_DAMAGE   = 'XUAT_HUY';
    public const REASON_TYPE_STOCKTAKE = 'XUAT_KIEM_KHO';
    public const REASON_TYPE_RETURN_SUPPLIER = 'XUAT_TRA_NCC';
    public const REASON_TYPE_ADJUSTMENT = 'DIEU_CHINH';

    // Chỉ loại lý do này mới cho phép Admin tự chỉnh đơn giá xuất; các loại còn lại luôn chốt theo giá vốn hệ thống.
    public const REASON_TYPES_ALLOWING_PRICE_EDIT = [self::REASON_TYPE_RETURN_SUPPLIER];

    public const REASON_TYPE_LABELS = [
        self::REASON_TYPE_DAMAGE => 'Xuất hủy',
        self::REASON_TYPE_STOCKTAKE => 'Xuất kiểm kho - Cân bằng',
        self::REASON_TYPE_RETURN_SUPPLIER => 'Xuất trả đối tác - Nhà cung cấp',
        self::REASON_TYPE_ADJUSTMENT => 'Điều chỉnh chứng từ sai',
    ];

    protected $fillable = [
        'code',
        'reason',
        'reason_type',
        'note',
        'status',
        'total_amount',
        'created_by',
        'issued_at',
        'adjusted_by',
        'adjusted_at',
        'adjustment_reason',
        'adjustment_goods_receipt_id',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'issued_at'    => 'datetime',
        'adjusted_at'  => 'datetime',
    ];

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

    public function adjustmentGoodsReceipt(): BelongsTo
    {
        return $this->belongsTo(GoodsReceipt::class, 'adjustment_goods_receipt_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(StockIssueItem::class);
    }

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isIssued(): bool
    {
        return $this->status === self::STATUS_ISSUED;
    }

    public function isAdjusted(): bool
    {
        return $this->status === self::STATUS_ADJUSTED;
    }

    public function allowsPriceEdit(): bool
    {
        return in_array($this->reason_type, self::REASON_TYPES_ALLOWING_PRICE_EDIT, true);
    }
}
