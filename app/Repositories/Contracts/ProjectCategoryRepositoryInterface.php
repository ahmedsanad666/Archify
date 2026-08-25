<?php

namespace App\Repositories\Contracts;

use App\Models\ProjectCategory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface ProjectCategoryRepositoryInterface
{
    public function find(int $id): ?ProjectCategory;

    public function all(): Collection;

    public function paginate(int $perPage = 15): LengthAwarePaginator;

    public function create(array $data): ProjectCategory;

    public function update(ProjectCategory $category, array $data): ProjectCategory;

    public function delete(ProjectCategory $category): void;

    /**
     * @param  array<int, int>  $orderedIds
     */
    public function reorder(array $orderedIds): void;
}
