<?php

namespace App\Repositories\Contracts;

use App\Models\Concept;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface ConceptRepositoryInterface
{
    public function find(int $id): ?Concept;

    public function all(): Collection;
}
