<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockIssue extends Model
{
    use HasFactory;

    public const STATUS_DRAFT  = 'draft';
    public const STATUS_ISSUED = 'issued';

    protected $fillable = [
        'code',
        'reason',
        'note',
        'status',
        'total_amount',
        'created_by',
        'issued_at',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'issued_at'    => 'datetime',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(StockIssueItem::class);
    }

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isIssued(): bool
    {
        return $this->status === self::STATUS_ISSUED;
    }
}
