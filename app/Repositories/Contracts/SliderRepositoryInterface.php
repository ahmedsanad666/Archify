<?php

namespace App\Repositories\Contracts;

use App\Models\Slider;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface SliderRepositoryInterface
{
    public function find(int $id): ?Slider;

    public function all(): Collection;

    public function allActive(): Collection;

    public function paginate(int $perPage = 15): LengthAwarePaginator;

    public function create(array $data): Slider;

    public function update(Slider $slider, array $data): Slider;

    public function delete(Slider $slider): void;

    /**
     * @param  array<int, int>  $orderedIds
     */
    public function reorder(array $orderedIds): void;
}
