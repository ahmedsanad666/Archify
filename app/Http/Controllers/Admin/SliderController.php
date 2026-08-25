<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReorderSlidersRequest;
use App\Http\Requests\Admin\StoreSliderRequest;
use App\Http\Requests\Admin\UpdateSliderRequest;
use App\Http\Resources\SliderResource;
use App\Models\Slider;
use App\Services\SliderService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class SliderController extends Controller
{
    public function __construct(
        private readonly SliderService $sliderService,
    ) {}

    public function index(): Response
    {
        $sliders = $this->sliderService->all();

        return Inertia::render('Admin/HomeBuilder/Index', [
            'sliders' => SliderResource::collection($sliders)->resolve(),
        ]);
    }

    public function store(StoreSliderRequest $request): RedirectResponse
    {
        $this->sliderService->create($request->validated());

        return redirect()
            ->route('admin.sliders.index')
            ->with('success', 'Slider created.');
    }

    public function update(UpdateSliderRequest $request, Slider $slider): RedirectResponse
    {
        $this->sliderService->update($slider, $request->validated());

        return redirect()
            ->route('admin.sliders.index')
            ->with('success', 'Slider updated.');
    }

    public function destroy(Slider $slider): RedirectResponse
    {
        $this->sliderService->delete($slider);

        return redirect()
            ->route('admin.sliders.index')
            ->with('success', 'Slider deleted.');
    }

    public function reorder(ReorderSlidersRequest $request): RedirectResponse
    {
        $this->sliderService->reorder($request->validated('ids'));

        return redirect()
            ->route('admin.sliders.index')
            ->with('success', 'Slide order saved.');
    }
}
