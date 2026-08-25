<?php

namespace App\Repositories\Contracts;

use App\Models\Language;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface LanguageRepositoryInterface
{
    public function find(int $id): ?Language;

    public function all(): Collection;

    public function findByCode(string $code): ?Language;

    public function allActive(): Collection;
}
