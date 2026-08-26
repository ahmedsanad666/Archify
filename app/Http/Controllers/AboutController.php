<?php

namespace App\Http\Controllers;

use App\Http\Resources\AboutPageResource;
use App\Http\Resources\CoreValueResource;
use App\Http\Resources\StatisticResource;
use App\Services\AboutPageService;
use App\Services\CoreValueService;
use App\Services\SiteSettingService;
use App\Services\StatisticService;
use App\Support\UiTranslations;
use Inertia\Inertia;
use Inertia\Response;

class AboutController extends Controller
{
    public function __construct(
        private readonly AboutPageService $aboutPageService,
        private readonly StatisticService $statisticService,
        private readonly CoreValueService $coreValueService,
        private readonly SiteSettingService $siteSettingService,
    ) {}

    public function index(): Response
    {
        $ui = UiTranslations::flatten(UiTranslations::forLocale(app()->getLocale()));
        $pageTitle = $ui['nav.about'] ?? 'About';

        return Inertia::render('About', [
            'about' => (new AboutPageResource($this->aboutPageService->get()))->resolve(),
            'statistics' => StatisticResource::collection($this->statisticService->all())->resolve(),
            'coreValues' => CoreValueResource::collection($this->coreValueService->all())->resolve(),
        ])->withViewData([
            'seo' => $this->siteSettingService->documentSeo([
                'page_title' => $pageTitle,
            ]),
        ]);
    }
}
