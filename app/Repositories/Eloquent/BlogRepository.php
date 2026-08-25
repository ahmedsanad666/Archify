<?php

namespace App\Repositories\Eloquent;

use App\Models\Blog;
use App\Repositories\Contracts\BlogRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class BlogRepository implements BlogRepositoryInterface
{
    public function find(int $id): ?Blog
    {
        return Blog::query()->find($id);
    }

    public function all(): Collection
    {
        return Blog::query()->get();
    }

    public function findBySlug(string $slug, int $languageId): ?Blog
    {
        return Blog::query()
            ->whereHas('translations', function ($query) use ($slug, $languageId) {
                $query->where('slug', $slug)->where('language_id', $languageId);
            })
            ->first();
    }

    public function paginate(?int $categoryId = null, int $perPage = 15): LengthAwarePaginator
    {
        return Blog::query()
            ->when($categoryId, fn ($query) => $query->where('blog_category_id', $categoryId))
            ->latest()
            ->paginate($perPage);
    }
}
