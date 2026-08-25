<?php

namespace App\Http\Controllers;

use App\Http\Resources\AboutPageResource;
use App\Http\Resources\CoreValueResource;
use App\Http\Resources\StatisticResource;
use App\Services\AboutPageService;
use App\Services\CoreValueService;
use App\Services\StatisticService;
use Inertia\Inertia;
use Inertia\Response;

class AboutController extends Controller
{
    public function __construct(
        private readonly AboutPageService $aboutPageService,
        private readonly StatisticService $statisticService,
        private readonly CoreValueService $coreValueService,
    ) {}

    public function index(): Response
    {
        return Inertia::render('About', [
            'about' => (new AboutPageResource($this->aboutPageService->get()))->resolve(),
            'statistics' => StatisticResource::collection($this->statisticService->all())->resolve(),
            'coreValues' => CoreValueResource::collection($this->coreValueService->all())->resolve(),
        ]);
    }
}
