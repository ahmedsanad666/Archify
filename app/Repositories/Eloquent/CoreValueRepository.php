<?php

namespace App\Repositories\Eloquent;

use App\Models\CoreValue;
use App\Repositories\Contracts\CoreValueRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class CoreValueRepository implements CoreValueRepositoryInterface
{
    public function find(int $id): ?CoreValue
    {
        return CoreValue::query()->with(['translations.language'])->find($id);
    }

    public function all(): Collection
    {
        return CoreValue::query()->with(['translations.language'])->get();
    }
}
