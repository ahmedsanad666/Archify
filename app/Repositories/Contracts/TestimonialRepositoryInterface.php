<?php

namespace App\Repositories\Contracts;

use App\Models\Testimonial;
use Illuminate\Database\Eloquent\Collection;

interface TestimonialRepositoryInterface
{
    public function find(int $id): ?Testimonial;

    public function all(): Collection;

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Testimonial;

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Testimonial $testimonial, array $data): Testimonial;

    public function delete(Testimonial $testimonial): void;

    /**
     * @param  array<int, int>  $orderedIds
     */
    public function reorder(array $orderedIds): void;
}
