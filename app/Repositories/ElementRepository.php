<?php

namespace App\Repositories;

use App\Contracts\Repositories\ElementRepositoryInterface;
use App\Models\Element;
use Illuminate\Database\Eloquent\Collection;

class ElementRepository implements ElementRepositoryInterface
{
    public function findById(int $id): ?Element
    {
        return Element::find($id);
    }

    public function findByUuid(string $uuid): ?Element
    {
        return Element::where('uuid', $uuid)->first();
    }

    public function getByPage(int $pageId): Collection
    {
        return Element::where('page_id', $pageId)->orderBy('order')->get();
    }

    public function getRootByPage(int $pageId): Collection
    {
        return Element::where('page_id', $pageId)->whereNull('parent_id')->orderBy('order')->get();
    }

    public function getChildren(int $parentId): Collection
    {
        return Element::where('parent_id', $parentId)->orderBy('order')->get();
    }

    public function getMaxOrder(int $pageId, ?int $parentId = null): ?int
    {
        return Element::where('page_id', $pageId)
            ->when($parentId, fn($q) => $q->where('parent_id', $parentId))
            ->when(is_null($parentId), fn($q) => $q->whereNull('parent_id'))
            ->max('order');
    }

    public function create(array $data): Element
    {
        $element = new Element();
        $element->fill($data);
        $element->save();

        return $element;
    }

    public function update(Element $element, array $data): Element
    {
        $element->fill($data);
        $element->save();

        return $element;
    }

    public function delete(Element $element): bool
    {
        return $element->delete();
    }

    public function deleteByPage(int $pageId, array $excludeIds = []): int
    {
        return Element::where('page_id', $pageId)
            ->whereNotIn('id', $excludeIds)
            ->delete();
    }

    public function searchInPage(int $pageId, string $search, int $limit = 100): Collection
    {
        return Element::where('page_id', $pageId)
            ->where(function ($q) use ($search) {
                $q->where('settings', 'LIKE', "%{$search}%")
                    ->orWhere('content', 'LIKE', "%{$search}%");
            })
            ->limit($limit)
            ->get();
    }

    public function getByType(string $type): Collection
    {
        return Element::byType($type)->get();
    }

    public function searchAcrossAll(string $search, int $limit = 100): Collection
    {
        return Element::where(function ($q) use ($search) {
            $q->where('settings', 'LIKE', "%{$search}%")
                ->orWhere('content', 'LIKE', "%{$search}%");
        })
            ->limit($limit)
            ->get();
    }
}
