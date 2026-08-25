<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProjectRequest;
use App\Http\Requests\Admin\UpdateProjectRequest;
use App\Http\Resources\ProjectCategoryResource;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use App\Repositories\Contracts\ConceptRepositoryInterface;
use App\Services\ProjectCategoryService;
use App\Services\ProjectService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ProjectController extends Controller
{
    public function __construct(
        private readonly ProjectService $projectService,
        private readonly ProjectCategoryService $projectCategoryService,
        private readonly ConceptRepositoryInterface $conceptRepository,
    ) {}

    public function index(): Response
    {
        $projects = $this->projectService->paginate();

        return Inertia::render('Admin/Projects/Index', [
            'projects' => ProjectResource::collection($projects),
            'categories' => ProjectCategoryResource::collection(
                $this->projectCategoryService->all(),
            )->resolve(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Projects/Form', [
            'project' => null,
            'categories' => ProjectCategoryResource::collection(
                $this->projectCategoryService->all(),
            )->resolve(),
            'concepts' => $this->conceptsForForm(),
        ]);
    }

    public function store(StoreProjectRequest $request): RedirectResponse
    {
        $this->projectService->create($request->validated());

        return redirect()
            ->route('admin.projects.index')
            ->with('success', 'Project created.');
    }

    public function edit(Project $project): Response
    {
        $project = $this->projectService->find($project->id);

        return Inertia::render('Admin/Projects/Form', [
            'project' => (new ProjectResource($project))->resolve(),
            'categories' => ProjectCategoryResource::collection(
                $this->projectCategoryService->all(),
            )->resolve(),
            'concepts' => $this->conceptsForForm(),
        ]);
    }

    public function update(UpdateProjectRequest $request, Project $project): RedirectResponse
    {
        $this->projectService->update($project, $request->validated());

        return redirect()
            ->route('admin.projects.index')
            ->with('success', 'Project updated.');
    }

    public function destroy(Project $project): RedirectResponse
    {
        $this->projectService->delete($project);

        return redirect()
            ->route('admin.projects.index')
            ->with('success', 'Project deleted.');
    }

    /**
     * @return array<int, array{id: int, icon: string|null, title: string|null}>
     */
    private function conceptsForForm(): array
    {
        return $this->conceptRepository->all()->map(function ($concept) {
            $title = null;
            if ($concept->relationLoaded('translations')) {
                $title = $concept->translations
                    ->first(fn ($t) => filled($t->title))?->title;
            }

            return [
                'id' => $concept->id,
                'icon' => $concept->icon,
                'title' => $title,
            ];
        })->values()->all();
    }
}
