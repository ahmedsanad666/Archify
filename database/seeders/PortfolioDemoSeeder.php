<?php

namespace Database\Seeders;

use App\Models\Concept;
use App\Models\Language;
use App\Models\Project;
use App\Models\ProjectCategory;
use App\Models\ProjectCategoryTranslation;
use App\Models\ProjectTranslation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class PortfolioDemoSeeder extends Seeder
{
    /**
     * Seed demo categories, concepts, and projects with real Unsplash images.
     */
    public function run(): void
    {
        $languages = Language::query()->whereIn('code', ['en', 'tr', 'ar'])->get()->keyBy('code');

        if ($languages->count() < 3) {
            $this->command?->warn('PortfolioDemoSeeder skipped: en/tr/ar languages missing. Run LanguageSeeder first.');

            return;
        }

        $categories = $this->seedCategories($languages);
        $concepts = $this->seedConcepts($languages);
        $this->seedProjects($languages, $categories, $concepts);

        $this->command?->info('Portfolio demo data seeded (5 categories, 5 concepts, 10 projects).');
    }

    /**
     * @param  \Illuminate\Support\Collection<string, Language>  $languages
     * @return array<int, ProjectCategory>
     */
    private function seedCategories($languages): array
    {
        $items = [
            [
                'order' => 0,
                'en' => ['name' => 'Residential', 'slug' => 'residential'],
                'tr' => ['name' => 'Konut', 'slug' => 'konut'],
                'ar' => ['name' => 'سكني', 'slug' => 'residential-ar'],
            ],
            [
                'order' => 1,
                'en' => ['name' => 'Commercial', 'slug' => 'commercial'],
                'tr' => ['name' => 'Ticari', 'slug' => 'ticari'],
                'ar' => ['name' => 'تجاري', 'slug' => 'commercial-ar'],
            ],
            [
                'order' => 2,
                'en' => ['name' => 'Hospitality', 'slug' => 'hospitality'],
                'tr' => ['name' => 'Otelcilik', 'slug' => 'otelcilik'],
                'ar' => ['name' => 'ضيافة', 'slug' => 'hospitality-ar'],
            ],
            [
                'order' => 3,
                'en' => ['name' => 'Landscape', 'slug' => 'landscape'],
                'tr' => ['name' => 'Peyzaj', 'slug' => 'peyzaj'],
                'ar' => ['name' => 'مناظر طبيعية', 'slug' => 'landscape-ar'],
            ],
            [
                'order' => 4,
                'en' => ['name' => 'Interior', 'slug' => 'interior'],
                'tr' => ['name' => 'İç Mekân', 'slug' => 'ic-mekan'],
                'ar' => ['name' => 'داخلي', 'slug' => 'interior-ar'],
            ],
        ];

        $categories = [];

        foreach ($items as $item) {
            $category = $this->findOrCreateCategoryByEnSlug($item['en']['slug'], $languages['en']->id, $item['order']);

            foreach (['en', 'tr', 'ar'] as $code) {
                $category->translations()->updateOrCreate(
                    ['language_id' => $languages[$code]->id],
                    $item[$code],
                );
            }

            $categories[] = $category->fresh();
        }

        return $categories;
    }

    private function findOrCreateCategoryByEnSlug(string $slug, int $englishId, int $order): ProjectCategory
    {
        $existing = ProjectCategoryTranslation::query()
            ->where('language_id', $englishId)
            ->where('slug', $slug)
            ->first();

        if ($existing) {
            $category = ProjectCategory::query()->findOrFail($existing->project_category_id);
            $category->update(['order' => $order]);

            return $category;
        }

        return ProjectCategory::query()->create(['order' => $order]);
    }

    /**
     * @param  \Illuminate\Support\Collection<string, Language>  $languages
     * @return array<int, Concept>
     */
    private function seedConcepts($languages): array
    {
        $items = [
            [
                'icon' => 'compass',
                'en' => [
                    'title' => 'Creativity',
                    'short_description' => 'Pushing boundaries to discover unique spatial solutions.',
                ],
                'tr' => [
                    'title' => 'Yaratıcılık',
                    'short_description' => 'Benzersiz mekânsal çözümler keşfetmek için sınırları zorlamak.',
                ],
                'ar' => [
                    'title' => 'الإبداع',
                    'short_description' => 'دفع الحدود لاكتشاف حلول مكانية فريدة.',
                ],
            ],
            [
                'icon' => 'leaf',
                'en' => [
                    'title' => 'Sustainability',
                    'short_description' => 'Designing with deep respect for environmental impact.',
                ],
                'tr' => [
                    'title' => 'Sürdürülebilirlik',
                    'short_description' => 'Çevresel etkiye derin saygıyla tasarlamak.',
                ],
                'ar' => [
                    'title' => 'الاستدامة',
                    'short_description' => 'التصميم باحترام عميق للأثر البيئي.',
                ],
            ],
            [
                'icon' => 'ruler',
                'en' => [
                    'title' => 'Precision',
                    'short_description' => 'Uncompromising attention to technical detail and execution.',
                ],
                'tr' => [
                    'title' => 'Hassasiyet',
                    'short_description' => 'Teknik detay ve uygulamaya ödünsüz dikkat.',
                ],
                'ar' => [
                    'title' => 'الدقة',
                    'short_description' => 'اهتمام لا يساوم بالتفاصيل التقنية والتنفيذ.',
                ],
            ],
            [
                'icon' => 'users',
                'en' => [
                    'title' => 'Collaboration',
                    'short_description' => 'Fostering synergy between clients, builders, and designers.',
                ],
                'tr' => [
                    'title' => 'İş Birliği',
                    'short_description' => 'Müşteriler, uygulayıcılar ve tasarımcılar arasında sinerji.',
                ],
                'ar' => [
                    'title' => 'التعاون',
                    'short_description' => 'تعزيز التآزر بين العملاء والمنفذين والمصممين.',
                ],
            ],
            [
                'icon' => 'building',
                'en' => [
                    'title' => 'Context',
                    'short_description' => 'Rooting every project in place, culture, and climate.',
                ],
                'tr' => [
                    'title' => 'Bağlam',
                    'short_description' => 'Her projeyi yer, kültür ve iklimle ilişkilendirmek.',
                ],
                'ar' => [
                    'title' => 'السياق',
                    'short_description' => 'ترسيخ كل مشروع في المكان والثقافة والمناخ.',
                ],
            ],
        ];

        $concepts = [];

        foreach ($items as $item) {
            $concept = $this->findOrCreateConceptByEnTitle($item['en']['title'], $languages['en']->id, $item['icon']);

            foreach (['en', 'tr', 'ar'] as $code) {
                $concept->translations()->updateOrCreate(
                    ['language_id' => $languages[$code]->id],
                    $item[$code],
                );
            }

            $concepts[] = $concept->fresh();
        }

        return $concepts;
    }

    private function findOrCreateConceptByEnTitle(string $title, int $englishId, string $icon): Concept
    {
        $existing = Concept::query()
            ->whereHas('translations', fn ($q) => $q->where('language_id', $englishId)->where('title', $title))
            ->first();

        if ($existing) {
            $existing->update(['icon' => $icon]);

            return $existing;
        }

        return Concept::query()->create(['icon' => $icon]);
    }

    /**
     * @param  \Illuminate\Support\Collection<string, Language>  $languages
     * @param  array<int, ProjectCategory>  $categories
     * @param  array<int, Concept>  $concepts
     */
    private function seedProjects($languages, array $categories, array $concepts): void
    {
        $projects = [
            [
                'slug' => 'noir-residence',
                'client_name' => 'Noir Family Office',
                'location' => 'Istanbul, Türkiye',
                'year' => 2024,
                'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                'en' => [
                    'name' => 'Noir Residence',
                    'short_description' => 'A quiet cliffside home shaped by light and stone.',
                    'description' => '<p>Noir Residence sits on a narrow plot overlooking the Bosphorus. The plan folds living spaces toward the view while protecting privacy from the street.</p><p>Material palette: dark oak, limestone, and bronze fittings. Soft daylight washes the double-height atrium throughout the day.</p>',
                ],
                'tr' => [
                    'name' => 'Noir Residence',
                    'short_description' => 'Işık ve taşla şekillenen sakin bir yamaç evi.',
                    'description' => '<p>Noir Residence, Boğaz manzaralı dar bir parselde yer alır. Yaşam alanları manzaraya açılırken sokaktan mahremiyet korunur.</p><p>Malzeme paleti: koyu meşe, kireçtaşı ve bronz detaylar.</p>',
                ],
                'ar' => [
                    'name' => 'نوار ريزيدنس',
                    'short_description' => 'منزل هادئ على المنحدر يتشكل بالضوء والحجر.',
                    'description' => '<p>يقع نوار ريزيدنس على قطعة ضيقة تطل على البوسفور. تنفتح مساحات المعيشة نحو الإطلالة مع حماية الخصوصية من الشارع.</p>',
                ],
                'images' => [
                    'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=1400&q=80&auto=format&fit=crop',
                    'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=1400&q=80&auto=format&fit=crop',
                    'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?w=1400&q=80&auto=format&fit=crop',
                ],
            ],
            [
                'slug' => 'atrium-house',
                'client_name' => 'Demir Holding',
                'location' => 'Ankara, Türkiye',
                'year' => 2023,
                'en' => [
                    'name' => 'Atrium House',
                    'short_description' => 'Courtyard living for a multi-generational family.',
                    'description' => '<p>A central atrium organizes four wings around shared gardens. Cross ventilation and deep overhangs temper Ankara summers.</p>',
                ],
                'tr' => [
                    'name' => 'Atrium Evi',
                    'short_description' => 'Çok kuşaklı bir aile için avlulu yaşam.',
                    'description' => '<p>Merkezi atrium dört kanadı ortak bahçeler etrafında düzenler.</p>',
                ],
                'ar' => [
                    'name' => 'منزل الأتريوم',
                    'short_description' => 'حياة فناء لأسرة متعددة الأجيال.',
                    'description' => '<p>ينظم الأتريوم المركزي أربعة أجنحة حول حدائق مشتركة.</p>',
                ],
                'images' => [
                    'https://images.unsplash.com/photo-1613490493576-7fde63acd811?w=1400&q=80&auto=format&fit=crop',
                    'https://images.unsplash.com/photo-1600047509807-ba8f99d2cd0c?w=1400&q=80&auto=format&fit=crop',
                    'https://images.unsplash.com/photo-1600566753190-17f0baa2a6c3?w=1400&q=80&auto=format&fit=crop',
                ],
            ],
            [
                'slug' => 'harbor-loft',
                'client_name' => 'Marmara Yachts',
                'location' => 'Izmir, Türkiye',
                'year' => 2022,
                'en' => [
                    'name' => 'Harbor Loft',
                    'short_description' => 'Adaptive reuse of a warehouse into open loft living.',
                    'description' => '<p>Exposed structure and industrial glazing remain, while warm timber inserts create intimate zones for living and work.</p>',
                ],
                'tr' => [
                    'name' => 'Liman Loft',
                    'short_description' => 'Depodan açık loft yaşamına dönüşüm.',
                    'description' => '<p>Açık yapı ve endüstriyel camlar korunurken sıcak ahşap ekler mahrem alanlar oluşturur.</p>',
                ],
                'ar' => [
                    'name' => 'هاربر لوفت',
                    'short_description' => 'إعادة توظيف مستودع إلى مساحة سكن مفتوحة.',
                    'description' => '<p>يبقى الهيكل المكشوف والزجاج الصناعي مع إضافات خشبية دافئة للمناطق الحميمة.</p>',
                ],
                'images' => [
                    'https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=1400&q=80&auto=format&fit=crop',
                    'https://images.unsplash.com/photo-1493809842364-78817add7ffb?w=1400&q=80&auto=format&fit=crop',
                    'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=1400&q=80&auto=format&fit=crop',
                ],
            ],
            [
                'slug' => 'cedar-pavilion',
                'client_name' => 'Aegean Retreats',
                'location' => 'Bodrum, Türkiye',
                'year' => 2025,
                'en' => [
                    'name' => 'Cedar Pavilion',
                    'short_description' => 'A lightweight guest pavilion among olive trees.',
                    'description' => '<p>Cedar screens filter Aegean light. The pavilion opens fully to the garden with sliding panels and a shaded terrace.</p>',
                ],
                'tr' => [
                    'name' => 'Sedir Pavyonu',
                    'short_description' => 'Zeytin ağaçları arasında hafif bir misafir pavyonu.',
                    'description' => '<p>Sedir paneller Ege ışığını süzer; kayar panellerle bahçeye açılır.</p>',
                ],
                'ar' => [
                    'name' => 'جناح الأرز',
                    'short_description' => 'جناح ضيوف خفيف بين أشجار الزيتون.',
                    'description' => '<p>تُرشّح ألواح الأرز ضوء بحر إيجة وتنفتح على الحديقة.</p>',
                ],
                'images' => [
                    'https://images.unsplash.com/photo-1600210492486-724fe5c67fb0?w=1400&q=80&auto=format&fit=crop',
                    'https://images.unsplash.com/photo-1600573472592-401b489a3cdc?w=1400&q=80&auto=format&fit=crop',
                    'https://images.unsplash.com/photo-1600585154526-990dced4db0d?w=1400&q=80&auto=format&fit=crop',
                ],
            ],
            [
                'slug' => 'gallery-row',
                'client_name' => 'Pera Arts Foundation',
                'location' => 'Istanbul, Türkiye',
                'year' => 2021,
                'en' => [
                    'name' => 'Gallery Row',
                    'short_description' => 'A linear gallery for rotating contemporary shows.',
                    'description' => '<p>Neutral volumes and calibrated daylight tracks support flexible exhibition layouts without distracting from the art.</p>',
                ],
                'tr' => [
                    'name' => 'Galeri Sıra',
                    'short_description' => 'Dönen çağdaş sergiler için doğrusal galeri.',
                    'description' => '<p>Nötr hacimler ve kontrollü gün ışığı esnek sergi düzenlerini destekler.</p>',
                ],
                'ar' => [
                    'name' => 'صف المعرض',
                    'short_description' => 'معرض خطي للعروض المعاصرة المتغيرة.',
                    'description' => '<p>أحجام محايدة وضوء نهاري مضبوط يدعمان تخطيطات عرض مرنة.</p>',
                ],
                'images' => [
                    'https://images.unsplash.com/photo-1497366216548-37526070297c?w=1400&q=80&auto=format&fit=crop',
                    'https://images.unsplash.com/photo-1497366811333-894147e37c2e?w=1400&q=80&auto=format&fit=crop',
                    'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=1400&q=80&auto=format&fit=crop',
                ],
            ],
            [
                'slug' => 'studio-atelier',
                'client_name' => 'Archify Studio',
                'location' => 'Kadıköy, Istanbul',
                'year' => 2020,
                'en' => [
                    'name' => 'Studio Atelier',
                    'short_description' => 'Our own workspace — open desks under a sawtooth roof.',
                    'description' => '<p>North light enters through the sawtooth roof. Pin-up walls and model tables sit at the heart of the studio floor.</p>',
                ],
                'tr' => [
                    'name' => 'Stüdyo Atölye',
                    'short_description' => 'Kendi çalışma alanımız — testere dişli çatı altında açık masalar.',
                    'description' => '<p>Kuzey ışığı çatıdan girer; pano duvarları ve maket masaları stüdyonun merkezindedir.</p>',
                ],
                'ar' => [
                    'name' => 'مرسم الاستوديو',
                    'short_description' => 'مساحة عملنا — مكاتب مفتوحة تحت سقف مسنن.',
                    'description' => '<p>يدخل ضوء الشمال عبر السقف؛ جدران العرض وطاولات النماذج في قلب الاستوديو.</p>',
                ],
                'images' => [
                    'https://images.unsplash.com/photo-1497366754035-f200968a6e72?w=1400&q=80&auto=format&fit=crop',
                    'https://images.unsplash.com/photo-1524758631624-e2822e304c36?w=1400&q=80&auto=format&fit=crop',
                    'https://images.unsplash.com/photo-1497215842964-222b430dc094?w=1400&q=80&auto=format&fit=crop',
                ],
            ],
            [
                'slug' => 'terrace-villa',
                'client_name' => 'Yılmaz Family',
                'location' => 'Antalya, Türkiye',
                'year' => 2024,
                'en' => [
                    'name' => 'Terrace Villa',
                    'short_description' => 'Cascading terraces toward the Mediterranean.',
                    'description' => '<p>Each level steps with the hillside. Outdoor rooms connect pool, kitchen garden, and shaded dining under pergolas.</p>',
                ],
                'tr' => [
                    'name' => 'Teras Villası',
                    'short_description' => 'Akdeniz’e doğru basamaklanan teraslar.',
                    'description' => '<p>Her kat yamaca uyum sağlar; açık odalar havuz ve gölgeli yemek alanını bağlar.</p>',
                ],
                'ar' => [
                    'name' => 'فيلا التراسات',
                    'short_description' => 'تراسات متدرجة نحو البحر المتوسط.',
                    'description' => '<p>تنزل كل طابق مع المنحدر وتربط المساحات الخارجية المسبح ومنطقة الطعام المظللة.</p>',
                ],
                'images' => [
                    'https://images.unsplash.com/photo-1613977257363-707ba9348227?w=1400&q=80&auto=format&fit=crop',
                    'https://images.unsplash.com/photo-1600607687644-c7171b42498b?w=1400&q=80&auto=format&fit=crop',
                    'https://images.unsplash.com/photo-1600566753086-00f18fb6b3ea?w=1400&q=80&auto=format&fit=crop',
                ],
            ],
            [
                'slug' => 'library-courtyard',
                'client_name' => 'Municipality of Bursa',
                'location' => 'Bursa, Türkiye',
                'year' => 2019,
                'en' => [
                    'name' => 'Library Courtyard',
                    'short_description' => 'A civic reading garden wrapped by quiet stacks.',
                    'description' => '<p>Brick and timber frame a protected courtyard. Acoustics and seating encourage long stays for study and community events.</p>',
                ],
                'tr' => [
                    'name' => 'Kütüphane Avlusu',
                    'short_description' => 'Sakin raflarla sarılmış kamusal okuma bahçesi.',
                    'description' => '<p>Tuğla ve ahşap korumalı bir avlu oluşturur; akustik ve oturma uzun kalışları teşvik eder.</p>',
                ],
                'ar' => [
                    'name' => 'فناء المكتبة',
                    'short_description' => 'حديقة قراءة عامة محاطة بأرفف هادئة.',
                    'description' => '<p>يشكل الطوب والخشب فناءً محمياً يشجع على الدراسة والفعاليات المجتمعية.</p>',
                ],
                'images' => [
                    'https://images.unsplash.com/photo-1521587760476-6c12a4b040da?w=1400&q=80&auto=format&fit=crop',
                    'https://images.unsplash.com/photo-1507842217343-583bb7270b66?w=1400&q=80&auto=format&fit=crop',
                    'https://images.unsplash.com/photo-1481627834876-b7833e8f5040?w=1400&q=80&auto=format&fit=crop',
                ],
            ],
            [
                'slug' => 'boutique-hotel-casa',
                'client_name' => 'Casa Hospitality Group',
                'location' => 'Cappadocia, Türkiye',
                'year' => 2023,
                'en' => [
                    'name' => 'Boutique Hotel Casa',
                    'short_description' => 'Cave-inspired suites with contemporary comfort.',
                    'description' => '<p>Carved volumes meet precise joinery. Soft lighting and local stone keep the hotel rooted in Cappadocian landscape.</p>',
                ],
                'tr' => [
                    'name' => 'Butik Otel Casa',
                    'short_description' => 'Çağdaş konforla mağara esintili süitler.',
                    'description' => '<p>Oyulmuş hacimler hassas doğramayla buluşur; yerel taş Kapadokya peyzajına bağlar.</p>',
                ],
                'ar' => [
                    'name' => 'فندق كاسا البوتيكي',
                    'short_description' => 'أجنحة مستوحاة من الكهوف براحة معاصرة.',
                    'description' => '<p>تلتقي الأحجام المنحوتة بأعمال نجارة دقيقة والحجر المحلي يربط الفندق بالمشهد.</p>',
                ],
                'images' => [
                    'https://images.unsplash.com/photo-1566073771259-6a8506099945?w=1400&q=80&auto=format&fit=crop',
                    'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?w=1400&q=80&auto=format&fit=crop',
                    'https://images.unsplash.com/photo-1578683010236-d716f9a3f461?w=1400&q=80&auto=format&fit=crop',
                ],
            ],
            [
                'slug' => 'riverside-office',
                'client_name' => 'Delta Tech',
                'location' => 'Gebze, Türkiye',
                'year' => 2022,
                'en' => [
                    'name' => 'Riverside Office',
                    'short_description' => 'A calm HQ campus for a growing tech team.',
                    'description' => '<p>Two bars of workspace frame a riverside garden. Collaboration hubs sit at the joints; focus rooms line the quieter edge.</p>',
                ],
                'tr' => [
                    'name' => 'Nehir Kenarı Ofis',
                    'short_description' => 'Büyüyen bir teknoloji ekibi için sakin kampüs.',
                    'description' => '<p>İki çalışma barı nehir bahçesini çerçeveler; ortak alanlar eklemlerde, odak odaları sakin cephede yer alır.</p>',
                ],
                'ar' => [
                    'name' => 'مكتب ضفة النهر',
                    'short_description' => 'حرم هادئ لفريق تقني متنامٍ.',
                    'description' => '<p>شريطان من مساحات العمل يؤطران حديقة النهر مع مراكز تعاون وغرف تركيز.</p>',
                ],
                'images' => [
                    'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=1400&q=80&auto=format&fit=crop',
                    'https://images.unsplash.com/photo-1497366216548-37526070297c?w=1400&q=80&auto=format&fit=crop',
                    'https://images.unsplash.com/photo-1497215728101-856f4ea42174?w=1400&q=80&auto=format&fit=crop',
                ],
            ],
        ];

        foreach ($projects as $index => $item) {
            $category = $categories[$index % count($categories)];
            $project = $this->findOrCreateProjectByEnSlug($item['slug'], $languages['en']->id, [
                'project_category_id' => $category->id,
                'client_name' => $item['client_name'],
                'location' => $item['location'],
                'year' => $item['year'],
                'video_url' => $item['video_url'] ?? null,
            ]);

            foreach (['en', 'tr', 'ar'] as $code) {
                $fields = $item[$code];
                $slug = $code === 'en'
                    ? $item['slug']
                    : Str::slug($fields['name']).($code === 'ar' ? '-ar' : '-'.$code);

                if ($slug === '' || $slug === '-ar' || $slug === '-tr') {
                    $slug = $item['slug'].'-'.$code;
                }

                $project->translations()->updateOrCreate(
                    ['language_id' => $languages[$code]->id],
                    [
                        'name' => $fields['name'],
                        'slug' => $slug,
                        'short_description' => $fields['short_description'],
                        'description' => $fields['description'],
                        'meta_title' => $fields['name'].' | Archify',
                        'meta_description' => $fields['short_description'],
                        'meta_keywords' => 'architecture, interior, '.$item['slug'],
                        'translation_status' => 'manual',
                    ],
                );
            }

            $conceptIds = [
                $concepts[$index % count($concepts)]->id,
                $concepts[($index + 2) % count($concepts)]->id,
            ];
            $project->concepts()->sync(array_unique($conceptIds));

            $images = $item['images'];
            $this->replaceCollectionWithRemote($project, 'thumbnail', [$images[0]]);
            $this->replaceCollectionWithRemote($project, 'images_2d', array_slice($images, 0, 3));
            if (isset($images[2])) {
                $this->replaceCollectionWithRemote($project, 'images_outdoor', [$images[2]]);
            }
        }
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function findOrCreateProjectByEnSlug(string $slug, int $englishId, array $attributes): Project
    {
        $existing = ProjectTranslation::query()
            ->where('language_id', $englishId)
            ->where('slug', $slug)
            ->first();

        if ($existing) {
            $project = Project::query()->findOrFail($existing->project_id);
            $project->update($attributes);

            return $project->fresh();
        }

        return Project::query()->create($attributes);
    }

    /**
     * @param  array<int, string>  $urls
     */
    private function replaceCollectionWithRemote(Model $model, string $collection, array $urls): void
    {
        if (! method_exists($model, 'clearMediaCollection')) {
            return;
        }

        /** @var Project $model */
        $model->clearMediaCollection($collection);

        foreach ($urls as $url) {
            $this->attachRemoteImage($model, $url, $collection);
        }
    }

    private function attachRemoteImage(Model $model, string $url, string $collection): void
    {
        $tmp = tempnam(sys_get_temp_dir(), 'seed_img_');

        if ($tmp === false) {
            Log::warning('PortfolioDemoSeeder: unable to create temp file', ['url' => $url]);

            return;
        }

        $path = $tmp.'.jpg';
        @rename($tmp, $path);

        try {
            $response = Http::timeout(45)
                ->withHeaders(['User-Agent' => 'ArchifyPortfolioSeeder/1.0'])
                ->sink($path)
                ->get($url);

            if (! $response->successful() || ! is_file($path) || filesize($path) < 1000) {
                Log::warning('PortfolioDemoSeeder: image download failed', [
                    'url' => $url,
                    'status' => $response->status(),
                ]);

                return;
            }

            /** @var Project $model */
            $model->addMedia($path)
                ->usingFileName(Str::slug(pathinfo(parse_url($url, PHP_URL_PATH) ?: 'image', PATHINFO_FILENAME)).'.jpg')
                ->toMediaCollection($collection);
        } catch (Throwable $e) {
            Log::warning('PortfolioDemoSeeder: '.$e->getMessage(), ['url' => $url]);
        } finally {
            if (is_file($path)) {
                @unlink($path);
            }
        }
    }
}
