<?php

namespace App\Services;

use App\Models\Language;
use App\Models\Project;
use App\Models\ProjectTranslation;
use App\Repositories\Contracts\LanguageRepositoryInterface;
use App\Repositories\Contracts\ProjectRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ProjectService
{
    public function __construct(
        private readonly ProjectRepositoryInterface $projectRepository,
        private readonly LanguageRepositoryInterface $languageRepository,
        private readonly TranslationDispatchService $translationDispatchService,
    ) {}

    public function paginate(?int $categoryId = null, int $perPage = 15): LengthAwarePaginator
    {
        return $this->projectRepository->paginate($categoryId, $perPage);
    }

    public function find(int $id): ?Project
    {
        return $this->projectRepository->find($id);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Project
    {
        return DB::transaction(function () use ($data) {
            $project = $this->projectRepository->create([
                'project_category_id' => (int) $data['project_category_id'],
                'client_name' => $data['client_name'],
                'location' => $data['location'],
                'year' => (int) $data['year'],
                'video_url' => $data['video_url'] ?? null,
            ]);

            $this->syncTranslations($project, $data['translations'] ?? []);
            $this->syncConcepts($project, $data['concept_ids'] ?? null);
            $this->syncMedia($project, $data);
            $this->maybeDispatch($project, $data);

            return $this->projectRepository->find((int) $project->id);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Project $project, array $data): Project
    {
        return DB::transaction(function () use ($project, $data) {
            $this->projectRepository->update($project, [
                'project_category_id' => (int) ($data['project_category_id'] ?? $project->project_category_id),
                'client_name' => $data['client_name'] ?? $project->client_name,
                'location' => $data['location'] ?? $project->location,
                'year' => (int) ($data['year'] ?? $project->year),
                'video_url' => array_key_exists('video_url', $data)
                    ? $data['video_url']
                    : $project->video_url,
            ]);

            $this->syncTranslations($project, $data['translations'] ?? []);
            $this->syncConcepts($project, $data['concept_ids'] ?? null);
            $this->syncMedia($project, $data);
            $this->maybeDispatch($project, $data);

            return $this->projectRepository->find((int) $project->id);
        });
    }

    public function delete(Project $project): void
    {
        $this->projectRepository->delete($project);
    }

    /**
     * @param  array<string, array<string, mixed>>  $translations
     */
    private function syncTranslations(Project $project, array $translations): void
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
            $baseSlug = $baseSlug !== '' ? $baseSlug : 'project';

            $existing = $project->translations()
                ->where('language_id', $language->id)
                ->first();

            $slug = $this->uniqueSlug(
                $baseSlug,
                $language->id,
                $existing?->id,
            );

            $project->translations()->updateOrCreate(
                ['language_id' => $language->id],
                [
                    'name' => $name,
                    'slug' => $slug,
                    'short_description' => $fields['short_description'] ?? null,
                    'description' => $fields['description'] ?? null,
                    'meta_title' => $fields['meta_title'] ?? null,
                    'meta_description' => $fields['meta_description'] ?? null,
                    'meta_keywords' => $fields['meta_keywords'] ?? null,
                    'translation_status' => 'manual',
                ],
            );
        }
    }

    private function uniqueSlug(string $base, int $languageId, ?int $ignoreTranslationId = null): string
    {
        $slug = $base;
        $suffix = 2;

        while (
            ProjectTranslation::query()
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
     * @param  array<int, int>|null  $conceptIds
     */
    private function syncConcepts(Project $project, ?array $conceptIds): void
    {
        if ($conceptIds === null) {
            return;
        }

        $project->concepts()->sync(
            collect($conceptIds)->map(fn ($id) => (int) $id)->unique()->values()->all(),
        );
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function syncMedia(Project $project, array $data): void
    {
        if (! empty($data['remove_thumbnail'])) {
            $project->clearMediaCollection('thumbnail');
        }

        if (! empty($data['remove_preview_video'])) {
            $project->clearMediaCollection('preview_video');
        }

        $this->replaceSingleFile($project, 'thumbnail', $data['thumbnail'] ?? null);
        $this->replaceSingleFile($project, 'preview_video', $data['preview_video'] ?? null);

        $this->appendGalleryFiles($project, 'images_2d', $data['images_2d'] ?? []);
        $this->appendGalleryFiles($project, 'images_3d', $data['images_3d'] ?? []);
        $this->appendGalleryFiles($project, 'images_outdoor', $data['images_outdoor'] ?? []);

        $removeIds = collect($data['remove_media_ids'] ?? [])
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values()
            ->all();

        if ($removeIds === []) {
            return;
        }

        $project->media()
            ->whereIn('id', $removeIds)
            ->get()
            ->each(fn (Media $media) => $media->delete());
    }

    private function replaceSingleFile(Project $project, string $collection, mixed $file): void
    {
        if (! $file instanceof UploadedFile) {
            return;
        }

        $project->clearMediaCollection($collection);
        $this->addUploadedMedia($project, $file, $collection);
    }

    /**
     * @param  array<int, mixed>  $files
     */
    private function appendGalleryFiles(Project $project, string $collection, array $files): void
    {
        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $this->addUploadedMedia($project, $file, $collection);
            }
        }
    }

    private function addUploadedMedia(Project $project, UploadedFile $file, string $collection): void
    {
        try {
            $project->addMedia($file)->toMediaCollection($collection);
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
    private function maybeDispatch(Project $project, array $data): void
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
            $project->fresh(),
            $sourceLanguage,
            [
                'name',
                'short_description',
                'description',
                'meta_title',
                'meta_description',
                'meta_keywords',
            ],
            ['slug' => 'name'],
            force: true,
        );
    }
}
