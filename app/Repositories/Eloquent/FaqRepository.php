<?php

namespace App\Repositories\Eloquent;

use App\Models\Faq;
use App\Repositories\Contracts\FaqRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class FaqRepository implements FaqRepositoryInterface
{
    public function find(int $id): ?Faq
    {
        return Faq::query()->with(['translations.language'])->find($id);
    }

    public function all(): Collection
    {
        return Faq::query()->with(['translations.language'])->get();
    }
}
