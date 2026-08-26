<?php

namespace App\Repositories\Eloquent;

use App\Repositories\Contracts\MediaRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaRepository implements MediaRepositoryInterface
{
    /**
     * @param  array{model_type?: string|null, collection_name?: string|null, q?: string|null}  $filters
     */
    public function paginate(array $filters, int $perPage = 24): LengthAwarePaginator
    {
        return $this->filteredQuery($filters)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function find(int $id): ?Media
    {
        return Media::query()->find($id);
    }

    public function delete(Media $media): void
    {
        $media->delete();
    }

    public function distinctModelTypes(): Collection
    {
        return Media::query()
            ->select('model_type')
            ->distinct()
            ->orderBy('model_type')
            ->pluck('model_type')
            ->filter()
            ->values();
    }

    public function distinctCollections(?string $modelType = null): Collection
    {
        $query = Media::query()
            ->select('collection_name')
            ->distinct()
            ->orderBy('collection_name');

        if (filled($modelType)) {
            $query->where('model_type', $modelType);
        }

        return $query
            ->pluck('collection_name')
            ->filter()
            ->values();
    }

    /**
     * @param  array{model_type?: string|null, collection_name?: string|null, q?: string|null}  $filters
     */
    private function filteredQuery(array $filters): Builder
    {
        $query = Media::query();

        if (filled($filters['model_type'] ?? null)) {
            $query->where('model_type', $filters['model_type']);
        }

        if (filled($filters['collection_name'] ?? null)) {
            $query->where('collection_name', $filters['collection_name']);
        }

        $q = trim((string) ($filters['q'] ?? ''));
        if ($q !== '') {
            $query->where(function (Builder $builder) use ($q): void {
                $builder
                    ->where('name', 'like', "%{$q}%")
                    ->orWhere('file_name', 'like', "%{$q}%");
            });
        }

        return $query;
    }
}
