<?php

namespace App\Repositories\Eloquent;

use App\Models\ProjectCategory;
use App\Repositories\Contracts\ProjectCategoryRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ProjectCategoryRepository implements ProjectCategoryRepositoryInterface
{
    public function find(int $id): ?ProjectCategory
    {
        return ProjectCategory::query()
            ->with(['translations.language'])
            ->withCount('projects')
            ->find($id);
    }

    public function all(): Collection
    {
        return ProjectCategory::query()
            ->with(['translations.language'])
            ->withCount('projects')
            ->get();
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return ProjectCategory::query()
            ->with(['translations.language'])
            ->withCount('projects')
            ->paginate($perPage);
    }

    public function create(array $data): ProjectCategory
    {
        return ProjectCategory::query()->create($data);
    }

    public function update(ProjectCategory $category, array $data): ProjectCategory
    {
        $category->update($data);

        return $category->fresh(['translations.language'])->loadCount('projects');
    }

    public function delete(ProjectCategory $category): void
    {
        $category->delete();
    }

    /**
     * @param  array<int, int>  $orderedIds
     */
    public function reorder(array $orderedIds): void
    {
        foreach ($orderedIds as $index => $id) {
            ProjectCategory::query()->whereKey($id)->update(['order' => $index]);
        }
    }
}
