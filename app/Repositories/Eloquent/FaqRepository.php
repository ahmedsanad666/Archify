<?php

namespace App\Repositories\Eloquent;

use App\Models\Faq;
use App\Repositories\Contracts\FaqRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class FaqRepository implements FaqRepositoryInterface
{
    private const ADMIN_WITH = ['translations.language'];

    public function find(int $id): ?Faq
    {
        return Faq::query()->with(self::ADMIN_WITH)->find($id);
    }

    public function all(): Collection
    {
        return Faq::query()->with(self::ADMIN_WITH)->get();
    }

    public function create(array $data): Faq
    {
        return Faq::query()->create($data);
    }

    public function update(Faq $faq, array $data): Faq
    {
        $faq->update($data);

        return $faq->fresh(self::ADMIN_WITH);
    }

    public function delete(Faq $faq): void
    {
        $faq->delete();
    }

    /**
     * @param  array<int, int>  $orderedIds
     */
    public function reorder(array $orderedIds): void
    {
        foreach ($orderedIds as $index => $id) {
            Faq::query()->whereKey($id)->update(['order' => $index]);
        }
    }
}
