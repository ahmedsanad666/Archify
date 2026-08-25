<?php

namespace App\Services;

use App\Models\CoreValue;
use App\Models\Language;
use App\Repositories\Contracts\CoreValueRepositoryInterface;
use App\Repositories\Contracts\LanguageRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class CoreValueService
{
    public function __construct(
        private readonly CoreValueRepositoryInterface $coreValueRepository,
        private readonly LanguageRepositoryInterface $languageRepository,
        private readonly TranslationDispatchService $translationDispatchService,
    ) {}

    public function all(): Collection
    {
        return $this->coreValueRepository->all();
    }

    public function find(int $id): ?CoreValue
    {
        return $this->coreValueRepository->find($id);
    }

    /**
     * @param  array<int, int>  $orderedIds
     */
    public function reorder(array $orderedIds): void
    {
        DB::transaction(function () use ($orderedIds): void {
            $this->coreValueRepository->reorder($orderedIds);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): CoreValue
    {
        return DB::transaction(function () use ($data) {
            $nextOrder = (int) ($this->coreValueRepository->all()->max('order') + 1);

            $coreValue = $this->coreValueRepository->create([
                'icon' => $data['icon'] ?? null,
                'order' => $nextOrder,
            ]);

            $this->syncTranslations($coreValue, $data['translations'] ?? []);
            $this->maybeDispatch($coreValue, $data);

            return $this->coreValueRepository->find((int) $coreValue->id);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(CoreValue $coreValue, array $data): CoreValue
    {
        return DB::transaction(function () use ($coreValue, $data) {
            $this->coreValueRepository->update($coreValue, [
                'icon' => $data['icon'] ?? $coreValue->icon,
            ]);

            $this->syncTranslations($coreValue, $data['translations'] ?? []);
            $this->maybeDispatch($coreValue, $data);

            return $this->coreValueRepository->find((int) $coreValue->id);
        });
    }

    public function delete(CoreValue $coreValue): void
    {
        $this->coreValueRepository->delete($coreValue);
    }

    /**
     * @param  array<string, array<string, mixed>>  $translations
     */
    private function syncTranslations(CoreValue $coreValue, array $translations): void
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

            $coreValue->translations()->updateOrCreate(
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
    private function maybeDispatch(CoreValue $coreValue, array $data): void
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
            $coreValue->fresh(),
            $sourceLanguage,
            ['title', 'short_description'],
            [],
            force: true,
        );
    }
}
