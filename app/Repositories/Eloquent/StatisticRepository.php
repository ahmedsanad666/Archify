<?php

namespace App\Repositories\Eloquent;

use App\Models\Statistic;
use App\Repositories\Contracts\StatisticRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class StatisticRepository implements StatisticRepositoryInterface
{
    public function find(int $id): ?Statistic
    {
        return Statistic::query()->with(['translations.language'])->find($id);
    }

    public function all(): Collection
    {
        return Statistic::query()->with(['translations.language'])->get();
    }
}
