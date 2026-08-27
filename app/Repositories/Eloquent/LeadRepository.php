<?php

namespace App\Repositories\Eloquent;

use App\Models\Lead;
use App\Repositories\Contracts\LeadRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

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
            ->with(['service.translations.language', 'language'])
            ->when($status, fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate($perPage);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Lead
    {
        return Lead::query()->create($data);
    }

    public function update(Lead $lead, array $data): Lead
    {
        $lead->update($data);

        return $lead->fresh(['service.translations.language', 'language']);
    }

    public function countCreatedBetween(Carbon $from, Carbon $to): int
    {
        return Lead::query()
            ->whereBetween('created_at', [$from, $to])
            ->count();
    }

    public function countPending(): int
    {
        return Lead::query()->pending()->count();
    }

    public function latest(int $limit = 5): Collection
    {
        return Lead::query()
            ->with(['service.translations.language'])
            ->latest()
            ->limit($limit)
            ->get();
    }
}
