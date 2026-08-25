<?php

namespace App\Repositories\Contracts;

use App\Models\Blog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface BlogRepositoryInterface
{
    public function find(int $id): ?Blog;

    public function all(): Collection;

    public function findBySlug(string $slug, int $languageId): ?Blog;

    public function paginate(?int $categoryId = null, int $perPage = 15): LengthAwarePaginator;
}
