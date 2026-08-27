<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProjectResource;
use App\Services\ProjectService;
use App\Services\SiteSettingService;
use App\Support\UiTranslations;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProjectController extends Controller
{
    public function __construct(
        private readonly ProjectService $projectService,
        private readonly SiteSettingService $siteSettingService,
    ) {}

    public function index(Request $request): Response
    {
        $categorySlug = $request->string('category')->trim()->toString();
        $categorySlug = $categorySlug !== '' ? $categorySlug : null;

        $ui = UiTranslations::flatten(UiTranslations::forLocale(app()->getLocale()));
        $pageTitle = $ui['public.projects.title'] ?? $ui['nav.work'] ?? 'Our Work';

        return Inertia::render('Projects/Index', [
            'projects' => ProjectResource::collection(
                $this->projectService->paginatePublic($categorySlug, 6),
            ),
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
        $project = $this->projectService->findBySlugOrFail($slug);
        $this->projectService->recordView($project);

        $resource = (new ProjectResource($project))->resolve();
        $locale = app()->getLocale();
        $translation = $resource['translations'][$locale] ?? [];

        $pageTitle = $translation['meta_title']
            ?? $translation['name']
            ?? $resource['name']
            ?? 'Project';

        $description = trim((string) ($translation['meta_description'] ?? ''));
        if ($description === '') {
            $description = trim((string) ($translation['short_description'] ?? ''));
        }

        $keywords = $this->siteSettingService->mergeKeywords(
            $translation['meta_keywords'] ?? null,
            data_get($resource, 'category.name'),
        );

        $seo = [
            'page_title' => $pageTitle,
            'keywords' => $keywords,
            'og_image' => $resource['thumbnail_url'] ?? null,
        ];

        if ($description !== '') {
            $seo['description'] = $description;
        }

        return Inertia::render('Projects/Show', [
            'project' => $resource,
        ])->withViewData([
            'seo' => $this->siteSettingService->documentSeo($seo),
        ]);
    }
}
