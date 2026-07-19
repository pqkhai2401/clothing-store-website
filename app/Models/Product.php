<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model implements \OwenIt\Auditing\Contracts\Auditable
{
    use HasFactory, SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'discount_type',
        'discount_value',
        'discount_start_at',
        'discount_end_at',
        'thumbnail',
        'category_id',
        'brand_id',
        'gender',
        'views_count',
        'is_featured',
        'status',
    ];

    protected $casts = [
        'discount_value'    => 'decimal:2',
        'discount_start_at' => 'datetime',
        'discount_end_at'   => 'datetime',
        'is_featured'       => 'boolean',
        'status'            => 'boolean',
        'views_count'       => 'integer',
    ];

    /**
     * Get the category that this product belongs to.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the brand that this product belongs to.
     */
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    /**
     * Get the images for the product.
     */
    public function productImages(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    /**
     * Get the variants for the product.
     */
    public function productVariants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    /**
     * Get the reviews for the product.
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Get the product views (browsing analytics) for this product.
     */
    public function productViews(): HasMany
    {
        return $this->hasMany(ProductView::class);
    }

    /**
     * The tags that belong to the product.
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'product_tags')->withTimestamps();
    }

    /**
     * The collections that contain this product.
     */
    public function collections(): BelongsToMany
    {
        return $this->belongsToMany(Collection::class, 'collection_product');
    }

    public function hasActiveDiscount(): bool
    {
        if (! in_array($this->discount_type, ['percent', 'fixed'], true)) {
            return false;
        }

        if ((float) $this->discount_value <= 0) {
            return false;
        }

        $now = now();

        if ($this->discount_start_at && $this->discount_start_at->gt($now)) {
            return false;
        }

        if ($this->discount_end_at && $this->discount_end_at->lt($now)) {
            return false;
        }

        return true;
    }

    public function discountedPrice(float $price): float
    {
        if (! $this->hasActiveDiscount()) {
            return max(0, $price);
        }

        $discount = $this->discount_type === 'percent'
            ? $price * ((float) $this->discount_value / 100)
            : (float) $this->discount_value;

        return max(0, $price - $discount);
    }

    public function getMinVariantPriceAttribute(): ?float
    {
        $value = $this->productVariants->min('price');

        return $value === null ? null : (float) $value;
    }

    public function getMaxVariantPriceAttribute(): ?float
    {
        $value = $this->productVariants->max('price');

        return $value === null ? null : (float) $value;
    }

    public function getMinFinalPriceAttribute(): ?float
    {
        $min = $this->min_variant_price;

        return $min === null ? null : $this->discountedPrice($min);
    }

    public function getMaxFinalPriceAttribute(): ?float
    {
        $max = $this->max_variant_price;

        return $max === null ? null : $this->discountedPrice($max);
    }

    public function getTotalStockAttribute(): int
    {
        return (int) ($this->product_variants_sum_stock ?? $this->productVariants->sum('stock'));
    }

    /**
     * A product created via Quick Create (from goods-receipts) is missing
     * catalog fields required to be safely published on the storefront.
     */
    public function isIncomplete(): bool
    {
        return blank($this->thumbnail) || blank($this->brand_id) || blank($this->description);
    }

    /**
     * Có biến thể nào chưa có giá bán hợp lệ (giá <= 0) không — dùng để chặn Publish.
     * Khác isIncomplete(): đây là chặn CỨNG, không cho phép bỏ qua kể cả với quyền
     * publish-products, vì để lọt sản phẩm 0đ lên website là rủi ro nghiêm trọng hơn
     * thiếu ảnh/mô tả.
     */
    public function hasUnpricedVariant(): bool
    {
        return $this->productVariants()->where('price', '<=', 0)->exists();
    }
}
