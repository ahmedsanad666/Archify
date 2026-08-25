<?php

namespace App\Repositories\Contracts;

use App\Models\CoreValue;
use Illuminate\Database\Eloquent\Collection;

interface CoreValueRepositoryInterface
{
    public function find(int $id): ?CoreValue;

    public function all(): Collection;

    public function create(array $data): CoreValue;

    public function update(CoreValue $coreValue, array $data): CoreValue;

    public function delete(CoreValue $coreValue): void;

    /**
     * @param  array<int, int>  $orderedIds
     */
    public function reorder(array $orderedIds): void;
}
