<?php

namespace App\Http\Controllers;

use App\Http\Resources\AboutPageResource;
use App\Http\Resources\BlogResource;
use App\Http\Resources\FaqResource;
use App\Http\Resources\ProjectResource;
use App\Http\Resources\ServiceResource;
use App\Http\Resources\SliderResource;
use App\Http\Resources\StatisticResource;
use App\Services\AboutPageService;
use App\Services\BlogService;
use App\Services\FaqService;
use App\Services\ProjectService;
use App\Services\ServiceService;
use App\Services\SiteSettingService;
use App\Services\SliderService;
use App\Services\StatisticService;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __construct(
        private readonly SliderService $sliderService,
        private readonly AboutPageService $aboutPageService,
        private readonly ServiceService $serviceService,
        private readonly ProjectService $projectService,
        private readonly StatisticService $statisticService,
        private readonly BlogService $blogService,
        private readonly FaqService $faqService,
        private readonly SiteSettingService $siteSettingService,
    ) {}

    public function index(): Response
    {
        return Inertia::render('Home', [
            'sliders' => SliderResource::collection($this->sliderService->allActive())->resolve(),
            'about' => (new AboutPageResource($this->aboutPageService->get()))->resolve(),
            'services' => ServiceResource::collection($this->serviceService->forHomeLimited(3))->resolve(),
            'projects' => ProjectResource::collection($this->projectService->latestForHome(2))->resolve(),
            'statistics' => StatisticResource::collection($this->statisticService->all())->resolve(),
            'blogs' => BlogResource::collection($this->blogService->latestForHome(3))->resolve(),
            'faqs' => FaqResource::collection($this->faqService->all())->resolve(),
        ])->withViewData([
            'seo' => $this->siteSettingService->documentSeo(),
        ]);
    }
}
