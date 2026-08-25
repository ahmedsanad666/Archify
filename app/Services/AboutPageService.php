<?php

namespace App\Services;

use App\Models\AboutPage;
use App\Models\Language;
use App\Repositories\Contracts\AboutPageRepositoryInterface;
use App\Repositories\Contracts\LanguageRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class AboutPageService
{
    public function __construct(
        private readonly AboutPageRepositoryInterface $aboutPageRepository,
        private readonly LanguageRepositoryInterface $languageRepository,
        private readonly TranslationDispatchService $translationDispatchService,
    ) {}

    public function getForAdmin(): AboutPage
    {
        $about = $this->aboutPageRepository->getSingleton();

        if (! $about) {
            $about = AboutPage::query()->create([]);
        }

        return $about->load(['translations.language', 'media']);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(array $data): AboutPage
    {
        return DB::transaction(function () use ($data) {
            $about = $this->getForAdmin();

            $this->syncTranslations($about, $data['translations'] ?? []);
            $this->syncMedia($about, $data);

            if ($data['auto_translate'] ?? false) {
                $sourceLanguage = $this->languageRepository->findByCode(
                    $data['source_locale'] ?? 'en',
                );

                if ($sourceLanguage) {
                    $this->translationDispatchService->dispatchForModel(
                        $about->fresh(),
                        $sourceLanguage,
                        [
                            'story_title',
                            'story_description',
                            'vision_title',
                            'vision_description',
                            'mission_title',
                            'mission_description',
                        ],
                        [],
                        force: true,
                    );
                }
            }

            return $about->fresh(['translations.language', 'media']);
        });
    }

    /**
     * @param  array<string, array<string, mixed>>  $translations
     */
    private function syncTranslations(AboutPage $about, array $translations): void
    {
        foreach ($translations as $locale => $fields) {
            $language = $this->languageRepository->findByCode($locale);

            if (! $language instanceof Language) {
                continue;
            }

            $about->translations()->updateOrCreate(
                ['language_id' => $language->id],
                [
                    'story_title' => $fields['story_title'] ?? '',
                    'story_description' => $fields['story_description'] ?? null,
                    'vision_title' => $fields['vision_title'] ?? null,
                    'vision_description' => $fields['vision_description'] ?? null,
                    'mission_title' => $fields['mission_title'] ?? null,
                    'mission_description' => $fields['mission_description'] ?? null,
                ],
            );
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function syncMedia(AboutPage $about, array $data): void
    {
        if (! empty($data['remove_story_image'])) {
            $about->clearMediaCollection('story_image');
        }

        $file = $data['story_image'] ?? null;
        if ($file instanceof UploadedFile) {
            $about->clearMediaCollection('story_image');
            $about->addMedia($file)->toMediaCollection('story_image');
        }
    }
}
