<?php

namespace App\Repositories\Eloquent;

use App\Models\TeamMember;
use App\Repositories\Contracts\TeamMemberRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class TeamMemberRepository implements TeamMemberRepositoryInterface
{
    public function find(int $id): ?TeamMember
    {
        return TeamMember::query()->find($id);
    }

    public function all(): Collection
    {
        return TeamMember::query()->get();
    }
}
