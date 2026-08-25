<?php

namespace App\Repositories\Eloquent;

use App\Models\ProjectCategory;
use App\Repositories\Contracts\ProjectCategoryRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ProjectCategoryRepository implements ProjectCategoryRepositoryInterface
{
    public function find(int $id): ?ProjectCategory
    {
        return ProjectCategory::query()->find($id);
    }

    public function all(): Collection
    {
        return ProjectCategory::query()->get();
    }
}
