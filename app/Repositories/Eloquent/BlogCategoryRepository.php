<?php

namespace App\Repositories\Eloquent;

use App\Models\BlogCategory;
use App\Repositories\Contracts\BlogCategoryRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class BlogCategoryRepository implements BlogCategoryRepositoryInterface
{
    public function find(int $id): ?BlogCategory
    {
        return BlogCategory::query()
            ->with(['translations.language'])
            ->withCount('blogs')
            ->find($id);
    }

    public function all(): Collection
    {
        return BlogCategory::query()
            ->with(['translations.language'])
            ->withCount('blogs')
            ->get();
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return BlogCategory::query()
            ->with(['translations.language'])
            ->withCount('blogs')
            ->paginate($perPage);
    }

    public function create(array $data): BlogCategory
    {
        return BlogCategory::query()->create($data);
    }

    public function update(BlogCategory $category, array $data): BlogCategory
    {
        $category->update($data);

        return $category->fresh(['translations.language'])->loadCount('blogs');
    }

    public function delete(BlogCategory $category): void
    {
        $category->delete();
    }

    /**
     * @param  array<int, int>  $orderedIds
     */
    public function reorder(array $orderedIds): void
    {
        foreach ($orderedIds as $index => $id) {
            BlogCategory::query()->whereKey($id)->update(['order' => $index]);
        }
    }
}
