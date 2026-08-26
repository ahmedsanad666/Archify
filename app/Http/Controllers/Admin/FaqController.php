<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReorderFaqsRequest;
use App\Http\Requests\Admin\StoreFaqRequest;
use App\Http\Requests\Admin\UpdateFaqRequest;
use App\Http\Resources\FaqResource;
use App\Models\Faq;
use App\Services\FaqService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class FaqController extends Controller
{
    public function __construct(
        private readonly FaqService $faqService,
    ) {}

    public function index(): Response
    {
        $faqs = $this->faqService->all();

        return Inertia::render('Admin/Faqs/Index', [
            'faqs' => FaqResource::collection($faqs)->resolve(),
        ]);
    }

    public function store(StoreFaqRequest $request): RedirectResponse
    {
        $this->faqService->create($request->validated());

        return redirect()
            ->route('admin.faqs.index')
            ->with('success', 'FAQ created.');
    }

    public function update(UpdateFaqRequest $request, Faq $faq): RedirectResponse
    {
        $this->faqService->update($faq, $request->validated());

        return redirect()
            ->route('admin.faqs.index')
            ->with('success', 'FAQ updated.');
    }

    public function destroy(Faq $faq): RedirectResponse
    {
        $this->faqService->delete($faq);

        return redirect()
            ->route('admin.faqs.index')
            ->with('success', 'FAQ deleted.');
    }

    public function reorder(ReorderFaqsRequest $request): RedirectResponse
    {
        $this->faqService->reorder($request->validated('ids'));

        return redirect()
            ->route('admin.faqs.index')
            ->with('success', 'FAQ order updated.');
    }
}
