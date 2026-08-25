<?php

namespace App\Repositories\Eloquent;

use App\Models\Lead;
use App\Repositories\Contracts\LeadRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class LeadRepository implements LeadRepositoryInterface
{
    public function find(int $id): ?Lead
    {
        return Lead::query()->find($id);
    }

    public function all(): Collection
    {
        return Lead::query()->get();
    }

    public function paginateByStatus(?string $status = null, int $perPage = 15): LengthAwarePaginator
    {
        return Lead::query()
            ->when($status, fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate($perPage);
    }
}
