<?php

namespace App\Http\Controllers;

use App\Http\Resources\ServiceResource;
use App\Services\ServiceService;
use App\Services\SiteSettingService;
use App\Support\UiTranslations;
use Inertia\Inertia;
use Inertia\Response;

class ServiceController extends Controller
{
    public function __construct(
        private readonly ServiceService $serviceService,
        private readonly SiteSettingService $siteSettingService,
    ) {}

    public function index(): Response
    {
        $ui = UiTranslations::flatten(UiTranslations::forLocale(app()->getLocale()));
        $pageTitle = $ui['public.services.title'] ?? $ui['nav.services'] ?? 'Services';

        return Inertia::render('Services/Index', [
            'services' => ServiceResource::collection($this->serviceService->all())->resolve(),
        ])->withViewData([
            'seo' => $this->siteSettingService->documentSeo([
                'page_title' => $pageTitle,
            ]),
        ]);
    }
}
