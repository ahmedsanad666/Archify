<?php

namespace App\Repositories\Eloquent;

use App\Models\Faq;
use App\Repositories\Contracts\FaqRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class FaqRepository implements FaqRepositoryInterface
{
    public function find(int $id): ?Faq
    {
        return Faq::query()->find($id);
    }

    public function all(): Collection
    {
        return Faq::query()->get();
    }
}
