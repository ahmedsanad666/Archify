<?php

namespace App\Repositories\Eloquent;

use App\Models\Project;
use App\Repositories\Contracts\ProjectRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ProjectRepository implements ProjectRepositoryInterface
{
    public function find(int $id): ?Project
    {
        return Project::query()->find($id);
    }

    public function all(): Collection
    {
        return Project::query()->get();
    }

    public function findBySlug(string $slug, int $languageId): ?Project
    {
        return Project::query()
            ->whereHas('translations', function ($query) use ($slug, $languageId) {
                $query->where('slug', $slug)->where('language_id', $languageId);
            })
            ->first();
    }

    public function paginate(?int $categoryId = null, int $perPage = 15): LengthAwarePaginator
    {
        return Project::query()
            ->when($categoryId, fn ($query) => $query->where('project_category_id', $categoryId))
            ->latest()
            ->paginate($perPage);
    }
}
