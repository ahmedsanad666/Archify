<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateAboutPageRequest;
use App\Http\Resources\AboutPageResource;
use App\Services\AboutPageService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class AboutPageController extends Controller
{
    public function __construct(
        private readonly AboutPageService $aboutPageService,
    ) {}

    public function edit(): Response
    {
        $about = $this->aboutPageService->getForAdmin();

        return Inertia::render('Admin/About/Index', [
            'about' => (new AboutPageResource($about))->resolve(),
        ]);
    }

    public function update(UpdateAboutPageRequest $request): RedirectResponse
    {
        $this->aboutPageService->update($request->validated());

        return redirect()
            ->route('admin.about.edit')
            ->with('success', 'About page saved.');
    }
}
