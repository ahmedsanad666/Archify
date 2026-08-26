<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateLeadStatusRequest;
use App\Http\Resources\LeadResource;
use App\Models\Lead;
use App\Services\LeadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LeadController extends Controller
{
    public function __construct(
        private readonly LeadService $leadService,
    ) {}

    public function index(Request $request): Response
    {
        $status = $request->query('status');

        if (! in_array($status, ['pending', 'contacted', null], true)) {
            $status = null;
        }

        $leads = $this->leadService->paginateByStatus($status, 15);

        return Inertia::render('Admin/Leads/Index', [
            'leads' => LeadResource::collection($leads),
            'filters' => [
                'status' => $status,
            ],
        ]);
    }

    public function updateStatus(UpdateLeadStatusRequest $request, Lead $lead): RedirectResponse
    {
        $this->leadService->updateStatus(
            $lead,
            $request->validated('status'),
        );

        return redirect()->back();
    }
}
