<?php

namespace App\Repositories\Contracts;

use App\Models\Service;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

interface ServiceRepositoryInterface
{
    public function find(int $id): ?Service;

    public function all(): Collection;

    public function forHome(): Collection;

    public function forHomeLimited(int $limit = 3): Collection;

    public function paginate(int $perPage = 15): LengthAwarePaginator;

    public function create(array $data): Service;

    public function update(Service $service, array $data): Service;

    public function delete(Service $service): void;

    /**
     * @param  array<int, int>  $orderedIds
     */
    public function reorder(array $orderedIds): void;

    public function count(): int;

    public function countCreatedBetween(Carbon $from, Carbon $to): int;
}
