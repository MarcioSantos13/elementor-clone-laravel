<?php

namespace App\Contracts\Repositories;

use App\Models\Page;
use Illuminate\Pagination\LengthAwarePaginator;

interface PageRepositoryInterface
{
    public function findById(int $id): ?Page;

    public function findBySlug(string $slug): ?Page;

    public function getPublished(int $perPage = 20): LengthAwarePaginator;

    public function getByUser(int $userId, int $perPage = 20): LengthAwarePaginator;

    public function getAllPaginated(int $perPage = 20): LengthAwarePaginator;

    public function create(array $data): Page;

    public function update(Page $page, array $data): Page;

    public function delete(Page $page): bool;

    public function slugExists(string $slug, ?int $excludeId = null): bool;

    public function getPublishedPagesForSelect(): \Illuminate\Support\Collection;
}
