<?php

namespace App\Repositories\Contracts;

use App\Models\CoreValue;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface CoreValueRepositoryInterface
{
    public function find(int $id): ?CoreValue;

    public function all(): Collection;
}
