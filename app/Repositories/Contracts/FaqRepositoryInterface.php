<?php

namespace App\Repositories\Contracts;

use App\Models\Faq;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface FaqRepositoryInterface
{
    public function find(int $id): ?Faq;

    public function all(): Collection;
}
