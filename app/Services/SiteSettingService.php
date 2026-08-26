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
                'robots_txt' => SiteSetting::defaultRobotsTxt(),
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
     * Resolve a title template with placeholders (%site_name%, %tagline%, %slogan%, %page_title%).
     *
     * @param  array{site_name?: string, tagline?: string, slogan?: string, page_title?: string}  $replacements
     */
    public function resolveTitleTemplate(string $template, array $replacements = []): string
    {
        $map = [
            '%site_name%' => (string) ($replacements['site_name'] ?? ''),
            '%tagline%' => (string) ($replacements['tagline'] ?? $replacements['slogan'] ?? ''),
            '%slogan%' => (string) ($replacements['slogan'] ?? $replacements['tagline'] ?? ''),
            '%page_title%' => (string) ($replacements['page_title'] ?? ''),
        ];

        $resolved = str_ireplace(array_keys($map), array_values($map), $template);
        $resolved = preg_replace('/%[a-z0-9_]+%/i', '', $resolved) ?? $resolved;
        $resolved = preg_replace('/\s+/', ' ', $resolved) ?? $resolved;
        $resolved = preg_replace('/\s*[\|\-–—]\s*[\|\-–—]+\s*/u', ' | ', $resolved) ?? $resolved;
        $resolved = preg_replace('/^[\s\|\-–—]+|[\s\|\-–—]+$/u', '', $resolved) ?? $resolved;

        return trim($resolved);
    }

    /**
     * Document-level SEO for Blade View Source and Inertia Head mirroring.
     *
     * @param  array{site_name?: string, tagline?: string, slogan?: string, page_title?: string}  $replacements
     * @return array{title: string, description: string, keywords: string}
     */
    public function documentSeo(array $replacements = []): array
    {
        $settings = $this->siteSettingRepository->getSingleton();

        $locale = app()->getLocale();
        $language = $this->languageRepository->findByCode($locale)
            ?? $this->languageRepository->allActive()->firstWhere('is_default', true);

        $translation = null;
        if ($settings && $language) {
            $settings->loadMissing('translations');
            $translation = $settings->translations->firstWhere('language_id', $language->id);
        }

        $siteName = (string) ($replacements['site_name']
            ?? $translation?->name
            ?? config('app.name', 'Archify'));
        $slogan = (string) ($replacements['slogan']
            ?? $replacements['tagline']
            ?? $translation?->slogan
            ?? '');
        $pageTitle = isset($replacements['page_title'])
            ? trim((string) $replacements['page_title'])
            : '';

        $template = trim((string) ($translation?->meta_title ?? ''));
        $merge = [
            'site_name' => $siteName,
            'tagline' => $slogan,
            'slogan' => $slogan,
            'page_title' => $pageTitle,
        ];

        if ($template !== '') {
            if ($pageTitle !== '' && ! str_contains(strtolower($template), '%page_title%')) {
                $title = trim($pageTitle.' - '.$siteName);
            } else {
                $title = $this->resolveTitleTemplate($template, $merge);
            }
        } elseif ($pageTitle !== '') {
            $title = trim($pageTitle.' - '.$siteName);
        } elseif ($slogan !== '') {
            $title = trim($siteName.' | '.$slogan);
        } else {
            $title = $siteName;
        }

        if ($title === '') {
            $title = $siteName;
        }

        return [
            'title' => $title,
            'description' => trim((string) ($translation?->meta_description ?? '')),
            'keywords' => trim((string) ($translation?->meta_keywords ?? '')),
        ];
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
