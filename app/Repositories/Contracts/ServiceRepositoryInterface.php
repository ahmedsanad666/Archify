<?php

namespace App\Repositories\Contracts;

use App\Models\Service;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface ServiceRepositoryInterface
{
    public function find(int $id): ?Service;

    public function all(): Collection;

    public function forHome(): Collection;

    public function paginate(int $perPage = 15): LengthAwarePaginator;

    public function create(array $data): Service;

    public function update(Service $service, array $data): Service;

    public function delete(Service $service): void;

    /**
     * @param  array<int, int>  $orderedIds
     */
    public function reorder(array $orderedIds): void;
}
