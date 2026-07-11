<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Bộ đếm số thứ tự chứng từ, mỗi (document_type + period_key) là 1 dòng riêng.
 *
 * current_number được tăng ATOMIC qua DB::transaction + lockForUpdate trong
 * App\Services\DocumentSequenceService::nextNumber() — KHÔNG bao giờ tính lại
 * từ MAX(code)/COUNT(*) để tránh race condition khi có nhiều worker/nhiều server.
 */
class DocumentSequence extends Model
{
    protected $fillable = [
        'document_type',
        'prefix',
        'current_number',
        'reset_type',
        'period_key',
    ];

    protected $casts = [
        'current_number' => 'integer',
    ];
}
