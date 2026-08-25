<?php

namespace App\Repositories\Contracts;

use App\Models\Testimonial;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface TestimonialRepositoryInterface
{
    public function find(int $id): ?Testimonial;

    public function all(): Collection;
}
