<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentMethod extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'status',
        'image',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    /**
     * Get the orders that use this payment method.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
