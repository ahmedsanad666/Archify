<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLeadRequest;
use App\Http\Resources\FaqResource;
use App\Http\Resources\ServiceResource;
use App\Services\FaqService;
use App\Services\LeadService;
use App\Services\ServiceService;
use App\Services\SiteSettingService;
use App\Support\UiTranslations;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ContactController extends Controller
{
    public function __construct(
        private readonly LeadService $leadService,
        private readonly ServiceService $serviceService,
        private readonly FaqService $faqService,
        private readonly SiteSettingService $siteSettingService,
    ) {}

    public function index(): Response
    {
        $ui = UiTranslations::flatten(UiTranslations::forLocale(app()->getLocale()));
        $pageTitle = $ui['public.contact.title'] ?? $ui['nav.contact'] ?? 'Contact';

        return Inertia::render('Contact', [
            'services' => ServiceResource::collection(
                $this->serviceService->all(),
            )->resolve(),
            'faqs' => FaqResource::collection(
                $this->faqService->all(),
            )->resolve(),
        ])->withViewData([
            'seo' => $this->siteSettingService->documentSeo([
                'page_title' => $pageTitle,
            ]),
        ]);
    }

    public function store(StoreLeadRequest $request): RedirectResponse
    {
        $this->leadService->create($request->validated(), $request);

        return redirect()
            ->back()
            ->with('contact_success', true);
    }
}
