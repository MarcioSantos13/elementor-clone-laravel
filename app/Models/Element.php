<?php

namespace App\Models;

use Database\Factories\ElementFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Element extends Model
{
    /** @use HasFactory<ElementFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'page_id',
        'parent_id',
        'uuid',
        'type',
        'name',
        'order',
        'settings',
        'content',
        'styles',
        'responsive_settings',
        'animation',
        'effects',
        'column_size',
        'css_classes',
        'css_id',
    ];

    protected $casts = [
        'uuid' => 'string',
        'settings' => 'array',
        'content' => 'array',
        'styles' => 'array',
        'responsive_settings' => 'array',
        'animation' => 'array',
        'effects' => 'array',
        'css_classes' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (Element $element) {
            if (empty($element->uuid)) {
                $element->uuid = (string) \Illuminate\Support\Str::uuid();
            }
        });
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Element::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Element::class, 'parent_id')->orderBy('order');
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    public function isContainer(): bool
    {
        return $this->children()->exists();
    }

    public function getDepth(): int
    {
        $depth = 0;
        $parent = $this->parent;

        while ($parent) {
            $depth++;
            $parent = $parent->parent;
        }

        return $depth;
    }
}
