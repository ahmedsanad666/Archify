<?php

namespace App\Http\Controllers;

use App\Http\Resources\AboutPageResource;
use App\Http\Resources\CoreValueResource;
use App\Http\Resources\ProjectResource;
use App\Http\Resources\ServiceResource;
use App\Http\Resources\SliderResource;
use App\Http\Resources\StatisticResource;
use App\Http\Resources\TestimonialResource;
use App\Services\AboutPageService;
use App\Services\CoreValueService;
use App\Services\ProjectService;
use App\Services\ServiceService;
use App\Services\SliderService;
use App\Services\StatisticService;
use App\Services\TestimonialService;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __construct(
        private readonly SliderService $sliderService,
        private readonly AboutPageService $aboutPageService,
        private readonly ServiceService $serviceService,
        private readonly ProjectService $projectService,
        private readonly TestimonialService $testimonialService,
        private readonly StatisticService $statisticService,
        private readonly CoreValueService $coreValueService,
    ) {}

    public function index(): Response
    {
        return Inertia::render('Home', [
            'sliders' => SliderResource::collection($this->sliderService->allActive())->resolve(),
            'about' => (new AboutPageResource($this->aboutPageService->get()))->resolve(),
            'services' => ServiceResource::collection($this->serviceService->forHome())->resolve(),
            'projects' => ProjectResource::collection($this->projectService->latestForHome(6))->resolve(),
            'testimonials' => TestimonialResource::collection($this->testimonialService->all())->resolve(),
            'statistics' => StatisticResource::collection($this->statisticService->all())->resolve(),
            'coreValues' => CoreValueResource::collection($this->coreValueService->all())->resolve(),
        ]);
    }
}
