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

    public function create(array $data): Statistic
    {
        return Statistic::query()->create($data);
    }

    public function update(Statistic $statistic, array $data): Statistic
    {
        $statistic->update($data);

        return $statistic->fresh(['translations.language']);
    }

    public function delete(Statistic $statistic): void
    {
        $statistic->delete();
    }

    /**
     * @param  array<int, int>  $orderedIds
     */
    public function reorder(array $orderedIds): void
    {
        foreach ($orderedIds as $index => $id) {
            Statistic::query()->whereKey($id)->update(['order' => $index]);
        }
    }
}
