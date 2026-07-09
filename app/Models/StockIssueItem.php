<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockIssueItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock_issue_id',
        'product_id',
        'product_variant_id',
        'quantity',
        'cost_price',
        'sale_price',
        'total_cost',
        'total_sale',
    ];

    protected $casts = [
        'quantity'   => 'integer',
        'cost_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'total_sale' => 'decimal:2',
    ];

    public function stockIssue(): BelongsTo
    {
        return $this->belongsTo(StockIssue::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }
}
