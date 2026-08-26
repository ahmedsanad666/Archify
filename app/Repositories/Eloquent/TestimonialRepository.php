<?php

namespace App\Repositories\Eloquent;

use App\Models\Testimonial;
use App\Repositories\Contracts\TestimonialRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class TestimonialRepository implements TestimonialRepositoryInterface
{
    private const ADMIN_WITH = ['translations.language', 'media'];

    public function find(int $id): ?Testimonial
    {
        return Testimonial::query()->with(self::ADMIN_WITH)->find($id);
    }

    public function all(): Collection
    {
        return Testimonial::query()->with(self::ADMIN_WITH)->get();
    }

    public function create(array $data): Testimonial
    {
        return Testimonial::query()->create($data);
    }

    public function update(Testimonial $testimonial, array $data): Testimonial
    {
        $testimonial->update($data);

        return $testimonial->fresh(self::ADMIN_WITH);
    }

    public function delete(Testimonial $testimonial): void
    {
        $testimonial->delete();
    }

    /**
     * @param  array<int, int>  $orderedIds
     */
    public function reorder(array $orderedIds): void
    {
        foreach ($orderedIds as $index => $id) {
            Testimonial::query()->whereKey($id)->update(['order' => $index]);
        }
    }
}
