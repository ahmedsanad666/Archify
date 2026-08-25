<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreConceptRequest;
use App\Http\Requests\Admin\UpdateConceptRequest;
use App\Http\Resources\ConceptResource;
use App\Models\Concept;
use App\Services\ConceptService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ConceptController extends Controller
{
    public function __construct(
        private readonly ConceptService $conceptService,
    ) {}

    public function index(): Response
    {
        $concepts = $this->conceptService->all();

        return Inertia::render('Admin/Concepts/Index', [
            'concepts' => ConceptResource::collection($concepts)->resolve(),
        ]);
    }

    public function store(StoreConceptRequest $request): RedirectResponse
    {
        $this->conceptService->create($request->validated());

        return redirect()
            ->route('admin.concepts.index')
            ->with('success', 'Concept created.');
    }

    public function update(UpdateConceptRequest $request, Concept $concept): RedirectResponse
    {
        $this->conceptService->update($concept, $request->validated());

        return redirect()
            ->route('admin.concepts.index')
            ->with('success', 'Concept updated.');
    }

    public function destroy(Concept $concept): RedirectResponse
    {
        $this->conceptService->delete($concept);

        return redirect()
            ->route('admin.concepts.index')
            ->with('success', 'Concept deleted.');
    }
}
