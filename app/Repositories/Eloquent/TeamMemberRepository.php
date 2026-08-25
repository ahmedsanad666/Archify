<?php

namespace App\Repositories\Eloquent;

use App\Models\TeamMember;
use App\Repositories\Contracts\TeamMemberRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class TeamMemberRepository implements TeamMemberRepositoryInterface
{
    public function find(int $id): ?TeamMember
    {
        return TeamMember::query()->with(['translations.language', 'media'])->find($id);
    }

    public function all(): Collection
    {
        return TeamMember::query()->with(['translations.language', 'media'])->get();
    }

    public function create(array $data): TeamMember
    {
        return TeamMember::query()->create($data);
    }

    public function update(TeamMember $member, array $data): TeamMember
    {
        $member->update($data);

        return $member->fresh(['translations.language', 'media']);
    }

    public function delete(TeamMember $member): void
    {
        $member->delete();
    }

    /**
     * @param  array<int, int>  $orderedIds
     */
    public function reorder(array $orderedIds): void
    {
        foreach ($orderedIds as $index => $id) {
            TeamMember::query()->whereKey($id)->update(['order' => $index]);
        }
    }
}
