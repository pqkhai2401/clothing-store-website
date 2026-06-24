<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'discount',
        'thumbnail',
        'category_id',
        'brand_id',
        'gender',
        'views_count',
        'is_featured',
        'status',
    ];

    protected $casts = [
        'price'       => 'decimal:2',
        'discount'    => 'integer',
        'is_featured' => 'boolean',
        'status'      => 'boolean',
        'views_count' => 'integer',
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

    public function getFinalPriceAttribute()
    {
        return $this->price *
            (100 - $this->discount) / 100;
    }
}
