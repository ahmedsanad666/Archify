<?php

namespace App\Services;

use App\Models\Language;
use App\Models\Slider;
use App\Repositories\Contracts\LanguageRepositoryInterface;
use App\Repositories\Contracts\SliderRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class SliderService
{
    public function __construct(
        private readonly SliderRepositoryInterface $sliderRepository,
        private readonly LanguageRepositoryInterface $languageRepository,
        private readonly TranslationDispatchService $translationDispatchService,
    ) {}

    public function allActive(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->sliderRepository->allActive();
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->sliderRepository->paginate($perPage);
    }

    public function all(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->sliderRepository->all();
    }

    public function find(int $id): ?Slider
    {
        return $this->sliderRepository->find($id);
    }

    /**
     * @param  array<int, int>  $orderedIds
     */
    public function reorder(array $orderedIds): void
    {
        DB::transaction(function () use ($orderedIds) {
            $this->sliderRepository->reorder($orderedIds);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Slider
    {
        return DB::transaction(function () use ($data) {
            $nextOrder = $data['order'] ?? ($this->sliderRepository->all()->max('order') + 1);

            $slider = $this->sliderRepository->create([
                'order' => (int) $nextOrder,
                'is_active' => (bool) ($data['is_active'] ?? true),
            ]);

            $this->syncTranslations($slider, $data['translations'] ?? []);
            $this->syncMedia($slider, $data);
            $this->maybeDispatch($slider, $data);

            return $slider->fresh(['translations.language', 'media']);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Slider $slider, array $data): Slider
    {
        return DB::transaction(function () use ($slider, $data) {
            $this->sliderRepository->update($slider, [
                'order' => (int) ($data['order'] ?? $slider->order),
                'is_active' => (bool) ($data['is_active'] ?? $slider->is_active),
            ]);

            $this->syncTranslations($slider, $data['translations'] ?? []);
            $this->syncMedia($slider, $data);
            $this->maybeDispatch($slider, $data);

            return $slider->fresh(['translations.language', 'media']);
        });
    }

    public function delete(Slider $slider): void
    {
        $this->sliderRepository->delete($slider);
    }

    /**
     * @param  array<string, array<string, mixed>>  $translations
     */
    private function syncTranslations(Slider $slider, array $translations): void
    {
        foreach ($translations as $locale => $fields) {
            $language = $this->languageRepository->findByCode($locale);

            if (! $language instanceof Language) {
                continue;
            }

            $slider->translations()->updateOrCreate(
                ['language_id' => $language->id],
                [
                    'title' => $fields['title'] ?? '',
                    'description' => $fields['description'] ?? null,
                ],
            );
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function syncMedia(Slider $slider, array $data): void
    {
        if (! empty($data['remove_image'])) {
            $slider->clearMediaCollection('image');
        }

        $file = $data['image'] ?? null;
        if ($file instanceof UploadedFile) {
            $slider->clearMediaCollection('image');
            $slider->addMedia($file)->toMediaCollection('image');
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function maybeDispatch(Slider $slider, array $data): void
    {
        if (! ($data['auto_translate'] ?? false)) {
            return;
        }

        $sourceCode = $data['source_locale'] ?? 'en';
        $sourceLanguage = $this->languageRepository->findByCode($sourceCode);

        if (! $sourceLanguage) {
            return;
        }

        $this->translationDispatchService->dispatchForModel(
            $slider->fresh(),
            $sourceLanguage,
            ['title', 'description'],
            [],
            force: true,
        );
    }
}
