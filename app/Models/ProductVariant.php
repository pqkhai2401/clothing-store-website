<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'color_id',
        'size_id',
        'stock',
        'image',
        'sku',
        'cost_price',
        'price',
        'status',
    ];

    protected $casts = [
        'stock'      => 'integer',
        'cost_price' => 'decimal:2',
        'price'      => 'decimal:2',
    ];

    /**
     * Get the product associated with this variant.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }

    /**
     * Get the color associated with this variant.
     */
    public function color(): BelongsTo
    {
        return $this->belongsTo(Color::class);
    }

    /**
     * Get the size associated with this variant.
     */
    public function size(): BelongsTo
    {
        return $this->belongsTo(Size::class);
    }

    /**
     * Get the cart items referencing this variant.
     */
    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Get the order items referencing this variant.
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Các lô hàng (batch/lot) của biến thể này — nguồn sự thật về tồn & giá vốn.
     */
    public function batches(): HasMany
    {
        return $this->hasMany(ProductBatch::class);
    }

    public function getFinalPriceAttribute(): float
    {
        return $this->product
            ? $this->product->discountedPrice((float) $this->price)
            : (float) $this->price;
    }
}
