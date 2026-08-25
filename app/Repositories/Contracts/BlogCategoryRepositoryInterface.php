<?php

namespace App\Repositories\Contracts;

use App\Models\BlogCategory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface BlogCategoryRepositoryInterface
{
    public function find(int $id): ?BlogCategory;

    public function all(): Collection;
}
