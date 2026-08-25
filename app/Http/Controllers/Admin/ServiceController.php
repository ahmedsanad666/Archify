<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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
        $services = $this->serviceService->paginate();

        return Inertia::render('Admin/Services/Index', [
            'services' => ServiceResource::collection($services),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Services/Form', [
            'service' => null,
        ]);
    }

    public function store(StoreServiceRequest $request): RedirectResponse
    {
        $this->serviceService->create($request->validated());

        return redirect()
            ->route('admin.services.index')
            ->with('success', 'Service created.');
    }

    public function edit(Service $service): Response
    {
        $service = $this->serviceService->find($service->id);

        return Inertia::render('Admin/Services/Form', [
            'service' => (new ServiceResource($service))->resolve(),
        ]);
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
}
