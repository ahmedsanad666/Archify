<?php

namespace App\Services;

use App\Models\Language;
use App\Models\Service;
use App\Repositories\Contracts\LanguageRepositoryInterface;
use App\Repositories\Contracts\ServiceRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ServiceService
{
    public function __construct(
        private readonly ServiceRepositoryInterface $serviceRepository,
        private readonly LanguageRepositoryInterface $languageRepository,
        private readonly TranslationDispatchService $translationDispatchService,
    ) {}

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->serviceRepository->paginate($perPage);
    }

    public function find(int $id): ?Service
    {
        return $this->serviceRepository->find($id);
    }

    public function forHome(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->serviceRepository->forHome();
    }

    public function all(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->serviceRepository->all();
    }

    /**
     * @param  array<int, int>  $orderedIds
     */
    public function reorder(array $orderedIds): void
    {
        DB::transaction(function () use ($orderedIds): void {
            $this->serviceRepository->reorder($orderedIds);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Service
    {
        return DB::transaction(function () use ($data) {
            $service = $this->serviceRepository->create([
                'icon' => $data['icon'] ?? null,
                'order' => (int) ($data['order'] ?? 0),
                'show_on_home' => (bool) ($data['show_on_home'] ?? false),
            ]);

            $this->syncTranslations($service, $data['translations'] ?? []);
            $this->maybeDispatch($service, $data);

            return $service->fresh(['translations.language']);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Service $service, array $data): Service
    {
        return DB::transaction(function () use ($service, $data) {
            $this->serviceRepository->update($service, [
                'icon' => $data['icon'] ?? $service->icon,
                'order' => (int) ($data['order'] ?? $service->order),
                'show_on_home' => (bool) ($data['show_on_home'] ?? $service->show_on_home),
            ]);

            $this->syncTranslations($service, $data['translations'] ?? []);
            $this->maybeDispatch($service, $data);

            return $service->fresh(['translations.language']);
        });
    }

    public function delete(Service $service): void
    {
        $this->serviceRepository->delete($service);
    }

    /**
     * @param  array<string, array<string, mixed>>  $translations
     */
    private function syncTranslations(Service $service, array $translations): void
    {
        foreach ($translations as $locale => $fields) {
            $language = $this->languageRepository->findByCode($locale);

            if (! $language instanceof Language) {
                continue;
            }

            $items = $fields['included_items'] ?? [];
            if (is_string($items)) {
                $items = array_values(array_filter(array_map('trim', explode("\n", $items))));
            }
            if (! is_array($items)) {
                $items = [];
            }

            $service->translations()->updateOrCreate(
                ['language_id' => $language->id],
                [
                    'title' => $fields['title'] ?? '',
                    'short_description' => $fields['short_description'] ?? null,
                    'included_items' => array_values($items),
                ],
            );
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function maybeDispatch(Service $service, array $data): void
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

        // Translate text fields only; included_items stay manual per locale for now
        $this->translationDispatchService->dispatchForModel(
            $service->fresh(),
            $sourceLanguage,
            ['title', 'short_description'],
            [],
            force: true,
        );
    }
}
