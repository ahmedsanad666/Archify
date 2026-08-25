<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReorderProjectCategoriesRequest;
use App\Http\Requests\Admin\StoreProjectCategoryRequest;
use App\Http\Requests\Admin\UpdateProjectCategoryRequest;
use App\Http\Resources\ProjectCategoryResource;
use App\Models\ProjectCategory;
use App\Services\ProjectCategoryService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ProjectCategoryController extends Controller
{
    public function __construct(
        private readonly ProjectCategoryService $projectCategoryService,
    ) {}

    public function index(): Response
    {
        $categories = $this->projectCategoryService->all();

        return Inertia::render('Admin/Categories/Index', [
            'categories' => ProjectCategoryResource::collection($categories)->resolve(),
        ]);
    }

    public function store(StoreProjectCategoryRequest $request): RedirectResponse
    {
        $this->projectCategoryService->create($request->validated());

        return redirect()
            ->route('admin.project-categories.index')
            ->with('success', 'Category created.');
    }

    public function update(UpdateProjectCategoryRequest $request, ProjectCategory $projectCategory): RedirectResponse
    {
        $this->projectCategoryService->update($projectCategory, $request->validated());

        return redirect()
            ->route('admin.project-categories.index')
            ->with('success', 'Category updated.');
    }

    public function destroy(ProjectCategory $projectCategory): RedirectResponse
    {
        $this->projectCategoryService->delete($projectCategory);

        return redirect()
            ->route('admin.project-categories.index')
            ->with('success', 'Category deleted.');
    }

    public function reorder(ReorderProjectCategoriesRequest $request): RedirectResponse
    {
        $this->projectCategoryService->reorder($request->validated('ids'));

        return redirect()
            ->route('admin.project-categories.index')
            ->with('success', 'Category order saved.');
    }
}
