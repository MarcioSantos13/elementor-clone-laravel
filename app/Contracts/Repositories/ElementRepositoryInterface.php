<?php

namespace App\Contracts\Repositories;

use App\Models\Element;
use Illuminate\Database\Eloquent\Collection;

interface ElementRepositoryInterface
{
    public function findById(int $id): ?Element;

    public function findByUuid(string $uuid): ?Element;

    public function getByPage(int $pageId): Collection;

    public function getRootByPage(int $pageId): Collection;

    public function getChildren(int $parentId): Collection;

    public function getMaxOrder(int $pageId, ?int $parentId = null): ?int;

    public function create(array $data): Element;

    public function update(Element $element, array $data): Element;

    public function delete(Element $element): bool;

    public function deleteByPage(int $pageId, array $excludeIds = []): int;

    public function searchInPage(int $pageId, string $search, int $limit = 100): Collection;

    public function getByType(string $type): Collection;

    public function searchAcrossAll(string $search, int $limit = 100): Collection;
}
