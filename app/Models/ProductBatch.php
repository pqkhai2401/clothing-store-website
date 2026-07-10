<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductBatch extends Model
{
    use HasFactory;

    public const STATUS_ACTIVE   = 'active';   // còn hàng, được xuất
    public const STATUS_DEPLETED = 'depleted'; // đã hết
    public const STATUS_LOCKED   = 'locked';   // khoá lô (hàng lỗi, không cho xuất)
    public const STATUS_EXPIRED  = 'expired';  // hết hạn (ít dùng với quần áo)

    protected $fillable = [
        'batch_code',
        'product_variant_id',
        'goods_receipt_item_id',
        'quantity_import',
        'quantity_remaining',
        'cost_price',
        'manufacture_date',
        'expired_date',
        'received_at',
        'status',
    ];

    protected $casts = [
        'quantity_import'    => 'integer',
        'quantity_remaining' => 'integer',
        'cost_price'         => 'decimal:2',
        'manufacture_date'   => 'date',
        'expired_date'       => 'date',
        'received_at'        => 'datetime',
    ];

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function goodsReceiptItem(): BelongsTo
    {
        return $this->belongsTo(GoodsReceiptItem::class);
    }

    public function movements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE && $this->quantity_remaining > 0;
    }

    /** Lô còn bán được: active và còn tồn. Sắp theo FIFO (nhập trước xuất trước). */
    public function scopeSellable($query)
    {
        return $query->where('status', self::STATUS_ACTIVE)
            ->where('quantity_remaining', '>', 0);
    }

    public function scopeFifo($query)
    {
        return $query->orderBy('received_at')->orderBy('id');
    }
}
