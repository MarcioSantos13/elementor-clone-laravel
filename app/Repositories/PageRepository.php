<?php

namespace App\Repositories;

use App\Contracts\Repositories\PageRepositoryInterface;
use App\Models\Page;
use Illuminate\Pagination\LengthAwarePaginator;

class PageRepository implements PageRepositoryInterface
{
    public function findById(int $id): ?Page
    {
        return Page::find($id);
    }

    public function findBySlug(string $slug): ?Page
    {
        return Page::where('slug', $slug)->first();
    }

    public function getPublished(int $perPage = 20): LengthAwarePaginator
    {
        return Page::published()->latest()->paginate($perPage);
    }

    public function getByUser(int $userId, int $perPage = 20): LengthAwarePaginator
    {
        return Page::where('user_id', $userId)->latest()->paginate($perPage);
    }

    public function getAllPaginated(int $perPage = 20): LengthAwarePaginator
    {
        return Page::latest()->paginate($perPage);
    }

    public function create(array $data): Page
    {
        $page = new Page();
        $page->fill($data);
        $page->user_id = auth()->id();
        $page->status = $data['status'] ?? 'draft';
        $page->save();

        return $page;
    }

    public function update(Page $page, array $data): Page
    {
        $page->fill($data);
        $page->save();

        return $page;
    }

    public function delete(Page $page): bool
    {
        return $page->delete();
    }

    public function slugExists(string $slug, ?int $excludeId = null): bool
    {
        return Page::withTrashed()
            ->where('slug', $slug)
            ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
            ->exists();
    }

    public function getPublishedPagesForSelect(): \Illuminate\Support\Collection
    {
        return Page::published()->select('id', 'title', 'slug')->orderBy('title')->get();
    }
}
