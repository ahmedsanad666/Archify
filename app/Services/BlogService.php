<?php

namespace App\Services;

use App\Models\Blog;
use App\Models\BlogTranslation;
use App\Models\Language;
use App\Repositories\Contracts\BlogRepositoryInterface;
use App\Repositories\Contracts\LanguageRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;

class BlogService
{
    public function __construct(
        private readonly BlogRepositoryInterface $blogRepository,
        private readonly LanguageRepositoryInterface $languageRepository,
        private readonly TranslationDispatchService $translationDispatchService,
    ) {}

    public function paginate(?int $categoryId = null, int $perPage = 15): LengthAwarePaginator
    {
        return $this->blogRepository->paginate($categoryId, $perPage);
    }

    public function find(int $id): ?Blog
    {
        return $this->blogRepository->find($id);
    }

    public function latestForHome(int $limit = 3): \Illuminate\Database\Eloquent\Collection
    {
        return $this->blogRepository->latestForHome($limit);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Blog
    {
        return DB::transaction(function () use ($data) {
            $blog = $this->blogRepository->create([
                'blog_category_id' => (int) $data['blog_category_id'],
                'views_count' => 0,
            ]);

            $this->syncTranslations($blog, $data['translations'] ?? []);
            $this->syncMedia($blog, $data);
            $this->maybeDispatch($blog, $data);

            return $this->blogRepository->find((int) $blog->id);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Blog $blog, array $data): Blog
    {
        return DB::transaction(function () use ($blog, $data) {
            $this->blogRepository->update($blog, [
                'blog_category_id' => (int) ($data['blog_category_id'] ?? $blog->blog_category_id),
            ]);

            $this->syncTranslations($blog, $data['translations'] ?? []);
            $this->syncMedia($blog, $data);
            $this->maybeDispatch($blog, $data);

            return $this->blogRepository->find((int) $blog->id);
        });
    }

    public function delete(Blog $blog): void
    {
        $this->blogRepository->delete($blog);
    }

    /**
     * @param  array<string, array<string, mixed>>  $translations
     */
    private function syncTranslations(Blog $blog, array $translations): void
    {
        foreach ($translations as $locale => $fields) {
            $language = $this->languageRepository->findByCode($locale);

            if (! $language instanceof Language) {
                continue;
            }

            $title = trim((string) ($fields['title'] ?? ''));
            if ($title === '') {
                continue;
            }

            $slugInput = trim((string) ($fields['slug'] ?? ''));
            $baseSlug = $slugInput !== '' ? Str::slug($slugInput) : Str::slug($title);
            $baseSlug = $baseSlug !== '' ? $baseSlug : 'post';

            $existing = $blog->translations()
                ->where('language_id', $language->id)
                ->first();

            $slug = $this->uniqueSlug(
                $baseSlug,
                $language->id,
                $existing?->id,
            );

            $content = $fields['content'] ?? null;

            $blog->translations()->updateOrCreate(
                ['language_id' => $language->id],
                [
                    'title' => $title,
                    'slug' => $slug,
                    'content' => $content,
                    'read_time' => $this->calculateReadTime($content),
                    'meta_title' => $fields['meta_title'] ?? null,
                    'meta_description' => $fields['meta_description'] ?? null,
                    'meta_keywords' => $fields['meta_keywords'] ?? null,
                    'translation_status' => 'manual',
                ],
            );
        }
    }

    private function calculateReadTime(mixed $content): ?int
    {
        $text = trim(strip_tags((string) ($content ?? '')));

        if ($text === '') {
            return null;
        }

        $words = str_word_count($text);

        return max(1, (int) ceil($words / 200));
    }

    private function uniqueSlug(string $base, int $languageId, ?int $ignoreTranslationId = null): string
    {
        $slug = $base;
        $suffix = 2;

        while (
            BlogTranslation::query()
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
    private function syncMedia(Blog $blog, array $data): void
    {
        if (! empty($data['remove_thumbnail'])) {
            $blog->clearMediaCollection('thumbnail');
        }

        if (! empty($data['remove_cover'])) {
            $blog->clearMediaCollection('cover');
        }

        $this->replaceSingleFile($blog, 'thumbnail', $data['thumbnail'] ?? null);
        $this->replaceSingleFile($blog, 'cover', $data['cover'] ?? null);
    }

    private function replaceSingleFile(Blog $blog, string $collection, mixed $file): void
    {
        if (! $file instanceof UploadedFile) {
            return;
        }

        $blog->clearMediaCollection($collection);
        $this->addUploadedMedia($blog, $file, $collection);
    }

    private function addUploadedMedia(Blog $blog, UploadedFile $file, string $collection): void
    {
        try {
            $blog->addMedia($file)->toMediaCollection($collection);
        } catch (FileIsTooBig $exception) {
            $maxMb = (int) floor(config('media-library.max_file_size') / 1024 / 1024);

            throw ValidationException::withMessages([
                $collection => "\"{$file->getClientOriginalName()}\" is too large. Maximum upload size is {$maxMb}MB.",
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function maybeDispatch(Blog $blog, array $data): void
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
            $blog->fresh(),
            $sourceLanguage,
            [
                'title',
                'content',
                'meta_title',
                'meta_description',
                'meta_keywords',
            ],
            ['slug' => 'title'],
            force: true,
        );
    }
}
