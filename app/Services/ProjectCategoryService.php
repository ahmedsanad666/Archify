<?php

namespace App\Services;

use App\Models\Language;
use App\Models\ProjectCategory;
use App\Models\ProjectCategoryTranslation;
use App\Repositories\Contracts\LanguageRepositoryInterface;
use App\Repositories\Contracts\ProjectCategoryRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProjectCategoryService
{
    public function __construct(
        private readonly ProjectCategoryRepositoryInterface $projectCategoryRepository,
        private readonly LanguageRepositoryInterface $languageRepository,
        private readonly TranslationDispatchService $translationDispatchService,
    ) {}

    public function all(): Collection
    {
        return $this->projectCategoryRepository->all();
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->projectCategoryRepository->paginate($perPage);
    }

    public function find(int $id): ?ProjectCategory
    {
        return $this->projectCategoryRepository->find($id);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): ProjectCategory
    {
        return DB::transaction(function () use ($data) {
            $nextOrder = $data['order']
                ?? (($this->projectCategoryRepository->all()->max('order') ?? -1) + 1);

            $category = $this->projectCategoryRepository->create([
                'order' => (int) $nextOrder,
            ]);

            $this->syncTranslations($category, $data['translations'] ?? []);
            $this->maybeDispatch($category, $data);

            return $category->fresh(['translations.language'])->loadCount('projects');
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(ProjectCategory $category, array $data): ProjectCategory
    {
        return DB::transaction(function () use ($category, $data) {
            if (array_key_exists('order', $data)) {
                $this->projectCategoryRepository->update($category, [
                    'order' => (int) $data['order'],
                ]);
            }

            $this->syncTranslations($category, $data['translations'] ?? []);
            $this->maybeDispatch($category, $data);

            return $category->fresh(['translations.language'])->loadCount('projects');
        });
    }

    public function delete(ProjectCategory $category): void
    {
        $this->projectCategoryRepository->delete($category);
    }

    /**
     * @param  array<int, int>  $orderedIds
     */
    public function reorder(array $orderedIds): void
    {
        DB::transaction(function () use ($orderedIds) {
            $this->projectCategoryRepository->reorder($orderedIds);
        });
    }

    /**
     * @param  array<string, array<string, mixed>>  $translations
     */
    private function syncTranslations(ProjectCategory $category, array $translations): void
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
            ProjectCategoryTranslation::query()
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
    private function maybeDispatch(ProjectCategory $category, array $data): void
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
