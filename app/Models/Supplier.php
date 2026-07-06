<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'address',
        'note',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function goodsReceipts(): HasMany
    {
        return $this->hasMany(GoodsReceipt::class);
    }
}
