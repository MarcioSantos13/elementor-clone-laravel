<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomFont extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'family',
        'weight',
        'style',
        'file_ttf',
        'file_woff',
        'file_woff2',
        'url_ttf',
        'url_woff',
        'url_woff2',
        'font_display',
        'is_global',
    ];

    protected $casts = [
        'is_global' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeGlobal($query)
    {
        return $query->where('is_global', true);
    }

    public function scopeByFamily($query, string $family)
    {
        return $query->where('family', $family);
    }
}
