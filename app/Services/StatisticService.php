<?php

namespace App\Services;

use App\Models\Language;
use App\Models\Statistic;
use App\Repositories\Contracts\LanguageRepositoryInterface;
use App\Repositories\Contracts\StatisticRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class StatisticService
{
    public function __construct(
        private readonly StatisticRepositoryInterface $statisticRepository,
        private readonly LanguageRepositoryInterface $languageRepository,
        private readonly TranslationDispatchService $translationDispatchService,
    ) {}

    public function all(): Collection
    {
        return $this->statisticRepository->all();
    }

    public function find(int $id): ?Statistic
    {
        return $this->statisticRepository->find($id);
    }

    /**
     * @param  array<int, int>  $orderedIds
     */
    public function reorder(array $orderedIds): void
    {
        DB::transaction(function () use ($orderedIds): void {
            $this->statisticRepository->reorder($orderedIds);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Statistic
    {
        return DB::transaction(function () use ($data) {
            $nextOrder = (int) ($this->statisticRepository->all()->max('order') + 1);

            $statistic = $this->statisticRepository->create([
                'count' => (int) ($data['count'] ?? 0),
                'order' => $nextOrder,
            ]);

            $this->syncTranslations($statistic, $data['translations'] ?? []);
            $this->maybeDispatch($statistic, $data);

            return $this->statisticRepository->find((int) $statistic->id);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Statistic $statistic, array $data): Statistic
    {
        return DB::transaction(function () use ($statistic, $data) {
            $this->statisticRepository->update($statistic, [
                'count' => (int) ($data['count'] ?? $statistic->count),
            ]);

            $this->syncTranslations($statistic, $data['translations'] ?? []);
            $this->maybeDispatch($statistic, $data);

            return $this->statisticRepository->find((int) $statistic->id);
        });
    }

    public function delete(Statistic $statistic): void
    {
        $this->statisticRepository->delete($statistic);
    }

    /**
     * @param  array<string, array<string, mixed>>  $translations
     */
    private function syncTranslations(Statistic $statistic, array $translations): void
    {
        foreach ($translations as $locale => $fields) {
            $language = $this->languageRepository->findByCode($locale);

            if (! $language instanceof Language) {
                continue;
            }

            $label = trim((string) ($fields['label'] ?? ''));
            if ($label === '') {
                continue;
            }

            $statistic->translations()->updateOrCreate(
                ['language_id' => $language->id],
                ['label' => $label],
            );
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function maybeDispatch(Statistic $statistic, array $data): void
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
            $statistic->fresh(),
            $sourceLanguage,
            ['label'],
            [],
            force: true,
        );
    }
}
