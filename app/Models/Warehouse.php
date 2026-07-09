<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Warehouse extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'city',
        'phone',
        'is_default',
        'status',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'status'     => 'boolean',
    ];

    /**
     * Lấy tên đầy đủ kèm thành phố, ví dụ: "Kho chính - TP. Hồ Chí Minh"
     */
    public function getFullNameAttribute(): string
    {
        if ($this->city) {
            return "{$this->name} - {$this->city}";
        }
        return $this->name;
    }

    /**
     * Lấy kho mặc định của hệ thống.
     */
    public static function getDefault(): ?static
    {
        return static::where('is_default', true)->where('status', true)->first()
            ?? static::where('status', true)->orderBy('id')->first();
    }

    public function goodsReceipts(): HasMany
    {
        return $this->hasMany(GoodsReceipt::class);
    }
}
