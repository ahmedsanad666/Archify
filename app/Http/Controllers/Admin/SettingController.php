<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateSettingsRequest;
use App\Http\Resources\SiteSettingResource;
use App\Services\SiteSettingService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class SettingController extends Controller
{
    public function __construct(
        private readonly SiteSettingService $siteSettingService,
    ) {}

    public function edit(): Response
    {
        $settings = $this->siteSettingService->getForAdmin();

        return Inertia::render('Admin/Settings/Index', [
            'settings' => (new SiteSettingResource($settings))->resolve(),
        ]);
    }

    public function update(UpdateSettingsRequest $request): RedirectResponse
    {
        $this->siteSettingService->update($request->validated());

        return redirect()
            ->route('admin.settings.edit')
            ->with('success', 'Settings saved.');
    }
}
