<?php

namespace App\Services;

use App\Repositories\Contracts\TestimonialRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class TestimonialService
{
    public function __construct(
        private readonly TestimonialRepositoryInterface $testimonialRepository,
    ) {}

    public function all(): Collection
    {
        return $this->testimonialRepository->all();
    }
}
