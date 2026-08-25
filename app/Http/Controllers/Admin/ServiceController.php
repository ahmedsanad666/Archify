<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReorderServicesRequest;
use App\Http\Requests\Admin\StoreServiceRequest;
use App\Http\Requests\Admin\UpdateServiceRequest;
use App\Http\Resources\ServiceResource;
use App\Models\Service;
use App\Services\ServiceService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ServiceController extends Controller
{
    public function __construct(
        private readonly ServiceService $serviceService,
    ) {}

    public function index(): Response
    {
        $services = $this->serviceService->all();

        return Inertia::render('Admin/Services/Index', [
            'services' => ServiceResource::collection($services)->resolve(),
        ]);
    }

    public function store(StoreServiceRequest $request): RedirectResponse
    {
        $this->serviceService->create($request->validated());

        return redirect()
            ->route('admin.services.index')
            ->with('success', 'Service created.');
    }

    public function update(UpdateServiceRequest $request, Service $service): RedirectResponse
    {
        $this->serviceService->update($service, $request->validated());

        return redirect()
            ->route('admin.services.index')
            ->with('success', 'Service updated.');
    }

    public function destroy(Service $service): RedirectResponse
    {
        $this->serviceService->delete($service);

        return redirect()
            ->route('admin.services.index')
            ->with('success', 'Service deleted.');
    }

    public function reorder(ReorderServicesRequest $request): RedirectResponse
    {
        $this->serviceService->reorder($request->validated('ids'));

        return redirect()
            ->route('admin.services.index')
            ->with('success', 'Service order saved.');
    }
}
