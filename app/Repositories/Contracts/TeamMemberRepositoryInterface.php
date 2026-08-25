<?php

namespace App\Repositories\Contracts;

use App\Models\TeamMember;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface TeamMemberRepositoryInterface
{
    public function find(int $id): ?TeamMember;

    public function all(): Collection;
}
