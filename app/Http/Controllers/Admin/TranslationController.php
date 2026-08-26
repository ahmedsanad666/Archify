<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateUiTranslationsRequest;
use App\Repositories\Contracts\LanguageRepositoryInterface;
use App\Services\UiTranslationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TranslationController extends Controller
{
    public function __construct(
        private readonly UiTranslationService $uiTranslationService,
        private readonly LanguageRepositoryInterface $languageRepository,
    ) {}

    public function index(Request $request): Response
    {
        $allowed = $this->uiTranslationService->allowedLocales();
        $requested = $request->query('locale');
        $activeLocale = is_string($requested) && in_array($requested, $allowed, true)
            ? $requested
            : (in_array(app()->getLocale(), $allowed, true) ? app()->getLocale() : ($allowed[0] ?? 'en'));

        $languages = $this->languageRepository->allActive();

        $locales = $languages
            ->filter(fn ($language) => in_array($language->code, $allowed, true))
            ->map(fn ($language) => [
                'code' => $language->code,
                'name' => $language->name,
            ])
            ->values()
            ->all();

        if ($locales === []) {
            $locales = collect($allowed)->map(fn (string $code) => [
                'code' => $code,
                'name' => strtoupper($code),
            ])->all();
        }

        return Inertia::render('Admin/Translations/Index', [
            'locales' => $locales,
            'activeLocale' => $activeLocale,
            'translations' => $this->uiTranslationService->flat($activeLocale),
            'groups' => $this->uiTranslationService->groups($activeLocale),
        ]);
    }

    public function update(UpdateUiTranslationsRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $locale = $data['locale'];
        $translations = $data['translations'];

        $normalized = [];
        foreach ($translations as $key => $value) {
            if (is_string($key)) {
                $normalized[$key] = (string) $value;
            }
        }

        $this->uiTranslationService->update($locale, $normalized);

        return redirect()
            ->route('admin.translations.index', ['locale' => $locale])
            ->with('success', 'Translations saved.');
    }
}
