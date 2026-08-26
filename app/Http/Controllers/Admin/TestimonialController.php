<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReorderTestimonialsRequest;
use App\Http\Requests\Admin\StoreTestimonialRequest;
use App\Http\Requests\Admin\UpdateTestimonialRequest;
use App\Http\Resources\TestimonialResource;
use App\Models\Testimonial;
use App\Services\TestimonialService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class TestimonialController extends Controller
{
    public function __construct(
        private readonly TestimonialService $testimonialService,
    ) {}

    public function index(): Response
    {
        $testimonials = $this->testimonialService->all();

        return Inertia::render('Admin/Testimonials/Index', [
            'testimonials' => TestimonialResource::collection($testimonials)->resolve(),
        ]);
    }

    public function store(StoreTestimonialRequest $request): RedirectResponse
    {
        $this->testimonialService->create($request->validated());

        return redirect()
            ->route('admin.testimonials.index')
            ->with('success', 'Testimonial created.');
    }

    public function update(UpdateTestimonialRequest $request, Testimonial $testimonial): RedirectResponse
    {
        $this->testimonialService->update($testimonial, $request->validated());

        return redirect()
            ->route('admin.testimonials.index')
            ->with('success', 'Testimonial updated.');
    }

    public function destroy(Testimonial $testimonial): RedirectResponse
    {
        $this->testimonialService->delete($testimonial);

        return redirect()
            ->route('admin.testimonials.index')
            ->with('success', 'Testimonial deleted.');
    }

    public function reorder(ReorderTestimonialsRequest $request): RedirectResponse
    {
        $this->testimonialService->reorder($request->validated('ids'));

        return redirect()
            ->route('admin.testimonials.index')
            ->with('success', 'Testimonial order updated.');
    }
}
