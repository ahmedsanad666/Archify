<?php

namespace App\Http\Controllers;

use App\Http\Resources\FaqResource;
use App\Services\FaqService;
use Inertia\Inertia;
use Inertia\Response;

class FaqController extends Controller
{
    public function __construct(
        private readonly FaqService $faqService,
    ) {}

    public function index(): Response
    {
        return Inertia::render('Faq', [
            'faqs' => FaqResource::collection($this->faqService->all())->resolve(),
        ]);
    }
}
