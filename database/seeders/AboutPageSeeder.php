<?php

namespace Database\Seeders;

use App\Models\AboutPage;
use App\Models\Language;
use Illuminate\Database\Seeder;

class AboutPageSeeder extends Seeder
{
    /**
     * Seed the singleton about page row and per-locale translations.
     */
    public function run(): void
    {
        $aboutPage = AboutPage::query()->firstOrCreate([]);

        $translations = [
            'en' => [
                'story_title' => 'Our Story',
                'story_description' => 'We craft thoughtful architecture and interiors with a focus on material, light, and lasting detail.',
                'vision_title' => 'Vision',
                'vision_description' => 'To shape spaces that feel intentional, calm, and enduring.',
                'mission_title' => 'Mission',
                'mission_description' => 'Deliver refined design solutions through close collaboration and craftsmanship.',
            ],
            'tr' => [
                'story_title' => 'Hikayemiz',
                'story_description' => 'Malzeme, ışık ve kalıcı detaylara odaklanarak düşünülmüş mimari ve iç mekanlar tasarlıyoruz.',
                'vision_title' => 'Vizyon',
                'vision_description' => 'İncelikli, sakin ve kalıcı hisseden mekânlar şekillendirmek.',
                'mission_title' => 'Misyon',
                'mission_description' => 'Yakın iş birliği ve ustalıkla rafine tasarım çözümleri sunmak.',
            ],
            'ar' => [
                'story_title' => 'قصتنا',
                'story_description' => 'نصمم عمارة وديكورات داخلية مدروسة مع التركيز على المواد والضوء والتفاصيل الدائمة.',
                'vision_title' => 'الرؤية',
                'vision_description' => 'تشكيل مساحات تبدو مقصودة وهادئة ومستدامة.',
                'mission_title' => 'الرسالة',
                'mission_description' => 'تقديم حلول تصميم راقية من خلال التعاون الوثيق والحرفية.',
            ],
        ];

        foreach ($translations as $code => $fields) {
            $language = Language::query()->where('code', $code)->first();

            if (! $language) {
                continue;
            }

            $aboutPage->translations()->updateOrCreate(
                ['language_id' => $language->id],
                $fields
            );
        }
    }
}
