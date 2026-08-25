<?php

namespace App\Http\Middleware;

use App\Http\Resources\LanguageResource;
use App\Http\Resources\SiteSettingResource;
use App\Models\Language;
use App\Repositories\Contracts\LanguageRepositoryInterface;
use App\Repositories\Contracts\SiteSettingRepositoryInterface;
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
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $locale = $this->resolveCurrentLanguage();
        $languages = $this->languageRepository->allActive();
        $siteSettings = $this->siteSettingRepository->getSingleton();

        if ($siteSettings && $locale) {
            $siteSettings->load([
                'translations' => fn ($query) => $query->where('language_id', $locale->id),
            ]);
        }

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
            'locale' => $locale ? (new LanguageResource($locale))->resolve() : null,
            'languages' => LanguageResource::collection($languages)->resolve(),
            'siteSettings' => $siteSettings
                ? (new SiteSettingResource($siteSettings))->resolve()
                : null,
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
