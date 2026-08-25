<?php

namespace App\Repositories\Eloquent;

use App\Models\Statistic;
use App\Repositories\Contracts\StatisticRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class StatisticRepository implements StatisticRepositoryInterface
{
    public function find(int $id): ?Statistic
    {
        return Statistic::query()->find($id);
    }

    public function all(): Collection
    {
        return Statistic::query()->get();
    }
}
