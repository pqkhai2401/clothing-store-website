<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Collection extends Model implements \OwenIt\Auditing\Contracts\Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'banner',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    /**
     * The products that belong to this collection.
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'collection_product');
    }
}
