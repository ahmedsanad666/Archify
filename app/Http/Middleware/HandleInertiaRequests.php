<?php

namespace App\Http\Middleware;

use App\Http\Resources\LanguageResource;
use App\Http\Resources\SiteSettingResource;
use App\Models\Language;
use App\Repositories\Contracts\LanguageRepositoryInterface;
use App\Repositories\Contracts\ProjectCategoryRepositoryInterface;
use App\Repositories\Contracts\SiteSettingRepositoryInterface;
use App\Support\UiTranslations;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    public function __construct(
        private readonly LanguageRepositoryInterface $languageRepository,
        private readonly SiteSettingRepositoryInterface $siteSettingRepository,
        private readonly ProjectCategoryRepositoryInterface $projectCategoryRepository,
    ) {}

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * Locale-dependent values are lazy so they resolve after SetLocale runs.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
            'locale' => function () {
                $language = $this->resolveCurrentLanguage();

                return $language
                    ? (new LanguageResource($language))->resolve()
                    : null;
            },
            'languages' => fn () => LanguageResource::collection(
                $this->languageRepository->allActive(),
            )->resolve(),
            'siteSettings' => function () {
                $locale = $this->resolveCurrentLanguage();
                $siteSettings = $this->siteSettingRepository->getSingleton();

                if ($siteSettings && $locale) {
                    $siteSettings->load([
                        'translations' => fn ($query) => $query->where('language_id', $locale->id),
                    ]);
                }

                return $siteSettings
                    ? (new SiteSettingResource($siteSettings))->resolve()
                    : null;
            },
            'projectCategories' => function () {
                return $this->projectCategoryRepository->all()->map(function ($category) {
                    $translations = [];
                    foreach ($category->translations as $row) {
                        $code = $row->language?->code;
                        if (! $code) {
                            continue;
                        }
                        $translations[$code] = [
                            'name' => $row->name,
                            'slug' => $row->slug,
                        ];
                    }

                    return [
                        'id' => $category->id,
                        'translations' => $translations,
                    ];
                })->values()->all();
            },
            'ui' => fn () => UiTranslations::forLocale(app()->getLocale()),
        ];
    }

    private function resolveCurrentLanguage(): ?Language
    {
        $language = $this->languageRepository->findByCode(app()->getLocale());

        if ($language) {
            return $language;
        }

        $active = $this->languageRepository->allActive();

        return $active->firstWhere('is_default', true) ?? $active->first();
    }
}
