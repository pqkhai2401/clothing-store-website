<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockIssueLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock_issue_id',
        'user_id',
        'action',
        'message',
    ];

    public function stockIssue(): BelongsTo
    {
        return $this->belongsTo(StockIssue::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
