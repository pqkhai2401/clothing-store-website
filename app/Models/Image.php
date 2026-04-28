<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use App\Models\User;

class Image extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'patient_code',
        'file_name',
        'file_size',
        'mime_type',
        'url',
        'created_by',
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function getImageUrlAttribute()
    {
        return $this->url ? Storage::disk('public')->url($this->url) : null;
    }
}

