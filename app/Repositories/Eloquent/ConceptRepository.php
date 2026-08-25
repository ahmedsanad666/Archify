<?php

namespace App\Repositories\Eloquent;

use App\Models\Concept;
use App\Repositories\Contracts\ConceptRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class ConceptRepository implements ConceptRepositoryInterface
{
    public function find(int $id): ?Concept
    {
        return Concept::query()->with(['translations.language'])->find($id);
    }

    public function all(): Collection
    {
        return Concept::query()->with(['translations.language'])->latest()->get();
    }

    public function create(array $data): Concept
    {
        return Concept::query()->create($data);
    }

    public function update(Concept $concept, array $data): Concept
    {
        $concept->update($data);

        return $concept->fresh(['translations.language']);
    }

    public function delete(Concept $concept): void
    {
        $concept->delete();
    }
}
