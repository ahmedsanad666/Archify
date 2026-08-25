<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReorderTeamMembersRequest;
use App\Http\Requests\Admin\StoreTeamMemberRequest;
use App\Http\Requests\Admin\UpdateTeamMemberRequest;
use App\Http\Resources\TeamMemberResource;
use App\Models\TeamMember;
use App\Services\TeamMemberService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class TeamMemberController extends Controller
{
    public function __construct(
        private readonly TeamMemberService $teamMemberService,
    ) {}

    public function index(): Response
    {
        $members = $this->teamMemberService->all();

        return Inertia::render('Admin/Team/Index', [
            'members' => TeamMemberResource::collection($members)->resolve(),
        ]);
    }

    public function store(StoreTeamMemberRequest $request): RedirectResponse
    {
        $this->teamMemberService->create($request->validated());

        return redirect()
            ->route('admin.team-members.index')
            ->with('success', 'Team member created.');
    }

    public function update(UpdateTeamMemberRequest $request, TeamMember $teamMember): RedirectResponse
    {
        $this->teamMemberService->update($teamMember, $request->validated());

        return redirect()
            ->route('admin.team-members.index')
            ->with('success', 'Team member updated.');
    }

    public function destroy(TeamMember $teamMember): RedirectResponse
    {
        $this->teamMemberService->delete($teamMember);

        return redirect()
            ->route('admin.team-members.index')
            ->with('success', 'Team member deleted.');
    }

    public function reorder(ReorderTeamMembersRequest $request): RedirectResponse
    {
        $this->teamMemberService->reorder($request->validated('ids'));

        return redirect()
            ->route('admin.team-members.index')
            ->with('success', 'Team order updated.');
    }
}
