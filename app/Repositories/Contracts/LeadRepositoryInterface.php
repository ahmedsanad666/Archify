<?php

namespace App\Repositories\Contracts;

use App\Models\Lead;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

interface LeadRepositoryInterface
{
    public function find(int $id): ?Lead;

    public function all(): Collection;

    public function paginateByStatus(?string $status = null, int $perPage = 15): LengthAwarePaginator;

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Lead;

    public function update(Lead $lead, array $data): Lead;

    public function countCreatedBetween(Carbon $from, Carbon $to): int;

    public function countPending(): int;

    public function latest(int $limit = 5): Collection;
}
