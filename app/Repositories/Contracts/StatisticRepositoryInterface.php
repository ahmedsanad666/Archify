<?php

namespace App\Repositories\Contracts;

use App\Models\Statistic;
use Illuminate\Database\Eloquent\Collection;

interface StatisticRepositoryInterface
{
    public function find(int $id): ?Statistic;

    public function all(): Collection;

    public function create(array $data): Statistic;

    public function update(Statistic $statistic, array $data): Statistic;

    public function delete(Statistic $statistic): void;

    /**
     * @param  array<int, int>  $orderedIds
     */
    public function reorder(array $orderedIds): void;
}
