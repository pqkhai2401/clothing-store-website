<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_variant_id',
        'product_name',
        'variant_sku',
        'color_name',
        'size_name',
        'unit_price',
        'quantity',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'quantity' => 'integer',
    ];

    /**
     * Get the order that contains this item.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the product variant associated with this order item.
     */
    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    /*
     |--------------------------------------------------------------------------
     | Hiển thị thông tin sản phẩm theo SNAPSHOT lúc đặt hàng (M4).
     | Ưu tiên cột snapshot đã đóng băng; fallback về quan hệ live chỉ để phòng
     | những dòng thiếu snapshot (đơn cũ chưa backfill / dữ liệu bất thường).
     |--------------------------------------------------------------------------
     */
    public function displayName(): string
    {
        return $this->product_name
            ?? $this->productVariant?->product?->name
            ?? 'Sản phẩm đã bị xóa';
    }

    public function displaySku(): ?string
    {
        return $this->variant_sku ?? $this->productVariant?->sku;
    }

    public function displayColor(): ?string
    {
        return $this->color_name ?? $this->productVariant?->color?->name;
    }

    public function displaySize(): ?string
    {
        return $this->size_name ?? $this->productVariant?->size?->name;
    }
}
