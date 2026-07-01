<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Size extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'sort_weight',
        'status',
    ];

    protected $casts = [
        'sort_weight' => 'integer',
        'status' => 'integer',
    ];

    /**
     * Get the product variants associated with this size.
     */
    public function productVariants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }
}
