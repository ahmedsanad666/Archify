<?php

namespace App\Repositories\Contracts;

use App\Models\Lead;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface LeadRepositoryInterface
{
    public function find(int $id): ?Lead;

    public function all(): Collection;

    public function paginateByStatus(?string $status = null, int $perPage = 15): LengthAwarePaginator;
}
