<?php

namespace App\Repositories\Eloquent;

use App\Models\CoreValue;
use App\Repositories\Contracts\CoreValueRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class CoreValueRepository implements CoreValueRepositoryInterface
{
    public function find(int $id): ?CoreValue
    {
        return CoreValue::query()->with(['translations.language'])->find($id);
    }

    public function all(): Collection
    {
        return CoreValue::query()->with(['translations.language'])->get();
    }

    public function create(array $data): CoreValue
    {
        return CoreValue::query()->create($data);
    }

    public function update(CoreValue $coreValue, array $data): CoreValue
    {
        $coreValue->update($data);

        return $coreValue->fresh(['translations.language']);
    }

    public function delete(CoreValue $coreValue): void
    {
        $coreValue->delete();
    }

    /**
     * @param  array<int, int>  $orderedIds
     */
    public function reorder(array $orderedIds): void
    {
        foreach ($orderedIds as $index => $id) {
            CoreValue::query()->whereKey($id)->update(['order' => $index]);
        }
    }
}
