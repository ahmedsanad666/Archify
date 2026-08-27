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
     * @param  array{
     *     site_name?: string,
     *     tagline?: string,
     *     slogan?: string,
     *     page_title?: string,
     *     description?: string,
     *     keywords?: string,
     *     og_image?: string|null
     * }  $replacements
     * @return array{title: string, description: string, keywords: string, og_image: string, favicon: string}
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

        $description = array_key_exists('description', $replacements)
            ? trim((string) $replacements['description'])
            : trim((string) ($translation?->meta_description ?? ''));

        $keywords = array_key_exists('keywords', $replacements)
            ? trim((string) $replacements['keywords'])
            : trim((string) ($translation?->meta_keywords ?? ''));

        $siteOgImage = '';
        $siteFavicon = '';
        if ($settings) {
            $siteOgImage = (string) ($settings->getFirstMediaUrl('og_image') ?: '');
            $siteFavicon = (string) ($settings->getFirstMediaUrl('favicon') ?: '');
        }

        $ogImage = array_key_exists('og_image', $replacements)
            ? trim((string) ($replacements['og_image'] ?? ''))
            : $siteOgImage;

        if ($ogImage === '' && $siteOgImage !== '') {
            $ogImage = $siteOgImage;
        }

        return [
            'title' => $title,
            'description' => $description,
            'keywords' => $keywords,
            'og_image' => $this->absoluteUrl($ogImage),
            'favicon' => $this->absoluteUrl($siteFavicon),
        ];
    }

    /**
     * Space-split meta keywords and append category when not already present.
     */
    public function mergeKeywords(?string $metaKeywords, ?string $categoryName): string
    {
        $parts = preg_split('/\s+/', trim((string) $metaKeywords), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $lower = array_map(
            static fn (string $part): string => mb_strtolower($part),
            $parts,
        );

        $category = trim((string) $categoryName);
        if ($category !== '' && ! in_array(mb_strtolower($category), $lower, true)) {
            $parts[] = $category;
        }

        return implode(' ', $parts);
    }

    public function absoluteUrl(?string $url): string
    {
        $url = trim((string) $url);
        if ($url === '') {
            return '';
        }

        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            return $url;
        }

        return url($url);
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
        $collections = [
            'logo',
            'favicon',
            'og_image',
            'banner_about',
            'banner_services',
            'banner_projects',
            'banner_blogs',
            'banner_contact',
        ];

        foreach ($collections as $collection) {
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
