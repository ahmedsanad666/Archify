<?php

namespace App\Repositories\Contracts;

use App\Models\Statistic;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface StatisticRepositoryInterface
{
    public function find(int $id): ?Statistic;

    public function all(): Collection;
}
