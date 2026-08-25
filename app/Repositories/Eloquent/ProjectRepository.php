<?php

namespace App\Repositories\Eloquent;

use App\Models\Project;
use App\Repositories\Contracts\ProjectRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ProjectRepository implements ProjectRepositoryInterface
{
    private const ADMIN_WITH = [
        'translations.language',
        'category.translations.language',
        'concepts.translations.language',
        'media',
    ];

    public function find(int $id): ?Project
    {
        return Project::query()->with(self::ADMIN_WITH)->find($id);
    }

    public function all(): Collection
    {
        return Project::query()->with(self::ADMIN_WITH)->latest()->get();
    }

    public function findBySlug(string $slug, int $languageId): ?Project
    {
        return Project::query()
            ->with(self::ADMIN_WITH)
            ->whereHas('translations', function ($query) use ($slug, $languageId) {
                $query->where('slug', $slug)->where('language_id', $languageId);
            })
            ->first();
    }

    public function latestForHome(int $limit = 6): Collection
    {
        return Project::query()
            ->with([
                'translations.language',
                'category.translations.language',
                'media',
            ])
            ->latest()
            ->limit($limit)
            ->get();
    }

    public function paginate(?int $categoryId = null, int $perPage = 15): LengthAwarePaginator
    {
        return Project::query()
            ->with(self::ADMIN_WITH)
            ->when($categoryId, fn ($query) => $query->where('project_category_id', $categoryId))
            ->latest()
            ->paginate($perPage);
    }

    public function create(array $data): Project
    {
        return Project::query()->create($data);
    }

    public function update(Project $project, array $data): Project
    {
        $project->update($data);

        return $project->fresh(self::ADMIN_WITH);
    }

    public function delete(Project $project): void
    {
        $project->delete();
    }
}
