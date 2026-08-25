<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateAboutPageRequest;
use App\Http\Resources\AboutPageResource;
use App\Http\Resources\CoreValueResource;
use App\Http\Resources\StatisticResource;
use App\Services\AboutPageService;
use App\Services\CoreValueService;
use App\Services\StatisticService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AboutPageController extends Controller
{
    public function __construct(
        private readonly AboutPageService $aboutPageService,
        private readonly StatisticService $statisticService,
        private readonly CoreValueService $coreValueService,
    ) {}

    public function edit(Request $request): Response
    {
        $about = $this->aboutPageService->getForAdmin();
        $statistics = $this->statisticService->all();
        $coreValues = $this->coreValueService->all();

        return Inertia::render('Admin/About/Index', [
            'about' => (new AboutPageResource($about))->resolve(),
            'statistics' => StatisticResource::collection($statistics)->resolve(),
            'coreValues' => CoreValueResource::collection($coreValues)->resolve(),
            'tab' => $request->query('tab', 'story'),
        ]);
    }

    public function update(UpdateAboutPageRequest $request): RedirectResponse
    {
        $this->aboutPageService->update($request->validated());

        return redirect()
            ->route('admin.about.edit', [
                'tab' => $request->validated('active_tab', 'story'),
            ])
            ->with('success', 'About page saved.');
    }
}
