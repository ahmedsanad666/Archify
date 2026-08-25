<?php

namespace App\Repositories\Eloquent;

use App\Models\Service;
use App\Repositories\Contracts\ServiceRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ServiceRepository implements ServiceRepositoryInterface
{
    public function find(int $id): ?Service
    {
        return Service::query()->with(['translations.language'])->find($id);
    }

    public function all(): Collection
    {
        return Service::query()->with(['translations'])->get();
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Service::query()
            ->with(['translations.language'])
            ->paginate($perPage);
    }

    public function create(array $data): Service
    {
        return Service::query()->create($data);
    }

    public function update(Service $service, array $data): Service
    {
        $service->update($data);

        return $service->fresh(['translations.language']);
    }

    public function delete(Service $service): void
    {
        $service->delete();
    }
}
