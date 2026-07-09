<?php

namespace App\Models;

use Database\Factories\RevisionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Revision extends Model
{
    /** @use HasFactory<RevisionFactory> */
    use HasFactory;
    protected $fillable = [
        'page_id',
        'user_id',
        'version',
        'label',
        'type',
        'content',
        'settings',
        'meta_data',
        'diff',
    ];

    protected $casts = [
        'content' => 'array',
        'settings' => 'array',
        'meta_data' => 'array',
        'diff' => 'array',
    ];

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeAutoSave($query)
    {
        return $query->where('type', 'auto_save');
    }

    public function scopeManual($query)
    {
        return $query->where('type', 'manual');
    }

    public function isAutoSave(): bool
    {
        return $this->type === 'auto_save';
    }
}
