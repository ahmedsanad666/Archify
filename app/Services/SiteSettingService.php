<?php

namespace App\Services;

use App\Models\Language;
use App\Models\SiteSetting;
use App\Repositories\Contracts\LanguageRepositoryInterface;
use App\Repositories\Contracts\SiteSettingRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class SiteSettingService
{
    public function __construct(
        private readonly SiteSettingRepositoryInterface $siteSettingRepository,
        private readonly LanguageRepositoryInterface $languageRepository,
        private readonly TranslationDispatchService $translationDispatchService,
    ) {}

    public function getForAdmin(): SiteSetting
    {
        $settings = $this->siteSettingRepository->getSingleton();

        if (! $settings) {
            $settings = SiteSetting::query()->create([
                'auto_translate_enabled' => false,
            ]);
        }

        return $settings->load(['translations.language', 'media']);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(array $data): SiteSetting
    {
        return DB::transaction(function () use ($data) {
            $settings = $this->getForAdmin();

            $this->siteSettingRepository->update($settings, [
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'] ?? null,
                'whatsapp' => $data['whatsapp'] ?? null,
                'map_lat' => $data['map_lat'] ?? null,
                'map_lng' => $data['map_lng'] ?? null,
                'instagram_url' => $data['instagram_url'] ?? null,
                'youtube_url' => $data['youtube_url'] ?? null,
                'twitter_url' => $data['twitter_url'] ?? null,
                'google_analytics_id' => $data['google_analytics_id'] ?? null,
                'gtm_id' => $data['gtm_id'] ?? null,
                'facebook_pixel_id' => $data['facebook_pixel_id'] ?? null,
                'google_site_verification' => $data['google_site_verification'] ?? null,
                'robots_txt' => $data['robots_txt'] ?? null,
                'auto_translate_enabled' => (bool) ($data['auto_translate_enabled'] ?? false),
            ]);

            $this->syncTranslations($settings, $data['translations'] ?? []);
            $this->syncMedia($settings, $data);

            $sourceCode = $data['source_locale'] ?? 'en';
            $sourceLanguage = $this->languageRepository->findByCode($sourceCode)
                ?? $this->languageRepository->allActive()->firstWhere('is_default', true);

            if ($sourceLanguage && ($data['auto_translate'] ?? false)) {
                $this->translationDispatchService->dispatchForModel(
                    $settings->fresh(),
                    $sourceLanguage,
                    ['name', 'slogan', 'address', 'meta_title', 'meta_description', 'meta_keywords'],
                    [],
                    force: true,
                );
            }

            return $settings->fresh(['translations.language', 'media']);
        });
    }

    /**
     * @param  array<string, array<string, mixed>>  $translations
     */
    private function syncTranslations(SiteSetting $settings, array $translations): void
    {
        foreach ($translations as $locale => $fields) {
            $language = $this->languageRepository->findByCode($locale);

            if (! $language instanceof Language) {
                continue;
            }

            $settings->translations()->updateOrCreate(
                ['language_id' => $language->id],
                [
                    'name' => $fields['name'] ?? '',
                    'slogan' => $fields['slogan'] ?? null,
                    'address' => $fields['address'] ?? null,
                    'meta_title' => $fields['meta_title'] ?? null,
                    'meta_description' => $fields['meta_description'] ?? null,
                    'meta_keywords' => $fields['meta_keywords'] ?? null,
                ],
            );
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function syncMedia(SiteSetting $settings, array $data): void
    {
        foreach (['logo', 'favicon', 'og_image'] as $collection) {
            if (! empty($data["remove_{$collection}"])) {
                $settings->clearMediaCollection($collection);
            }

            $file = $data[$collection] ?? null;
            if ($file instanceof UploadedFile) {
                $settings->clearMediaCollection($collection);
                $settings->addMedia($file)->toMediaCollection($collection);
            }
        }
    }
}
