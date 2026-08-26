<?php

namespace App\Repositories\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

interface MediaRepositoryInterface
{
    /**
     * @param  array{model_type?: string|null, collection_name?: string|null, q?: string|null}  $filters
     */
    public function paginate(array $filters, int $perPage = 24): LengthAwarePaginator;

    public function find(int $id): ?Media;

    public function delete(Media $media): void;

    /**
     * @return Collection<int, string>
     */
    public function distinctModelTypes(): Collection;

    /**
     * @return Collection<int, string>
     */
    public function distinctCollections(?string $modelType = null): Collection;
}
