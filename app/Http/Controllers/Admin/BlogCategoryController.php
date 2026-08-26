<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReorderBlogCategoriesRequest;
use App\Http\Requests\Admin\StoreBlogCategoryRequest;
use App\Http\Requests\Admin\UpdateBlogCategoryRequest;
use App\Http\Resources\BlogCategoryResource;
use App\Models\BlogCategory;
use App\Services\BlogCategoryService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class BlogCategoryController extends Controller
{
    public function __construct(
        private readonly BlogCategoryService $blogCategoryService,
    ) {}

    public function index(): Response
    {
        $categories = $this->blogCategoryService->all();

        return Inertia::render('Admin/BlogCategories/Index', [
            'categories' => BlogCategoryResource::collection($categories)->resolve(),
        ]);
    }

    public function store(StoreBlogCategoryRequest $request): RedirectResponse
    {
        $this->blogCategoryService->create($request->validated());

        return redirect()
            ->route('admin.blog-categories.index')
            ->with('success', 'Category created.');
    }

    public function update(UpdateBlogCategoryRequest $request, BlogCategory $blogCategory): RedirectResponse
    {
        $this->blogCategoryService->update($blogCategory, $request->validated());

        return redirect()
            ->route('admin.blog-categories.index')
            ->with('success', 'Category updated.');
    }

    public function destroy(BlogCategory $blogCategory): RedirectResponse
    {
        $this->blogCategoryService->delete($blogCategory);

        return redirect()
            ->route('admin.blog-categories.index')
            ->with('success', 'Category deleted.');
    }

    public function reorder(ReorderBlogCategoriesRequest $request): RedirectResponse
    {
        $this->blogCategoryService->reorder($request->validated('ids'));

        return redirect()
            ->route('admin.blog-categories.index')
            ->with('success', 'Category order saved.');
    }
}
