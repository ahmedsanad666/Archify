<?php

namespace App\Services;

use App\Models\BlogCategory;
use App\Models\BlogCategoryTranslation;
use App\Models\Language;
use App\Repositories\Contracts\BlogCategoryRepositoryInterface;
use App\Repositories\Contracts\LanguageRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BlogCategoryService
{
    private const DEFAULT_COLOR = '#f9ba7f';

    public function __construct(
        private readonly BlogCategoryRepositoryInterface $blogCategoryRepository,
        private readonly LanguageRepositoryInterface $languageRepository,
        private readonly TranslationDispatchService $translationDispatchService,
    ) {}

    public function all(): Collection
    {
        return $this->blogCategoryRepository->all();
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->blogCategoryRepository->paginate($perPage);
    }

    public function find(int $id): ?BlogCategory
    {
        return $this->blogCategoryRepository->find($id);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): BlogCategory
    {
        return DB::transaction(function () use ($data) {
            $nextOrder = $data['order']
                ?? (($this->blogCategoryRepository->all()->max('order') ?? -1) + 1);

            $category = $this->blogCategoryRepository->create([
                'color' => $data['color'] ?? self::DEFAULT_COLOR,
                'order' => (int) $nextOrder,
            ]);

            $this->syncTranslations($category, $data['translations'] ?? []);
            $this->maybeDispatch($category, $data);

            return $category->fresh(['translations.language'])->loadCount('blogs');
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(BlogCategory $category, array $data): BlogCategory
    {
        return DB::transaction(function () use ($category, $data) {
            $attributes = [];

            if (array_key_exists('order', $data)) {
                $attributes['order'] = (int) $data['order'];
            }

            if (array_key_exists('color', $data) && filled($data['color'])) {
                $attributes['color'] = $data['color'];
            }

            if ($attributes !== []) {
                $this->blogCategoryRepository->update($category, $attributes);
            }

            $this->syncTranslations($category, $data['translations'] ?? []);
            $this->maybeDispatch($category, $data);

            return $category->fresh(['translations.language'])->loadCount('blogs');
        });
    }

    public function delete(BlogCategory $category): void
    {
        $category->loadCount('blogs');

        if (($category->blogs_count ?? 0) > 0) {
            throw ValidationException::withMessages([
                'category' => 'This category has blog posts and cannot be deleted.',
            ]);
        }

        $this->blogCategoryRepository->delete($category);
    }

    /**
     * @param  array<int, int>  $orderedIds
     */
    public function reorder(array $orderedIds): void
    {
        DB::transaction(function () use ($orderedIds) {
            $this->blogCategoryRepository->reorder($orderedIds);
        });
    }

    /**
     * @param  array<string, array<string, mixed>>  $translations
     */
    private function syncTranslations(BlogCategory $category, array $translations): void
    {
        foreach ($translations as $locale => $fields) {
            $language = $this->languageRepository->findByCode($locale);

            if (! $language instanceof Language) {
                continue;
            }

            $name = trim((string) ($fields['name'] ?? ''));
            if ($name === '') {
                continue;
            }

            $slugInput = trim((string) ($fields['slug'] ?? ''));
            $baseSlug = $slugInput !== '' ? Str::slug($slugInput) : Str::slug($name);
            $baseSlug = $baseSlug !== '' ? $baseSlug : 'category';

            $existing = $category->translations()
                ->where('language_id', $language->id)
                ->first();

            $slug = $this->uniqueSlug(
                $baseSlug,
                $language->id,
                $existing?->id,
            );

            $category->translations()->updateOrCreate(
                ['language_id' => $language->id],
                [
                    'name' => $name,
                    'slug' => $slug,
                ],
            );
        }
    }

    private function uniqueSlug(string $base, int $languageId, ?int $ignoreTranslationId = null): string
    {
        $slug = $base;
        $suffix = 2;

        while (
            BlogCategoryTranslation::query()
                ->where('language_id', $languageId)
                ->where('slug', $slug)
                ->when(
                    $ignoreTranslationId,
                    fn ($query) => $query->whereKeyNot($ignoreTranslationId),
                )
                ->exists()
        ) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function maybeDispatch(BlogCategory $category, array $data): void
    {
        if (! ($data['auto_translate'] ?? false)) {
            return;
        }

        $sourceLanguage = $this->languageRepository->findByCode(
            $data['source_locale'] ?? 'en',
        );

        if (! $sourceLanguage) {
            return;
        }

        $this->translationDispatchService->dispatchForModel(
            $category->fresh(),
            $sourceLanguage,
            ['name'],
            ['slug' => 'name'],
            force: true,
        );
    }
}
