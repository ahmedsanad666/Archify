<?php

namespace App\Http\Controllers;

use App\Http\Resources\BlogResource;
use App\Services\BlogService;
use App\Services\SiteSettingService;
use App\Support\UiTranslations;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BlogController extends Controller
{
    public function __construct(
        private readonly BlogService $blogService,
        private readonly SiteSettingService $siteSettingService,
    ) {}

    public function index(Request $request): Response
    {
        $categorySlug = $request->string('category')->trim()->toString();
        $categorySlug = $categorySlug !== '' ? $categorySlug : null;

        $result = $this->blogService->paginatePublic($categorySlug, 9);

        $ui = UiTranslations::flatten(UiTranslations::forLocale(app()->getLocale()));
        $pageTitle = $ui['public.blogs.title'] ?? $ui['nav.blog'] ?? 'Journal';

        return Inertia::render('Blog/Index', [
            'blogs' => BlogResource::collection($result['blogs']),
            'featured' => $result['featured']
                ? (new BlogResource($result['featured']))->resolve()
                : null,
            'filters' => [
                'category' => $categorySlug,
            ],
        ])->withViewData([
            'seo' => $this->siteSettingService->documentSeo([
                'page_title' => $pageTitle,
            ]),
        ]);
    }

    public function show(string $slug): Response
    {
        $blog = $this->blogService->findBySlugOrFail($slug);
        $this->blogService->recordView($blog);

        $resource = (new BlogResource($blog))->resolve();
        $locale = app()->getLocale();
        $translation = $resource['translations'][$locale] ?? [];

        $pageTitle = $translation['meta_title']
            ?? $translation['title']
            ?? $resource['title']
            ?? 'Journal';

        $description = trim((string) ($translation['meta_description'] ?? ''));
        if ($description === '') {
            $description = trim((string) ($resource['excerpt'] ?? ''));
        }

        $keywords = $this->siteSettingService->mergeKeywords(
            $translation['meta_keywords'] ?? null,
            data_get($resource, 'category.name'),
        );

        $ogImage = $resource['cover_url']
            ?? $resource['thumbnail_url']
            ?? null;

        $seo = [
            'page_title' => $pageTitle,
            'keywords' => $keywords,
            'og_image' => $ogImage,
        ];

        if ($description !== '') {
            $seo['description'] = $description;
        }

        return Inertia::render('Blog/Show', [
            'blog' => $resource,
        ])->withViewData([
            'seo' => $this->siteSettingService->documentSeo($seo),
        ]);
    }
}
