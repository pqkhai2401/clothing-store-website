<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'site_name',
        'logo_path',
        'address',
        'hotline',
        'email',
        'facebook_url',
        'facebook_enabled',
        'instagram_url',
        'instagram_enabled',
        'zalo_url',
        'zalo_enabled',
    ];

    protected $casts = [
        'facebook_enabled' => 'boolean',
        'instagram_enabled' => 'boolean',
        'zalo_enabled' => 'boolean',
    ];

    protected static ?self $cached = null;

    public static function current(): self
    {
        if (static::$cached !== null) {
            return static::$cached;
        }

        return static::$cached = static::first() ?? static::create([]);
    }

    public function getLogoUrlAttribute(): ?string
    {
        return $this->logo_path ? asset($this->logo_path) : null;
    }

    protected static function booted(): void
    {
        static::saved(fn () => static::$cached = null);
    }
}
