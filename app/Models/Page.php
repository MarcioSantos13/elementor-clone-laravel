<?php

namespace App\Models;

use Database\Factories\PageFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Page extends Model
{
    /** @use HasFactory<PageFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'status',
        'content',
        'settings',
        'meta_data',
        'template',
    ];

    protected $casts = [
        'content' => 'array',
        'settings' => 'array',
        'meta_data' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function elements(): HasMany
    {
        return $this->hasMany(Element::class)->whereNull('parent_id')->orderBy('order');
    }

    public function allElements(): HasMany
    {
        return $this->hasMany(Element::class)->orderBy('order');
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(Revision::class)->orderBy('created_at', 'desc');
    }

    public function formSubmissions(): HasMany
    {
        return $this->hasMany(FormSubmission::class)->orderBy('created_at', 'desc');
    }

    public function latestRevision()
    {
        return $this->hasOne(Revision::class)->latestOfMany();
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeBySlug($query, string $slug)
    {
        return $query->where('slug', $slug);
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function getUrl(): string
    {
        return route('page-builder.render', $this->id);
    }

    public function getEditorUrl(): string
    {
        return route('page-builder.editor', $this->id);
    }
}
