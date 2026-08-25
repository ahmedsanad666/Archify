<?php

namespace Database\Seeders;

use App\Models\Language;
use Illuminate\Database\Seeder;

class LanguageSeeder extends Seeder
{
    /**
     * Seed the application's supported locales.
     */
    public function run(): void
    {
        $languages = [
            [
                'code' => 'en',
                'name' => 'English',
                'direction' => 'ltr',
                'is_default' => true,
                'is_active' => true,
                'order' => 1,
            ],
            [
                'code' => 'tr',
                'name' => 'Türkçe',
                'direction' => 'ltr',
                'is_default' => false,
                'is_active' => true,
                'order' => 2,
            ],
            [
                'code' => 'ar',
                'name' => 'العربية',
                'direction' => 'rtl',
                'is_default' => false,
                'is_active' => true,
                'order' => 3,
            ],
        ];

        foreach ($languages as $language) {
            Language::query()->updateOrCreate(
                ['code' => $language['code']],
                $language
            );
        }
    }
}
