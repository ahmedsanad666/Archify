<?php

namespace App\Repositories\Eloquent;

use App\Models\Concept;
use App\Repositories\Contracts\ConceptRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ConceptRepository implements ConceptRepositoryInterface
{
    public function find(int $id): ?Concept
    {
        return Concept::query()->find($id);
    }

    public function all(): Collection
    {
        return Concept::query()->get();
    }
}
