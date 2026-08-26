<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreBlogRequest;
use App\Http\Requests\Admin\UpdateBlogRequest;
use App\Http\Resources\BlogCategoryResource;
use App\Http\Resources\BlogResource;
use App\Models\Blog;
use App\Services\BlogCategoryService;
use App\Services\BlogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BlogController extends Controller
{
    public function __construct(
        private readonly BlogService $blogService,
        private readonly BlogCategoryService $blogCategoryService,
    ) {}

    public function index(Request $request): Response
    {
        $categoryId = $request->query('category');
        $categoryId = is_numeric($categoryId) ? (int) $categoryId : null;

        $blogs = $this->blogService->paginate($categoryId, 15);

        return Inertia::render('Admin/Blogs/Index', [
            'blogs' => BlogResource::collection($blogs),
            'categories' => BlogCategoryResource::collection(
                $this->blogCategoryService->all(),
            )->resolve(),
            'filters' => [
                'category' => $categoryId,
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Blogs/Form', [
            'blog' => null,
            'categories' => BlogCategoryResource::collection(
                $this->blogCategoryService->all(),
            )->resolve(),
        ]);
    }

    public function store(StoreBlogRequest $request): RedirectResponse
    {
        $this->blogService->create($request->validated());

        return redirect()
            ->route('admin.blogs.index')
            ->with('success', 'Blog post created.');
    }

    public function edit(Blog $blog): Response
    {
        $blog = $this->blogService->find($blog->id);

        return Inertia::render('Admin/Blogs/Form', [
            'blog' => (new BlogResource($blog))->resolve(),
            'categories' => BlogCategoryResource::collection(
                $this->blogCategoryService->all(),
            )->resolve(),
        ]);
    }

    public function update(UpdateBlogRequest $request, Blog $blog): RedirectResponse
    {
        $this->blogService->update($blog, $request->validated());

        return redirect()
            ->route('admin.blogs.index')
            ->with('success', 'Blog post updated.');
    }

    public function destroy(Blog $blog): RedirectResponse
    {
        $this->blogService->delete($blog);

        return redirect()
            ->route('admin.blogs.index')
            ->with('success', 'Blog post deleted.');
    }
}
