<?php

namespace App\Repositories\Contracts;

use App\Models\Project;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface ProjectRepositoryInterface
{
    public function find(int $id): ?Project;

    public function all(): Collection;

    public function findBySlug(string $slug, int $languageId): ?Project;

    /**
     * Latest projects for the public home portfolio strip.
     */
    public function latestForHome(int $limit = 6): Collection;

    public function paginate(?int $categoryId = null, int $perPage = 15): LengthAwarePaginator;

    public function create(array $data): Project;

    public function update(Project $project, array $data): Project;

    public function delete(Project $project): void;
}
