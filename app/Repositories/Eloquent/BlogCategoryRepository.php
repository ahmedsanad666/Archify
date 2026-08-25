<?php

namespace App\Repositories\Eloquent;

use App\Models\BlogCategory;
use App\Repositories\Contracts\BlogCategoryRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class BlogCategoryRepository implements BlogCategoryRepositoryInterface
{
    public function find(int $id): ?BlogCategory
    {
        return BlogCategory::query()->find($id);
    }

    public function all(): Collection
    {
        return BlogCategory::query()->get();
    }
}
