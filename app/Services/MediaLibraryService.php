<?php

namespace App\Services;

use App\Repositories\Contracts\MediaRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaLibraryService
{
    public function __construct(
        private readonly MediaRepositoryInterface $mediaRepository,
    ) {}

    /**
     * @param  array{model_type?: string|null, collection_name?: string|null, q?: string|null}  $filters
     */
    public function paginate(array $filters, int $perPage = 24): LengthAwarePaginator
    {
        return $this->mediaRepository->paginate($filters, $perPage);
    }

    public function find(int $id): ?Media
    {
        return $this->mediaRepository->find($id);
    }

    public function delete(Media $media): void
    {
        $this->mediaRepository->delete($media);
    }

    /**
     * @return Collection<int, string>
     */
    public function modelTypes(): Collection
    {
        return $this->mediaRepository->distinctModelTypes();
    }

    /**
     * @return Collection<int, string>
     */
    public function collections(?string $modelType = null): Collection
    {
        return $this->mediaRepository->distinctCollections($modelType);
    }
}
