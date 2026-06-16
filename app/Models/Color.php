<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Color extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
    ];

    /**
     * Get the product variants associated with this color.
     */
    public function productVariants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }
}
