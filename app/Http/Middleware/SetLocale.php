<?php

namespace App\Http\Middleware;

use App\Repositories\Contracts\LanguageRepositoryInterface;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public const ADMIN_SESSION_KEY = 'admin_locale';

    public function __construct(
        private readonly LanguageRepositoryInterface $languageRepository,
    ) {}

    /**
     * Resolve UI locale: admin from session; public from optional {locale} route param.
     *
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $languages = $this->languageRepository->allActive();
        $default = $languages->firstWhere('is_default', true) ?? $languages->first();

        if (! $default) {
            abort(503, 'No active languages configured.');
        }

        if ($this->isAdminRequest($request)) {
            $code = $request->session()->get(self::ADMIN_SESSION_KEY);
            $language = $code
                ? $languages->firstWhere('code', $code)
                : null;

            app()->setLocale($language?->code ?? $default->code);

            return $next($request);
        }

        $code = $request->route('locale');

        if ($code === null || $code === '') {
            app()->setLocale($default->code);

            return $next($request);
        }

        $language = $languages->firstWhere('code', $code);

        if (! $language) {
            abort(404);
        }

        // Default language is served without a prefix (/ not /en).
        if ($language->is_default) {
            abort(404);
        }

        app()->setLocale($language->code);

        return $next($request);
    }

    private function isAdminRequest(Request $request): bool
    {
        return $request->is('admin') || $request->is('admin/*');
    }
}
