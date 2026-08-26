<?php

namespace Database\Seeders;

use App\Models\Language;
use App\Models\SiteSetting;
use Illuminate\Database\Seeder;

class SiteSettingSeeder extends Seeder
{
    /**
     * Seed the singleton site settings row and per-locale translations.
     */
    public function run(): void
    {
        $setting = SiteSetting::query()->firstOrCreate(
            [],
            [
                'email' => 'hello@archify.com',
                'auto_translate_enabled' => false,
                'robots_txt' => SiteSetting::defaultRobotsTxt(),
            ]
        );

        if (blank($setting->robots_txt)) {
            $setting->update([
                'robots_txt' => SiteSetting::defaultRobotsTxt(),
            ]);
        }

        $translations = [
            'en' => [
                'name' => 'Archify',
                'slogan' => 'Architecture & interior design',
                'meta_title' => '%site_name% | %slogan%',
                'meta_description' => 'Archify crafts thoughtful architecture and interiors with a focus on material, light, and lasting detail.',
                'meta_keywords' => 'architecture, interior design, Archify, residential, hospitality',
            ],
            'tr' => [
                'name' => 'Archify',
                'slogan' => 'Mimarlık ve iç mimarlık',
                'meta_title' => '%site_name% | %slogan%',
                'meta_description' => 'Archify, malzeme, ışık ve kalıcı detaya odaklanan mimari ve iç mekânlar tasarlar.',
                'meta_keywords' => 'mimarlık, iç mimarlık, Archify, konut, otelcilik',
            ],
            'ar' => [
                'name' => 'Archify',
                'slogan' => 'العمارة والتصميم الداخلي',
                'meta_title' => '%site_name% | %slogan%',
                'meta_description' => 'تصمم Archify عمارة وديكورات داخلية مدروسة مع التركيز على المواد والضوء والتفاصيل الدائمة.',
                'meta_keywords' => 'عمارة, تصميم داخلي, Archify, سكني, ضيافة',
            ],
        ];

        foreach ($translations as $code => $fields) {
            $language = Language::query()->where('code', $code)->first();

            if (! $language) {
                continue;
            }

            $setting->translations()->updateOrCreate(
                ['language_id' => $language->id],
                $fields
            );
        }
    }
}
