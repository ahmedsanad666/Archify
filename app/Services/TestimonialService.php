<?php

namespace App\Services;

use App\Models\Language;
use App\Models\Testimonial;
use App\Repositories\Contracts\LanguageRepositoryInterface;
use App\Repositories\Contracts\TestimonialRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class TestimonialService
{
    public function __construct(
        private readonly TestimonialRepositoryInterface $testimonialRepository,
        private readonly LanguageRepositoryInterface $languageRepository,
        private readonly TranslationDispatchService $translationDispatchService,
    ) {}

    public function all(): Collection
    {
        return $this->testimonialRepository->all();
    }

    public function find(int $id): ?Testimonial
    {
        return $this->testimonialRepository->find($id);
    }

    /**
     * @param  array<int, int>  $orderedIds
     */
    public function reorder(array $orderedIds): void
    {
        DB::transaction(function () use ($orderedIds): void {
            $this->testimonialRepository->reorder($orderedIds);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Testimonial
    {
        return DB::transaction(function () use ($data) {
            $nextOrder = $data['order'] ?? ($this->testimonialRepository->all()->max('order') + 1);

            $testimonial = $this->testimonialRepository->create([
                'client_name' => $data['client_name'],
                'order' => (int) $nextOrder,
            ]);

            $this->syncTranslations($testimonial, $data['translations'] ?? []);
            $this->syncMedia($testimonial, $data);
            $this->maybeDispatch($testimonial, $data);

            return $this->testimonialRepository->find((int) $testimonial->id);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Testimonial $testimonial, array $data): Testimonial
    {
        return DB::transaction(function () use ($testimonial, $data) {
            $this->testimonialRepository->update($testimonial, [
                'client_name' => $data['client_name'] ?? $testimonial->client_name,
                'order' => (int) ($data['order'] ?? $testimonial->order),
            ]);

            $this->syncTranslations($testimonial, $data['translations'] ?? []);
            $this->syncMedia($testimonial, $data);
            $this->maybeDispatch($testimonial, $data);

            return $this->testimonialRepository->find((int) $testimonial->id);
        });
    }

    public function delete(Testimonial $testimonial): void
    {
        $this->testimonialRepository->delete($testimonial);
    }

    /**
     * @param  array<string, array<string, mixed>>  $translations
     */
    private function syncTranslations(Testimonial $testimonial, array $translations): void
    {
        foreach ($translations as $locale => $fields) {
            $language = $this->languageRepository->findByCode($locale);

            if (! $language instanceof Language) {
                continue;
            }

            $quote = trim((string) ($fields['quote'] ?? ''));
            if ($quote === '') {
                continue;
            }

            $testimonial->translations()->updateOrCreate(
                ['language_id' => $language->id],
                ['quote' => $quote],
            );
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function syncMedia(Testimonial $testimonial, array $data): void
    {
        if (! empty($data['remove_avatar'])) {
            $testimonial->clearMediaCollection('avatar');
        }

        $file = $data['avatar'] ?? null;
        if ($file instanceof UploadedFile) {
            $testimonial->clearMediaCollection('avatar');
            $testimonial->addMedia($file)->toMediaCollection('avatar');
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function maybeDispatch(Testimonial $testimonial, array $data): void
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
            $testimonial->fresh(),
            $sourceLanguage,
            ['quote'],
            [],
            force: true,
        );
    }
}
