<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(
        private readonly DashboardService $dashboardService,
    ) {}

    public function __invoke(Request $request): Response
    {
        $range = $request->string('traffic_range')->toString();

        return Inertia::render(
            'Admin/Dashboard',
            $this->dashboardService->forAdmin($range !== '' ? $range : '30d'),
        );
    }
}
