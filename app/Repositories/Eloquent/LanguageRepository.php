<?php

namespace App\Repositories\Eloquent;

use App\Models\Language;
use App\Repositories\Contracts\LanguageRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class LanguageRepository implements LanguageRepositoryInterface
{
    public function find(int $id): ?Language
    {
        return Language::query()->find($id);
    }

    public function all(): Collection
    {
        return Language::query()->get();
    }

    public function findByCode(string $code): ?Language
    {
        return Language::query()->where('code', $code)->first();
    }

    public function allActive(): Collection
    {
        return Language::query()->active()->get();
    }
}
