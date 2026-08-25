<?php

namespace App\Repositories\Eloquent;

use App\Models\Slider;
use App\Repositories\Contracts\SliderRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class SliderRepository implements SliderRepositoryInterface
{
    public function find(int $id): ?Slider
    {
        return Slider::query()->with(['translations.language', 'media'])->find($id);
    }

    public function all(): Collection
    {
        return Slider::query()->with(['translations.language', 'media'])->get();
    }

    public function allActive(): Collection
    {
        return Slider::query()->active()->with(['translations.language', 'media'])->get();
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Slider::query()
            ->with(['translations.language', 'media'])
            ->paginate($perPage);
    }

    public function create(array $data): Slider
    {
        return Slider::query()->create($data);
    }

    public function update(Slider $slider, array $data): Slider
    {
        $slider->update($data);

        return $slider->fresh(['translations.language', 'media']);
    }

    public function delete(Slider $slider): void
    {
        $slider->delete();
    }

    /**
     * @param  array<int, int>  $orderedIds
     */
    public function reorder(array $orderedIds): void
    {
        foreach ($orderedIds as $index => $id) {
            Slider::query()->whereKey($id)->update(['order' => $index]);
        }
    }
}
