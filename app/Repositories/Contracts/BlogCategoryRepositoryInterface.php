<?php

namespace App\Repositories\Contracts;

use App\Models\BlogCategory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface BlogCategoryRepositoryInterface
{
    public function find(int $id): ?BlogCategory;

    public function all(): Collection;

    public function paginate(int $perPage = 15): LengthAwarePaginator;

    public function create(array $data): BlogCategory;

    public function update(BlogCategory $category, array $data): BlogCategory;

    public function delete(BlogCategory $category): void;

    /**
     * @param  array<int, int>  $orderedIds
     */
    public function reorder(array $orderedIds): void;
}
