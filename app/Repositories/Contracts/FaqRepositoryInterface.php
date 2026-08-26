<?php

namespace App\Repositories\Contracts;

use App\Models\Faq;
use Illuminate\Database\Eloquent\Collection;

interface FaqRepositoryInterface
{
    public function find(int $id): ?Faq;

    public function all(): Collection;

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Faq;

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Faq $faq, array $data): Faq;

    public function delete(Faq $faq): void;

    /**
     * @param  array<int, int>  $orderedIds
     */
    public function reorder(array $orderedIds): void;
}
