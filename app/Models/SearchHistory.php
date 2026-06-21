<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SearchHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'keyword',
    ];

    /**
     * Get the user associated with this search history.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
