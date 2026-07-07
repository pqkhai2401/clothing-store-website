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
        'hex_code',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function getDisplayHexCodeAttribute(): ?string
    {
        if ($this->hex_code) {
            return $this->hex_code;
        }

        return self::defaultHexMap()[$this->name] ?? null;
    }

    public static function defaultHexMap(): array
    {
        return [
            'Đen' => '#000000',
            'Trắng' => '#FFFFFF',
            'Xám' => '#808080',
            'Xanh navy' => '#0F172A',
            'Xanh dương' => '#2563EB',
            'Đỏ' => '#DC2626',
            'Xanh lá' => '#16A34A',
            'Vàng' => '#FACC15',
            'Cam' => '#F97316',
            'Hồng' => '#EC4899',
            'Tím' => '#7C3AED',
            'Nâu' => '#92400E',
            'Be' => '#D6B98C',
            'Xanh rêu' => '#4D7C0F',
        ];
    }

    public function productVariants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }
}
