<?php

namespace App\Http\Controllers;

use App\Http\Resources\TeamMemberResource;
use App\Services\TeamMemberService;
use Inertia\Inertia;
use Inertia\Response;

class TeamController extends Controller
{
    public function __construct(
        private readonly TeamMemberService $teamMemberService,
    ) {}

    public function index(): Response
    {
        return Inertia::render('Team', [
            'members' => TeamMemberResource::collection($this->teamMemberService->all())->resolve(),
        ]);
    }
}
