<?php

namespace App\Repositories\Contracts;

use App\Models\TeamMember;
use Illuminate\Database\Eloquent\Collection;

interface TeamMemberRepositoryInterface
{
    public function find(int $id): ?TeamMember;

    public function all(): Collection;

    public function create(array $data): TeamMember;

    public function update(TeamMember $member, array $data): TeamMember;

    public function delete(TeamMember $member): void;

    /**
     * @param  array<int, int>  $orderedIds
     */
    public function reorder(array $orderedIds): void;
}
