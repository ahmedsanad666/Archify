<?php

namespace App\Services;

use App\Models\Concept;
use App\Models\Language;
use App\Repositories\Contracts\ConceptRepositoryInterface;
use App\Repositories\Contracts\LanguageRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ConceptService
{
    public function __construct(
        private readonly ConceptRepositoryInterface $conceptRepository,
        private readonly LanguageRepositoryInterface $languageRepository,
        private readonly TranslationDispatchService $translationDispatchService,
    ) {}

    public function all(): Collection
    {
        return $this->conceptRepository->all();
    }

    public function find(int $id): ?Concept
    {
        return $this->conceptRepository->find($id);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Concept
    {
        return DB::transaction(function () use ($data) {
            $concept = $this->conceptRepository->create([
                'icon' => $data['icon'] ?? null,
            ]);

            $this->syncTranslations($concept, $data['translations'] ?? []);
            $this->maybeDispatch($concept, $data);

            return $this->conceptRepository->find((int) $concept->id);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Concept $concept, array $data): Concept
    {
        return DB::transaction(function () use ($concept, $data) {
            $this->conceptRepository->update($concept, [
                'icon' => $data['icon'] ?? $concept->icon,
            ]);

            $this->syncTranslations($concept, $data['translations'] ?? []);
            $this->maybeDispatch($concept, $data);

            return $this->conceptRepository->find((int) $concept->id);
        });
    }

    public function delete(Concept $concept): void
    {
        $this->conceptRepository->delete($concept);
    }

    /**
     * @param  array<string, array<string, mixed>>  $translations
     */
    private function syncTranslations(Concept $concept, array $translations): void
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

            $concept->translations()->updateOrCreate(
                ['language_id' => $language->id],
                [
                    'title' => $title,
                    'short_description' => $fields['short_description'] ?? null,
                ],
            );
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function maybeDispatch(Concept $concept, array $data): void
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
            $concept->fresh(),
            $sourceLanguage,
            ['title', 'short_description'],
            [],
            force: true,
        );
    }
}
