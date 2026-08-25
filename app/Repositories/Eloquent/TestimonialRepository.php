<?php

namespace App\Repositories\Eloquent;

use App\Models\Testimonial;
use App\Repositories\Contracts\TestimonialRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class TestimonialRepository implements TestimonialRepositoryInterface
{
    public function find(int $id): ?Testimonial
    {
        return Testimonial::query()->with(['translations.language', 'media'])->find($id);
    }

    public function all(): Collection
    {
        return Testimonial::query()->with(['translations.language', 'media'])->get();
    }
}
