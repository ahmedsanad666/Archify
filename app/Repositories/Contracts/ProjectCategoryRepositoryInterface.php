<?php

namespace App\Repositories\Contracts;

use App\Models\ProjectCategory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface ProjectCategoryRepositoryInterface
{
    public function find(int $id): ?ProjectCategory;

    public function all(): Collection;
}
