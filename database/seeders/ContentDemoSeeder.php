<?php

namespace Database\Seeders;

use App\Models\AboutPage;
use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\BlogCategoryTranslation;
use App\Models\BlogTranslation;
use App\Models\CoreValue;
use App\Models\CoreValueTranslation;
use App\Models\Faq;
use App\Models\FaqTranslation;
use App\Models\Language;
use App\Models\Lead;
use App\Models\Service;
use App\Models\ServiceTranslation;
use App\Models\Slider;
use App\Models\SliderTranslation;
use App\Models\Statistic;
use App\Models\StatisticTranslation;
use App\Models\TeamMember;
use App\Models\Testimonial;
use Database\Seeders\Concerns\AttachesRemoteMedia;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ContentDemoSeeder extends Seeder
{
    use AttachesRemoteMedia;

    /**
     * Seed remaining CMS domains with rich multilingual demo content + media.
     */
    public function run(): void
    {
        $languages = Language::query()->whereIn('code', ['en', 'tr', 'ar'])->get()->keyBy('code');

        if ($languages->count() < 3) {
            $this->command?->warn('ContentDemoSeeder skipped: en/tr/ar languages missing. Run LanguageSeeder first.');

            return;
        }

        $this->seedAboutStoryImage();
        $this->seedSliders($languages);
        $services = $this->seedServices($languages);
        $this->seedStatistics($languages);
        $this->seedCoreValues($languages);
        $this->seedTeamMembers($languages);
        $this->seedTestimonials($languages);
        $this->seedFaqs($languages);
        $categories = $this->seedBlogCategories($languages);
        $this->seedBlogs($languages, $categories);
        $this->seedLeads($languages, $services);

        $this->command?->info('Content demo data seeded (sliders, services, stats, values, team, testimonials, FAQs, blogs, leads).');
    }

    private function seedAboutStoryImage(): void
    {
        $about = AboutPage::query()->first();

        if (! $about) {
            return;
        }

        if ($about->getFirstMedia('story_image')) {
            return;
        }

        $this->attachRemoteImage(
            $about,
            'https://images.unsplash.com/photo-1600210492486-724fe5c67fb0?w=1600&q=80&auto=format&fit=crop',
            'story_image',
        );
    }

    /**
     * @param  Collection<string, Language>  $languages
     */
    private function seedSliders(Collection $languages): void
    {
        $items = [
            [
                'order' => 0,
                'en' => ['title' => 'Spaces That Breathe', 'description' => 'Architecture rooted in light, material, and quiet craft.'],
                'tr' => ['title' => 'Nefes Alan Mekânlar', 'description' => 'Işık, malzeme ve sakin ustalığa dayanan mimari.'],
                'ar' => ['title' => 'مساحات تتنفس', 'description' => 'عمارة متجذّرة في الضوء والمادة والحرفة الهادئة.'],
                'image' => 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=1920&q=80&auto=format&fit=crop',
            ],
            [
                'order' => 1,
                'en' => ['title' => 'Interior Calm', 'description' => 'Residential interiors tuned for daily ritual and rest.'],
                'tr' => ['title' => 'İç Mekân Dinginliği', 'description' => 'Günlük ritüel ve dinlenmeye ayarlı konut iç mekânları.'],
                'ar' => ['title' => 'هدوء داخلي', 'description' => 'ديكورات سكنية متناغمة مع الطقوس اليومية والراحة.'],
                'image' => 'https://images.unsplash.com/photo-1600210492486-724fe5c67fb0?w=1920&q=80&auto=format&fit=crop',
            ],
            [
                'order' => 2,
                'en' => ['title' => 'Urban Hospitality', 'description' => 'Boutique hotels and lounges with tactile presence.'],
                'tr' => ['title' => 'Kentsel Ağırlama', 'description' => 'Dokunsal varlığı olan butik oteller ve lounge’lar.'],
                'ar' => ['title' => 'ضيافة حضرية', 'description' => 'فنادق بوتيك وصالات بحضور مادّي ملموس.'],
                'image' => 'https://images.unsplash.com/photo-1566073771259-6a8506099945?w=1920&q=80&auto=format&fit=crop',
            ],
            [
                'order' => 3,
                'en' => ['title' => 'Landscape Continuum', 'description' => 'Gardens and terraces that extend the living room outdoors.'],
                'tr' => ['title' => 'Peyzaj Sürekliliği', 'description' => 'Oturma odasını dışarıya taşıyan bahçeler ve teraslar.'],
                'ar' => ['title' => 'استمرارية المشهد', 'description' => 'حدائق وتراسات تمدّ غرفة المعيشة إلى الخارج.'],
                'image' => 'https://images.unsplash.com/photo-1613977257363-707ba9348227?w=1920&q=80&auto=format&fit=crop',
            ],
            [
                'order' => 4,
                'en' => ['title' => 'Work, Softened', 'description' => 'Workplaces designed for focus without the corporate chill.'],
                'tr' => ['title' => 'Yumuşatılmış Çalışma', 'description' => 'Kurumsal soğukluk olmadan odak için tasarlanmış ofisler.'],
                'ar' => ['title' => 'عمل بلطف', 'description' => 'مساحات عمل للتركيز دون برودة الشركات.'],
                'image' => 'https://images.unsplash.com/photo-1497366216548-37526070297c?w=1920&q=80&auto=format&fit=crop',
            ],
        ];

        foreach ($items as $item) {
            $slider = $this->findOrCreateByEnTitle(
                Slider::class,
                SliderTranslation::class,
                'slider_id',
                $item['en']['title'],
                $languages['en']->id,
                ['order' => $item['order'], 'is_active' => true],
            );

            foreach (['en', 'tr', 'ar'] as $code) {
                $slider->translations()->updateOrCreate(
                    ['language_id' => $languages[$code]->id],
                    $item[$code],
                );
            }

            $this->syncRemoteImages($slider, 'image', [$item['image']]);
        }
    }

    /**
     * @param  Collection<string, Language>  $languages
     * @return list<Service>
     */
    private function seedServices(Collection $languages): array
    {
        $items = [
            [
                'icon' => 'building-arch',
                'order' => 0,
                'show_on_home' => true,
                'en' => [
                    'title' => 'Architectural Design',
                    'short_description' => 'Concept to construction documentation for residential and civic buildings.',
                    'included_items' => ['Concept & massing', 'Permit drawings', 'Material schedules'],
                ],
                'tr' => [
                    'title' => 'Mimari Tasarım',
                    'short_description' => 'Konut ve kamusal yapılar için konseptten uygulama projelerine.',
                    'included_items' => ['Konsept ve kütle', 'Ruhsat çizimleri', 'Malzeme listeleri'],
                ],
                'ar' => [
                    'title' => 'التصميم المعماري',
                    'short_description' => 'من الفكرة إلى وثائق التنفيذ للمباني السكنية والعامة.',
                    'included_items' => ['المفهوم والكتلة', 'رسومات الترخيص', 'جداول المواد'],
                ],
            ],
            [
                'icon' => 'sofa',
                'order' => 1,
                'show_on_home' => true,
                'en' => [
                    'title' => 'Interior Styling',
                    'short_description' => 'Furniture, lighting, and finishes composed as one calm narrative.',
                    'included_items' => ['Mood boards', 'FF&E selection', 'Styling install'],
                ],
                'tr' => [
                    'title' => 'İç Mekân Styling',
                    'short_description' => 'Mobilya, aydınlatma ve yüzeyler tek sakin anlatıda.',
                    'included_items' => ['Mood board’lar', 'FF&E seçimi', 'Kurulum styling’i'],
                ],
                'ar' => [
                    'title' => 'تنسيق الديكور',
                    'short_description' => 'أثاث وإضاءة وتشطيبات في سرد هادئ واحد.',
                    'included_items' => ['لوحات المزاج', 'اختيار الأثاث', 'تركيب التنسيق'],
                ],
            ],
            [
                'icon' => 'tree',
                'order' => 2,
                'show_on_home' => true,
                'en' => [
                    'title' => 'Landscape Architecture',
                    'short_description' => 'Outdoor rooms, planting, and water features tied to the building.',
                    'included_items' => ['Site analysis', 'Planting plans', 'Hardscape details'],
                ],
                'tr' => [
                    'title' => 'Peyzaj Mimarlığı',
                    'short_description' => 'Yapıya bağlı dış mekânlar, bitkilendirme ve su ögeleri.',
                    'included_items' => ['Alan analizi', 'Bitkilendirme planları', 'Sert zemin detayları'],
                ],
                'ar' => [
                    'title' => 'عمارة المناظر',
                    'short_description' => 'فضاءات خارجية وزراعة وعناصر مائية مرتبطة بالمبنى.',
                    'included_items' => ['تحليل الموقع', 'خطط الزراعة', 'تفاصيل الأرضيات'],
                ],
            ],
            [
                'icon' => 'ruler',
                'order' => 3,
                'show_on_home' => false,
                'en' => [
                    'title' => 'Space Planning',
                    'short_description' => 'Efficient layouts that still feel generous and intuitive.',
                    'included_items' => ['Program workshops', 'Circulation studies', 'Furniture plans'],
                ],
                'tr' => [
                    'title' => 'Mekân Planlama',
                    'short_description' => 'Cömert ve sezgisel hisseden verimli planlar.',
                    'included_items' => ['Program atölyeleri', 'Dolaşım çalışmaları', 'Mobilya planları'],
                ],
                'ar' => [
                    'title' => 'تخطيط الفراغ',
                    'short_description' => 'مخططات فعّالة تبدو رحبة وبديهية.',
                    'included_items' => ['ورش البرنامج', 'دراسات الحركة', 'خطط الأثاث'],
                ],
            ],
            [
                'icon' => 'lamp',
                'order' => 4,
                'show_on_home' => false,
                'en' => [
                    'title' => 'Lighting Design',
                    'short_description' => 'Layered daylight and artificial light for mood and clarity.',
                    'included_items' => ['Daylight studies', 'Fixture schedules', 'Scene control'],
                ],
                'tr' => [
                    'title' => 'Aydınlatma Tasarımı',
                    'short_description' => 'Ruh hali ve netlik için katmanlı gün ışığı ve yapay ışık.',
                    'included_items' => ['Gün ışığı çalışmaları', 'Armatür listeleri', 'Sahne kontrolü'],
                ],
                'ar' => [
                    'title' => 'تصميم الإضاءة',
                    'short_description' => 'ضوء نهار واصطناعي متدرج للمزاج والوضوح.',
                    'included_items' => ['دراسات ضوء النهار', 'جداول الوحدات', 'التحكم بالمشاهد'],
                ],
            ],
            [
                'icon' => 'hammer',
                'order' => 5,
                'show_on_home' => false,
                'en' => [
                    'title' => 'Construction Oversight',
                    'short_description' => 'Site presence to protect detailing from concept through handover.',
                    'included_items' => ['Site reviews', 'Sample approvals', 'Snagging support'],
                ],
                'tr' => [
                    'title' => 'Şantiye Denetimi',
                    'short_description' => 'Konseptten teslime detayı koruyan saha varlığı.',
                    'included_items' => ['Saha incelemeleri', 'Numune onayları', 'Eksik listesi desteği'],
                ],
                'ar' => [
                    'title' => 'إشراف التنفيذ',
                    'short_description' => 'حضور ميداني يحمي التفاصيل من الفكرة حتى التسليم.',
                    'included_items' => ['مراجعات الموقع', 'اعتماد العينات', 'دعم قائمة الملاحظات'],
                ],
            ],
        ];

        $services = [];

        foreach ($items as $item) {
            $service = $this->findOrCreateByEnTitle(
                Service::class,
                ServiceTranslation::class,
                'service_id',
                $item['en']['title'],
                $languages['en']->id,
                [
                    'icon' => $item['icon'],
                    'order' => $item['order'],
                    'show_on_home' => $item['show_on_home'],
                ],
            );

            foreach (['en', 'tr', 'ar'] as $code) {
                $service->translations()->updateOrCreate(
                    ['language_id' => $languages[$code]->id],
                    $item[$code],
                );
            }

            $services[] = $service->fresh();
        }

        return $services;
    }

    /**
     * @param  Collection<string, Language>  $languages
     */
    private function seedStatistics(Collection $languages): void
    {
        $items = [
            [
                'count' => 120,
                'order' => 0,
                'en' => ['label' => 'Projects Delivered'],
                'tr' => ['label' => 'Teslim Edilen Proje'],
                'ar' => ['label' => 'مشاريع منجزة'],
            ],
            [
                'count' => 18,
                'order' => 1,
                'en' => ['label' => 'Years of Practice'],
                'tr' => ['label' => 'Yıllık Deneyim'],
                'ar' => ['label' => 'سنوات خبرة'],
            ],
            [
                'count' => 45,
                'order' => 2,
                'en' => ['label' => 'Team Specialists'],
                'tr' => ['label' => 'Uzman Ekip'],
                'ar' => ['label' => 'متخصصون في الفريق'],
            ],
            [
                'count' => 12,
                'order' => 3,
                'en' => ['label' => 'Design Awards'],
                'tr' => ['label' => 'Tasarım Ödülü'],
                'ar' => ['label' => 'جوائز تصميم'],
            ],
        ];

        foreach ($items as $item) {
            $stat = $this->findOrCreateByEnTitle(
                Statistic::class,
                StatisticTranslation::class,
                'statistic_id',
                $item['en']['label'],
                $languages['en']->id,
                ['count' => $item['count'], 'order' => $item['order']],
                'label',
            );

            foreach (['en', 'tr', 'ar'] as $code) {
                $stat->translations()->updateOrCreate(
                    ['language_id' => $languages[$code]->id],
                    $item[$code],
                );
            }
        }
    }

    /**
     * @param  Collection<string, Language>  $languages
     */
    private function seedCoreValues(Collection $languages): void
    {
        $items = [
            [
                'icon' => 'leaf',
                'order' => 0,
                'en' => ['title' => 'Material Honesty', 'short_description' => 'We let timber, stone, and plaster speak without heavy disguise.'],
                'tr' => ['title' => 'Malzeme Dürüstlüğü', 'short_description' => 'Ahşap, taş ve sıvanın ağır örtü olmadan konuşmasına izin veririz.'],
                'ar' => ['title' => 'صدق المواد', 'short_description' => 'ندع الخشب والحجر والجص يتحدثان دون تمويه ثقيل.'],
            ],
            [
                'icon' => 'compass',
                'order' => 1,
                'en' => ['title' => 'Site First', 'short_description' => 'Orientation, climate, and context drive every massing decision.'],
                'tr' => ['title' => 'Önce Arazi', 'short_description' => 'Yönelim, iklim ve bağlam her kütle kararını yönetir.'],
                'ar' => ['title' => 'الموقع أولاً', 'short_description' => 'التوجيه والمناخ والسياق يقودان كل قرار كتلي.'],
            ],
            [
                'icon' => 'users',
                'order' => 2,
                'en' => ['title' => 'Close Collaboration', 'short_description' => 'Clients stay inside the process — workshops, not black boxes.'],
                'tr' => ['title' => 'Yakın İş Birliği', 'short_description' => 'Müşteriler sürecin içinde kalır — atölyeler, kara kutular değil.'],
                'ar' => ['title' => 'تعاون وثيق', 'short_description' => 'يبقى العملاء داخل العملية — ورش عمل لا صناديق سوداء.'],
            ],
            [
                'icon' => 'hammer',
                'order' => 3,
                'en' => ['title' => 'Craft Precision', 'short_description' => 'Details are drawn once and defended on site until they land.'],
                'tr' => ['title' => 'Ustalık Hassasiyeti', 'short_description' => 'Detaylar bir kez çizilir ve sahada yerini bulana dek savunulur.'],
                'ar' => ['title' => 'دقة الحرفة', 'short_description' => 'تُرسم التفاصيل مرة وتُحمى في الموقع حتى تتحقق.'],
            ],
            [
                'icon' => 'world',
                'order' => 4,
                'en' => ['title' => 'Quiet Longevity', 'short_description' => 'We design for decades of use, not a single photoshoot season.'],
                'tr' => ['title' => 'Sakin Kalıcılık', 'short_description' => 'Tek bir çekim sezonu değil, onlarca yıllık kullanım için tasarlarız.'],
                'ar' => ['title' => 'استدامة هادئة', 'short_description' => 'نصمم لعقود من الاستخدام لا لموسم تصوير واحد.'],
            ],
        ];

        foreach ($items as $item) {
            $value = $this->findOrCreateByEnTitle(
                CoreValue::class,
                CoreValueTranslation::class,
                'core_value_id',
                $item['en']['title'],
                $languages['en']->id,
                ['icon' => $item['icon'], 'order' => $item['order']],
            );

            foreach (['en', 'tr', 'ar'] as $code) {
                $value->translations()->updateOrCreate(
                    ['language_id' => $languages[$code]->id],
                    $item[$code],
                );
            }
        }
    }

    /**
     * @param  Collection<string, Language>  $languages
     */
    private function seedTeamMembers(Collection $languages): void
    {
        $items = [
            [
                'name' => 'Elif Yılmaz',
                'order' => 0,
                'linkedin_url' => 'https://www.linkedin.com/',
                'en' => ['role' => 'Principal Architect'],
                'tr' => ['role' => 'Baş Mimar'],
                'ar' => ['role' => 'المهندسة المعمارية الرئيسية'],
                'avatar' => 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=600&q=80&auto=format&fit=crop',
            ],
            [
                'name' => 'Marcus Hale',
                'order' => 1,
                'linkedin_url' => 'https://www.linkedin.com/',
                'en' => ['role' => 'Design Director'],
                'tr' => ['role' => 'Tasarım Direktörü'],
                'ar' => ['role' => 'مدير التصميم'],
                'avatar' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=600&q=80&auto=format&fit=crop',
            ],
            [
                'name' => 'Sara Al-Hassan',
                'order' => 2,
                'behance_url' => 'https://www.behance.net/',
                'en' => ['role' => 'Interior Lead'],
                'tr' => ['role' => 'İç Mekân Lideri'],
                'ar' => ['role' => 'قائدة التصميم الداخلي'],
                'avatar' => 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?w=600&q=80&auto=format&fit=crop',
            ],
            [
                'name' => 'Can Demir',
                'order' => 3,
                'instagram_url' => 'https://www.instagram.com/',
                'en' => ['role' => 'Landscape Architect'],
                'tr' => ['role' => 'Peyzaj Mimarı'],
                'ar' => ['role' => 'مهندس مناظر طبيعية'],
                'avatar' => 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=600&q=80&auto=format&fit=crop',
            ],
            [
                'name' => 'Nora Varga',
                'order' => 4,
                'linkedin_url' => 'https://www.linkedin.com/',
                'en' => ['role' => 'Project Manager'],
                'tr' => ['role' => 'Proje Yöneticisi'],
                'ar' => ['role' => 'مديرة المشاريع'],
                'avatar' => 'https://images.unsplash.com/photo-1580489944761-15a19d654956?w=600&q=80&auto=format&fit=crop',
            ],
            [
                'name' => 'Yusuf Karaca',
                'order' => 5,
                'en' => ['role' => 'Visualization Artist'],
                'tr' => ['role' => 'Görselleştirme Sanatçısı'],
                'ar' => ['role' => 'فنان التصور'],
                'avatar' => 'https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?w=600&q=80&auto=format&fit=crop',
            ],
        ];

        foreach ($items as $item) {
            $member = TeamMember::query()->where('name', $item['name'])->first();

            $attrs = [
                'name' => $item['name'],
                'order' => $item['order'],
                'linkedin_url' => $item['linkedin_url'] ?? null,
                'behance_url' => $item['behance_url'] ?? null,
                'instagram_url' => $item['instagram_url'] ?? null,
            ];

            if ($member) {
                $member->update($attrs);
            } else {
                $member = TeamMember::query()->create($attrs);
            }

            foreach (['en', 'tr', 'ar'] as $code) {
                $member->translations()->updateOrCreate(
                    ['language_id' => $languages[$code]->id],
                    $item[$code],
                );
            }

            $this->syncRemoteImages($member, 'avatar', [$item['avatar']]);
        }
    }

    /**
     * @param  Collection<string, Language>  $languages
     */
    private function seedTestimonials(Collection $languages): void
    {
        $items = [
            [
                'client_name' => 'Amelia Rhodes',
                'order' => 0,
                'en' => ['quote' => 'They listened until the brief felt like ours — then delivered a home that still surprises us each season.'],
                'tr' => ['quote' => 'Brifi bizim gibi hissedene kadar dinlediler; her mevsim şaşırtan bir ev teslim ettiler.'],
                'ar' => ['quote' => 'استمعوا حتى أصبح الموجز ملكنا ثم سلّموا منزلاً يدهشنا في كل فصل.'],
                'avatar' => 'https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=400&q=80&auto=format&fit=crop',
            ],
            [
                'client_name' => 'Kenan Öztürk',
                'order' => 1,
                'en' => ['quote' => 'Our boutique hotel opened on schedule with detailing that guests notice without us pointing it out.'],
                'tr' => ['quote' => 'Butik otelimiz zamanında açıldı; misafirlerin fark ettiği detaylar anlatmaya gerek bırakmıyor.'],
                'ar' => ['quote' => 'افتتح فندقنا البوتيكي في موعده بتفاصيل يلاحظها الضيوف دون أن نشير إليها.'],
            ],
            [
                'client_name' => 'Helena Costa',
                'order' => 2,
                'en' => ['quote' => 'The office campus feels calm at 9am and still alive at 6pm — that balance is rare.'],
                'tr' => ['quote' => 'Ofis kampüsü sabah 9’da sakin, akşam 6’da hâlâ canlı — bu denge nadir.'],
                'ar' => ['quote' => 'حرم المكتب هادئ في التاسعة صباحاً وحيّ في السادسة مساءً — توازن نادر.'],
                'avatar' => 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?w=400&q=80&auto=format&fit=crop',
            ],
            [
                'client_name' => 'Rami Haddad',
                'order' => 3,
                'en' => ['quote' => 'Landscape and architecture arrived as one idea. The terrace finally feels like another room.'],
                'tr' => ['quote' => 'Peyzaj ve mimari tek fikir olarak geldi. Teras nihayet başka bir oda gibi.'],
                'ar' => ['quote' => 'وصل المشهد والعمارة كفكرة واحدة. الشرفة صارت غرفة أخرى أخيراً.'],
            ],
            [
                'client_name' => 'Mira Chen',
                'order' => 4,
                'en' => ['quote' => 'Clear drawings, patient site visits, and zero drama at handover. Exactly what we needed.'],
                'tr' => ['quote' => 'Net çizimler, sabırlı saha ziyaretleri ve teslimde sıfır drama. Tam ihtiyacımız olan.'],
                'ar' => ['quote' => 'رسومات واضحة وزيارات موقع صبورة وتسليم بلا دراما. بالضبط ما احتجنا.'],
                'avatar' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=400&q=80&auto=format&fit=crop',
            ],
            [
                'client_name' => 'Thomas Berg',
                'order' => 5,
                'en' => ['quote' => 'They treat material samples like decisions that matter — because in this house, they do.'],
                'tr' => ['quote' => 'Malzeme numunelerini önemli kararlar gibi ele alıyorlar — bu evde öyle çünkü.'],
                'ar' => ['quote' => 'يتعاملون مع عينات المواد كقرارات تهم — لأنها في هذا المنزل كذلك.'],
            ],
        ];

        foreach ($items as $item) {
            $testimonial = Testimonial::query()->where('client_name', $item['client_name'])->first();

            $attrs = [
                'client_name' => $item['client_name'],
                'order' => $item['order'],
            ];

            if ($testimonial) {
                $testimonial->update($attrs);
            } else {
                $testimonial = Testimonial::query()->create($attrs);
            }

            foreach (['en', 'tr', 'ar'] as $code) {
                $testimonial->translations()->updateOrCreate(
                    ['language_id' => $languages[$code]->id],
                    $item[$code],
                );
            }

            if (! empty($item['avatar'])) {
                $this->syncRemoteImages($testimonial, 'avatar', [$item['avatar']]);
            }
        }
    }

    /**
     * @param  Collection<string, Language>  $languages
     */
    private function seedFaqs(Collection $languages): void
    {
        $items = [
            [
                'order' => 0,
                'en' => [
                    'question' => 'How long does a typical residential project take?',
                    'answer' => 'Most homes move from brief to permit drawings in 4–8 months, depending on scope and municipality timelines. Construction oversight is scheduled separately.',
                ],
                'tr' => [
                    'question' => 'Tipik bir konut projesi ne kadar sürer?',
                    'answer' => 'Çoğu ev, kapsama ve belediye sürelerine bağlı olarak 4–8 ayda briften ruhsat çizimlerine geçer. Şantiye denetimi ayrı planlanır.',
                ],
                'ar' => [
                    'question' => 'كم يستغرق مشروع سكني نموذجي؟',
                    'answer' => 'معظم المنازل تنتقل من الموجز إلى رسومات الترخيص خلال 4–8 أشهر حسب النطاق والبلدية. إشراف التنفيذ يُجدول منفصلاً.',
                ],
            ],
            [
                'order' => 1,
                'en' => [
                    'question' => 'Do you work outside Türkiye?',
                    'answer' => 'Yes. We design remotely with local partners for site visits and permitting when projects sit abroad.',
                ],
                'tr' => [
                    'question' => 'Türkiye dışında çalışıyor musunuz?',
                    'answer' => 'Evet. Yurt dışı projelerde saha ve ruhsat için yerel ortaklarla uzaktan tasarlıyoruz.',
                ],
                'ar' => [
                    'question' => 'هل تعملون خارج تركيا؟',
                    'answer' => 'نعم. نصمم عن بُعد مع شركاء محليين للزيارات والترخيص عندما تكون المشاريع في الخارج.',
                ],
            ],
            [
                'order' => 2,
                'en' => [
                    'question' => 'Can you take on interiors only?',
                    'answer' => 'Absolutely. Many clients engage us for interiors after the shell is already designed or under construction.',
                ],
                'tr' => [
                    'question' => 'Sadece iç mekân alıyor musunuz?',
                    'answer' => 'Kesinlikle. Birçok müşteri kabuk tasarlandıktan veya inşaat sırasında sadece iç mekân için geliyor.',
                ],
                'ar' => [
                    'question' => 'هل يمكنكم تولي الديكور فقط؟',
                    'answer' => 'بالتأكيد. كثير من العملاء يتعاقدون معنا للديكور بعد تصميم الهيكل أو أثناء التنفيذ.',
                ],
            ],
            [
                'order' => 3,
                'en' => [
                    'question' => 'How do you price fees?',
                    'answer' => 'Fees are scoped by phase (concept, design development, documentation, site). We share a clear proposal before kickoff.',
                ],
                'tr' => [
                    'question' => 'Ücretleri nasıl belirliyorsunuz?',
                    'answer' => 'Ücretler aşamaya göre (konsept, geliştirme, dokümantasyon, saha) kapsamlanır. Başlamadan net teklif paylaşırız.',
                ],
                'ar' => [
                    'question' => 'كيف تحددون الأتعاب؟',
                    'answer' => 'تُحدد الأتعاب حسب المرحلة (مفهوم، تطوير، توثيق، موقع). نشارك عرضاً واضحاً قبل البدء.',
                ],
            ],
            [
                'order' => 4,
                'en' => [
                    'question' => 'Will you help select furniture and lighting?',
                    'answer' => 'Yes — FF&E and lighting design are available as packages or as part of a full interior mandate.',
                ],
                'tr' => [
                    'question' => 'Mobilya ve aydınlatma seçiminde yardımcı olur musunuz?',
                    'answer' => 'Evet — FF&E ve aydınlatma paket olarak veya tam iç mekân kapsamında sunulur.',
                ],
                'ar' => [
                    'question' => 'هل تساعدون في اختيار الأثاث والإضاءة؟',
                    'answer' => 'نعم — اختيار الأثاث والإضاءة متاح كباقات أو ضمن مهمة داخلية كاملة.',
                ],
            ],
            [
                'order' => 5,
                'en' => [
                    'question' => 'Do you provide 3D visualizations?',
                    'answer' => 'We include key view renders in design development. Extra scenes or animation can be added if needed.',
                ],
                'tr' => [
                    'question' => '3D görselleştirme sunuyor musunuz?',
                    'answer' => 'Tasarım geliştirmede ana görünüş render’ları dahildir. Gerekirse ek sahneler veya animasyon eklenir.',
                ],
                'ar' => [
                    'question' => 'هل تقدمون تصورات ثلاثية الأبعاد؟',
                    'answer' => 'نُدرج مشاهد رئيسية في مرحلة التطوير. يمكن إضافة مشاهد أو حركة عند الحاجة.',
                ],
            ],
            [
                'order' => 6,
                'en' => [
                    'question' => 'How involved will we be as clients?',
                    'answer' => 'Expect structured workshops at each gate. You approve direction before we deepen drawings.',
                ],
                'tr' => [
                    'question' => 'Müşteri olarak ne kadar dahil oluruz?',
                    'answer' => 'Her kapıda yapılandırılmış atölyeler bekleyin. Çizimleri derinleştirmeden önce yönü onaylarsınız.',
                ],
                'ar' => [
                    'question' => 'ما مدى مشاركتنا كعملاء؟',
                    'answer' => 'توقّع ورش عمل منظمة عند كل بوابة. تعتمدون الاتجاه قبل تعميق الرسومات.',
                ],
            ],
            [
                'order' => 7,
                'en' => [
                    'question' => 'Can you coordinate with our contractor?',
                    'answer' => 'Yes. We issue coordinated packages and join site meetings so detailing survives construction.',
                ],
                'tr' => [
                    'question' => 'Müteahhidimizle koordinasyon sağlar mısınız?',
                    'answer' => 'Evet. Koordineli paketler verir, detayın sahada yaşaması için toplantılara katılırız.',
                ],
                'ar' => [
                    'question' => 'هل تنسّقون مع المقاول؟',
                    'answer' => 'نعم. نصدر حزم منسّقة ونحضر اجتماعات الموقع حتى تبقى التفاصيل.',
                ],
            ],
            [
                'order' => 8,
                'en' => [
                    'question' => 'What languages do you work in?',
                    'answer' => 'Project communication is available in English, Turkish, and Arabic to match our studio languages.',
                ],
                'tr' => [
                    'question' => 'Hangi dillerde çalışıyorsunuz?',
                    'answer' => 'Proje iletişimi stüdyo dillerimize uygun olarak İngilizce, Türkçe ve Arapça sunulur.',
                ],
                'ar' => [
                    'question' => 'بأي لغات تعملون؟',
                    'answer' => 'التواصل متاح بالإنجليزية والتركية والعربية بما يوافق لغات الاستوديو.',
                ],
            ],
            [
                'order' => 9,
                'en' => [
                    'question' => 'How do we start a conversation?',
                    'answer' => 'Send a short note via the contact form with location, timeline, and what you hope the space will feel like. We reply within a few business days.',
                ],
                'tr' => [
                    'question' => 'Nasıl başlarız?',
                    'answer' => 'İletişim formundan konum, zaman çizelgesi ve mekânın nasıl hissettirmesini istediğinizi yazın. Birkaç iş günü içinde dönüş yaparız.',
                ],
                'ar' => [
                    'question' => 'كيف نبدأ الحوار؟',
                    'answer' => 'أرسلوا عبر نموذج التواصل موقعاً وجدولاً وما تريدون أن يشعر به المكان. نرد خلال أيام عمل قليلة.',
                ],
            ],
        ];

        foreach ($items as $item) {
            $faq = $this->findOrCreateByEnTitle(
                Faq::class,
                FaqTranslation::class,
                'faq_id',
                $item['en']['question'],
                $languages['en']->id,
                ['order' => $item['order']],
                'question',
            );

            foreach (['en', 'tr', 'ar'] as $code) {
                $faq->translations()->updateOrCreate(
                    ['language_id' => $languages[$code]->id],
                    $item[$code],
                );
            }
        }
    }

    /**
     * @param  Collection<string, Language>  $languages
     * @return list<BlogCategory>
     */
    private function seedBlogCategories(Collection $languages): array
    {
        $items = [
            [
                'color' => '#bd854f',
                'order' => 0,
                'en' => ['name' => 'Process', 'slug' => 'process'],
                'tr' => ['name' => 'Süreç', 'slug' => 'surec'],
                'ar' => ['name' => 'العملية', 'slug' => 'process-ar'],
            ],
            [
                'color' => '#998f82',
                'order' => 1,
                'en' => ['name' => 'Materials', 'slug' => 'materials'],
                'tr' => ['name' => 'Malzemeler', 'slug' => 'malzemeler'],
                'ar' => ['name' => 'المواد', 'slug' => 'materials-ar'],
            ],
            [
                'color' => '#66411b',
                'order' => 2,
                'en' => ['name' => 'Studio Notes', 'slug' => 'studio-notes'],
                'tr' => ['name' => 'Stüdyo Notları', 'slug' => 'studyo-notlari'],
                'ar' => ['name' => 'ملاحظات الاستوديو', 'slug' => 'studio-notes-ar'],
            ],
            [
                'color' => '#835422',
                'order' => 3,
                'en' => ['name' => 'Site Visits', 'slug' => 'site-visits'],
                'tr' => ['name' => 'Saha Ziyaretleri', 'slug' => 'saha-ziyaretleri'],
                'ar' => ['name' => 'زيارات الموقع', 'slug' => 'site-visits-ar'],
            ],
        ];

        $categories = [];

        foreach ($items as $item) {
            $existing = BlogCategoryTranslation::query()
                ->where('language_id', $languages['en']->id)
                ->where('slug', $item['en']['slug'])
                ->first();

            if ($existing) {
                $category = BlogCategory::query()->findOrFail($existing->blog_category_id);
                $category->update(['color' => $item['color'], 'order' => $item['order']]);
            } else {
                $category = BlogCategory::query()->create([
                    'color' => $item['color'],
                    'order' => $item['order'],
                ]);
            }

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

    /**
     * @param  Collection<string, Language>  $languages
     * @param  list<BlogCategory>  $categories
     */
    private function seedBlogs(Collection $languages, array $categories): void
    {
        $items = [
            [
                'category' => 0,
                'slug' => 'briefing-without-moodboards',
                'views' => 420,
                'thumbnail' => 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=1200&q=80&auto=format&fit=crop',
                'cover' => 'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?w=1600&q=80&auto=format&fit=crop',
                'en' => [
                    'title' => 'Briefing Without Moodboards',
                    'content' => '<p>Moodboards arrive early, but the real brief is about how a household moves through a day. We start with rituals: where coffee is made, where shoes land, where winter light is needed most.</p><p>Only then do material references enter the conversation — as answers, not decoration.</p>',
                    'read_time' => 4,
                ],
                'tr' => [
                    'title' => 'Moodboard’suz Brief',
                    'content' => '<p>Moodboard’lar erken gelir ama gerçek brief, bir hanenin gün boyunca nasıl hareket ettiğidir. Ritüellerle başlarız.</p><p>Malzeme referansları ancak o zaman — cevap olarak — konuşmaya girer.</p>',
                    'read_time' => 4,
                ],
                'ar' => [
                    'title' => 'موجز بلا لوحات مزاج',
                    'content' => '<p>تأتي لوحات المزاج مبكراً، لكن الموجز الحقيقي عن حركة المنزل خلال اليوم. نبدأ بالطقوس.</p><p>ثم تدخل مراجع المواد كإجابات لا كزينة.</p>',
                    'read_time' => 4,
                ],
            ],
            [
                'category' => 0,
                'slug' => 'three-gates-of-design',
                'views' => 310,
                'thumbnail' => 'https://images.unsplash.com/photo-1497366811333-894147e37c2e?w=1200&q=80&auto=format&fit=crop',
                'en' => [
                    'title' => 'Three Gates of Design',
                    'content' => '<p>We use three approval gates — concept, developed design, and documentation — so clients never discover major shifts late.</p><p>Each gate ends with a workshop, not a silent PDF drop.</p>',
                    'read_time' => 3,
                ],
                'tr' => [
                    'title' => 'Tasarımın Üç Kapısı',
                    'content' => '<p>Konsept, geliştirilmiş tasarım ve dokümantasyon olmak üzere üç onay kapısı kullanırız.</p><p>Her kapı sessiz PDF değil, bir atölyeyle biter.</p>',
                    'read_time' => 3,
                ],
                'ar' => [
                    'title' => 'ثلاث بوابات للتصميم',
                    'content' => '<p>نستخدم ثلاث بوابات موافقة: المفهوم والتطوير والتوثيق.</p><p>تنتهي كل بوابة بورشة لا بملف صامت.</p>',
                    'read_time' => 3,
                ],
            ],
            [
                'category' => 0,
                'slug' => 'when-to-invite-the-contractor',
                'views' => 188,
                'thumbnail' => 'https://images.unsplash.com/photo-1503387762-592deb58ef4e?w=1200&q=80&auto=format&fit=crop',
                'en' => [
                    'title' => 'When to Invite the Contractor',
                    'content' => '<p>Bringing a builder in during design development catches detailing that drawings alone cannot price. Early collaboration reduces change orders later.</p>',
                    'read_time' => 3,
                ],
                'tr' => [
                    'title' => 'Müteahhidi Ne Zaman Davet Etmeli',
                    'content' => '<p>Tasarım geliştirmede yükleniciyi dahil etmek, çizimlerin fiyatlandıramayacağı detayları yakalar.</p>',
                    'read_time' => 3,
                ],
                'ar' => [
                    'title' => 'متى ندعو المقاول',
                    'content' => '<p>إشراك المقاول أثناء تطوير التصميم يلتقط تفاصيل لا تسعرها الرسومات وحدها.</p>',
                    'read_time' => 3,
                ],
            ],
            [
                'category' => 1,
                'slug' => 'limestone-in-humid-climates',
                'views' => 512,
                'thumbnail' => 'https://images.unsplash.com/photo-1600566753190-17f0baa2a6c3?w=1200&q=80&auto=format&fit=crop',
                'cover' => 'https://images.unsplash.com/photo-1600047509807-ba8f99d2cd0c?w=1600&q=80&auto=format&fit=crop',
                'en' => [
                    'title' => 'Limestone in Humid Climates',
                    'content' => '<p>Limestone rewards patience: sealing, drainage, and the right finish matter more than the quarry name on the sample board.</p><p>We prefer honed surfaces indoors where bare feet meet cool stone in summer.</p>',
                    'read_time' => 5,
                ],
                'tr' => [
                    'title' => 'Nemli İklimlerde Kireçtaşı',
                    'content' => '<p>Kireçtaşı sabrı ödüllendirir: sızdırmazlık, drenaj ve doğru yüzey, numunedeki ocak adından önemlidir.</p>',
                    'read_time' => 5,
                ],
                'ar' => [
                    'title' => 'الحجر الجيري في المناخ الرطب',
                    'content' => '<p>يكافئ الحجر الجيري الصبر: العزل والصرف والتشطيب أهم من اسم المحجر على اللوحة.</p>',
                    'read_time' => 5,
                ],
            ],
            [
                'category' => 1,
                'slug' => 'timber-joinery-that-ages',
                'views' => 276,
                'thumbnail' => 'https://images.unsplash.com/photo-1613490493576-7fde63acd811?w=1200&q=80&auto=format&fit=crop',
                'en' => [
                    'title' => 'Timber Joinery That Ages',
                    'content' => '<p>We specify species and finishes that accept patina. Oil finishes can be refreshed; high-gloss films often cannot.</p>',
                    'read_time' => 4,
                ],
                'tr' => [
                    'title' => 'Yaşlanan Ahşap Doğrama',
                    'content' => '<p>Patina kabul eden tür ve yüzeyler seçeriz. Yağ yenilenebilir; yüksek parlak filmler genelde yenilenemez.</p>',
                    'read_time' => 4,
                ],
                'ar' => [
                    'title' => 'نجارة خشب تتقدم بأناقة',
                    'content' => '<p>نحدد أنواعاً وتشطيبات تقبل الباتينا. الزيوت تُجدَّد؛ الأفلام اللامعة غالباً لا.</p>',
                    'read_time' => 4,
                ],
            ],
            [
                'category' => 1,
                'slug' => 'plaster-as-soft-architecture',
                'views' => 199,
                'thumbnail' => 'https://images.unsplash.com/photo-1600573472592-401b489a3cdc?w=1200&q=80&auto=format&fit=crop',
                'en' => [
                    'title' => 'Plaster as Soft Architecture',
                    'content' => '<p>Lime plaster softens acoustics and light. In galleries and living rooms it replaces wallpaper noise with quiet texture.</p>',
                    'read_time' => 3,
                ],
                'tr' => [
                    'title' => 'Yumuşak Mimari Olarak Sıva',
                    'content' => '<p>Kireç sıvası akustiği ve ışığı yumuşatır; duvar kâğıdı gürültüsünün yerine sakin doku koyar.</p>',
                    'read_time' => 3,
                ],
                'ar' => [
                    'title' => 'الجص كعمارة ناعمة',
                    'content' => '<p>يلين جص الجير الصوت والضوء ويستبدل ضجيج ورق الجدران بنسيج هادئ.</p>',
                    'read_time' => 3,
                ],
            ],
            [
                'category' => 2,
                'slug' => 'studio-ritual-monday-reviews',
                'views' => 145,
                'thumbnail' => 'https://images.unsplash.com/photo-1524758631624-e2822e304c36?w=1200&q=80&auto=format&fit=crop',
                'en' => [
                    'title' => 'Studio Ritual: Monday Reviews',
                    'content' => '<p>Every Monday we pin open questions on the wall — not polished boards. The goal is shared judgment before drawings harden.</p>',
                    'read_time' => 2,
                ],
                'tr' => [
                    'title' => 'Stüdyo Ritüeli: Pazartesi İncelemeleri',
                    'content' => '<p>Her pazartesi açık soruları duvara asarız — parlak panolar değil. Amaç çizimler sertleşmeden ortak yargı.</p>',
                    'read_time' => 2,
                ],
                'ar' => [
                    'title' => 'طقس الاستوديو: مراجعات الاثنين',
                    'content' => '<p>كل اثنين نعلّق الأسئلة المفتوحة على الحائط — لا لوحات لامعة. الهدف حكم مشترك قبل أن تتصلّب الرسومات.</p>',
                    'read_time' => 2,
                ],
            ],
            [
                'category' => 2,
                'slug' => 'sketchbooks-over-slides',
                'views' => 233,
                'thumbnail' => 'https://images.unsplash.com/photo-1452860606245-08befc0ff44b?w=1200&q=80&auto=format&fit=crop',
                'en' => [
                    'title' => 'Sketchbooks Over Slides',
                    'content' => '<p>Client workshops often start with paper. Sketching together reveals preferences faster than a forty-slide deck.</p>',
                    'read_time' => 3,
                ],
                'tr' => [
                    'title' => 'Slaytlardan Çok Eskiz Defteri',
                    'content' => '<p>Müşteri atölyeleri sıkça kâğıtla başlar. Birlikte çizmek, kırk slaytlı sunumdan hızlı tercih ortaya çıkarır.</p>',
                    'read_time' => 3,
                ],
                'ar' => [
                    'title' => 'دفاتر الرسم قبل الشرائح',
                    'content' => '<p>تبدأ ورش العملاء غالباً بالورق. الرسم معاً يكشف التفضيلات أسرع من عرض من أربعين شريحة.</p>',
                    'read_time' => 3,
                ],
            ],
            [
                'category' => 3,
                'slug' => 'cappadocia-stone-notes',
                'views' => 367,
                'thumbnail' => 'https://images.unsplash.com/photo-1566073771259-6a8506099945?w=1200&q=80&auto=format&fit=crop',
                'cover' => 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?w=1600&q=80&auto=format&fit=crop',
                'en' => [
                    'title' => 'Cappadocia Stone Notes',
                    'content' => '<p>On a recent hospitality site visit we traced how carved openings meet new joinery. The lesson: contemporary comfort can still honor volcanic geology.</p>',
                    'read_time' => 4,
                ],
                'tr' => [
                    'title' => 'Kapadokya Taş Notları',
                    'content' => '<p>Yakın bir otelcilik saha ziyaretinde oyulmuş açıklıkların yeni doğramayla nasıl buluştuğunu izledik.</p>',
                    'read_time' => 4,
                ],
                'ar' => [
                    'title' => 'ملاحظات حجر كابادوكيا',
                    'content' => '<p>في زيارة ضيافة حديثة تتبعنا كيف تلتقي الفتحات المنحوتة بالنجارة الجديدة.</p>',
                    'read_time' => 4,
                ],
            ],
        ];

        foreach ($items as $item) {
            $category = $categories[$item['category']] ?? $categories[0];

            $existing = BlogTranslation::query()
                ->where('language_id', $languages['en']->id)
                ->where('slug', $item['slug'])
                ->first();

            if ($existing) {
                $blog = Blog::query()->findOrFail($existing->blog_id);
                $blog->update([
                    'blog_category_id' => $category->id,
                    'views_count' => $item['views'],
                ]);
            } else {
                $blog = Blog::query()->create([
                    'blog_category_id' => $category->id,
                    'views_count' => $item['views'],
                ]);
            }

            foreach (['en', 'tr', 'ar'] as $code) {
                $locale = $item[$code];
                $slug = match ($code) {
                    'en' => $item['slug'],
                    'tr' => Str::slug($locale['title']) ?: $item['slug'].'-tr',
                    'ar' => $item['slug'].'-ar',
                };

                $blog->translations()->updateOrCreate(
                    ['language_id' => $languages[$code]->id],
                    [
                        'title' => $locale['title'],
                        'slug' => $slug,
                        'content' => $locale['content'],
                        'read_time' => $locale['read_time'],
                        'meta_title' => $locale['title'],
                        'meta_description' => Str::limit(strip_tags($locale['content']), 150),
                        'meta_keywords' => null,
                        'translation_status' => 'manual',
                    ],
                );
            }

            $this->syncRemoteImages($blog, 'thumbnail', [$item['thumbnail']]);

            if (! empty($item['cover'])) {
                $this->syncRemoteImages($blog, 'cover', [$item['cover']]);
            }
        }
    }

    /**
     * @param  Collection<string, Language>  $languages
     * @param  list<Service>  $services
     */
    private function seedLeads(Collection $languages, array $services): void
    {
        $items = [
            [
                'full_name' => 'Leyla Aksoy',
                'email' => 'leyla.aksoy.demo@example.com',
                'phone' => '+905551112233',
                'service_index' => 0,
                'message' => 'We are planning a seaside villa renovation and would like a concept workshop.',
                'status' => 'pending',
                'language' => 'en',
            ],
            [
                'full_name' => 'Omar Farouk',
                'email' => 'omar.farouk.demo@example.com',
                'phone' => '+971501234567',
                'service_index' => 1,
                'message' => 'Looking for full interior design for a duplex apartment in Dubai Marina.',
                'status' => 'pending',
                'language' => 'en',
            ],
            [
                'full_name' => 'Ayşe Kılıç',
                'email' => 'ayse.kilic.demo@example.com',
                'phone' => '+905329998877',
                'service_index' => 2,
                'message' => 'Bahçe ve teras için peyzaj danışmanlığı almak istiyoruz.',
                'status' => 'contacted',
                'internal_notes' => 'Called back; site visit scheduled next week.',
                'language' => 'tr',
            ],
            [
                'full_name' => 'Nadia Mansour',
                'email' => 'nadia.mansour.demo@example.com',
                'service_index' => null,
                'interest_other' => 'Furniture curation only',
                'message' => 'We already have an architect; need help selecting furniture and art.',
                'status' => 'pending',
                'language' => 'en',
            ],
            [
                'full_name' => 'Burak Şen',
                'email' => 'burak.sen.demo@example.com',
                'phone' => '+905327771122',
                'service_index' => 3,
                'message' => 'Ofis katı için mekân planlama ve aydınlatma birlikte yapılabilir mi?',
                'status' => 'contacted',
                'internal_notes' => 'Sent fee proposal for space planning + lighting.',
                'language' => 'tr',
            ],
            [
                'full_name' => 'Fatima Zahra',
                'email' => 'fatima.zahra.demo@example.com',
                'phone' => '+966501112233',
                'service_index' => 0,
                'message' => 'نرغب في تصميم منزل عائلي في الرياض مع مراعاة الخصوصية والضوء.',
                'status' => 'pending',
                'language' => 'ar',
            ],
            [
                'full_name' => 'James Whitfield',
                'email' => 'james.whitfield.demo@example.com',
                'service_index' => 5,
                'message' => 'Need construction oversight for a renovation already documented by another firm.',
                'status' => 'contacted',
                'language' => 'en',
            ],
            [
                'full_name' => 'Zeynep Arslan',
                'email' => 'zeynep.arslan.demo@example.com',
                'phone' => '+905334445566',
                'service_index' => 4,
                'message' => 'Butik otel lobisi için aydınlatma tasarımı teklifi rica ederiz.',
                'status' => 'pending',
                'language' => 'tr',
            ],
        ];

        foreach ($items as $item) {
            $serviceId = null;
            if ($item['service_index'] !== null && isset($services[$item['service_index']])) {
                $serviceId = $services[$item['service_index']]->id;
            }

            Lead::query()->updateOrCreate(
                ['email' => $item['email']],
                [
                    'full_name' => $item['full_name'],
                    'phone' => $item['phone'] ?? null,
                    'service_id' => $serviceId,
                    'interest_other' => $item['interest_other'] ?? null,
                    'message' => $item['message'],
                    'status' => $item['status'],
                    'internal_notes' => $item['internal_notes'] ?? null,
                    'language_id' => $languages[$item['language']]->id,
                    'ip_address' => '127.0.0.1',
                ],
            );
        }
    }

    /**
     * Find parent model by English translation field, or create with attributes.
     *
     * @param  class-string  $modelClass
     * @param  class-string  $translationClass
     * @param  array<string, mixed>  $attributes
     */
    private function findOrCreateByEnTitle(
        string $modelClass,
        string $translationClass,
        string $foreignKey,
        string $enValue,
        int $englishId,
        array $attributes,
        string $field = 'title',
    ) {
        $existing = $translationClass::query()
            ->where('language_id', $englishId)
            ->where($field, $enValue)
            ->first();

        if ($existing) {
            $model = $modelClass::query()->findOrFail($existing->{$foreignKey});
            $model->update($attributes);

            return $model;
        }

        return $modelClass::query()->create($attributes);
    }
}
