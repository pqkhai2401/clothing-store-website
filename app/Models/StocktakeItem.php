<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StocktakeItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'stocktake_id',
        'product_variant_id',
        'system_stock',
        'actual_stock',
        'unit_cost',
    ];

    protected $casts = [
        'unit_cost' => 'decimal:2',
    ];

    public function stocktake(): BelongsTo
    {
        return $this->belongsTo(Stocktake::class);
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function diff(): int
    {
        return $this->actual_stock - $this->system_stock;
    }

    public function diffValue(): float
    {
        return $this->diff() * (float) $this->unit_cost;
    }
}
