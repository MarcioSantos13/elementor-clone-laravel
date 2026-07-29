<?php

namespace App\Models;

use Database\Factories\GlobalWidgetFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GlobalWidget extends Model
{
    /** @use HasFactory<GlobalWidgetFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'title',
        'type',
        'settings',
        'content',
        'styles',
        'status',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'settings' => 'array',
            'content' => 'array',
            'styles' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
