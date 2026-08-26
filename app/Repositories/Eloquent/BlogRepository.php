<?php

namespace App\Repositories\Eloquent;

use App\Models\Blog;
use App\Repositories\Contracts\BlogRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class BlogRepository implements BlogRepositoryInterface
{
    private const ADMIN_WITH = [
        'translations.language',
        'category.translations.language',
        'media',
    ];

    public function find(int $id): ?Blog
    {
        return Blog::query()->with(self::ADMIN_WITH)->find($id);
    }

    public function all(): Collection
    {
        return Blog::query()->with(self::ADMIN_WITH)->latest()->get();
    }

    public function findBySlug(string $slug, int $languageId): ?Blog
    {
        return Blog::query()
            ->with(self::ADMIN_WITH)
            ->whereHas('translations', function ($query) use ($slug, $languageId) {
                $query->where('slug', $slug)->where('language_id', $languageId);
            })
            ->first();
    }

    public function paginate(?int $categoryId = null, int $perPage = 15): LengthAwarePaginator
    {
        return Blog::query()
            ->with(self::ADMIN_WITH)
            ->when($categoryId, fn ($query) => $query->where('blog_category_id', $categoryId))
            ->latest()
            ->paginate($perPage);
    }

    public function create(array $data): Blog
    {
        return Blog::query()->create($data);
    }

    public function update(Blog $blog, array $data): Blog
    {
        $blog->update($data);

        return $blog->fresh(self::ADMIN_WITH);
    }

    public function delete(Blog $blog): void
    {
        $blog->delete();
    }

    public function sumViews(): int
    {
        return (int) Blog::query()->sum('views_count');
    }

    public function latestForHome(int $limit = 3): Collection
    {
        return Blog::query()
            ->with(self::ADMIN_WITH)
            ->latest()
            ->limit($limit)
            ->get();
    }
}
