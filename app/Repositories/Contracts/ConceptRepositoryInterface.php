<?php

namespace App\Repositories\Contracts;

use App\Models\Concept;
use Illuminate\Database\Eloquent\Collection;

interface ConceptRepositoryInterface
{
    public function find(int $id): ?Concept;

    public function all(): Collection;

    public function create(array $data): Concept;

    public function update(Concept $concept, array $data): Concept;

    public function delete(Concept $concept): void;
}
