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
            ],
            'tr' => [
                'name' => 'Archify',
                'slogan' => 'Mimarlık ve iç mimarlık',
            ],
            'ar' => [
                'name' => 'Archify',
                'slogan' => 'العمارة والتصميم الداخلي',
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
