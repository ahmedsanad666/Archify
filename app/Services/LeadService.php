<?php

namespace App\Services;

use App\Models\Lead;
use App\Repositories\Contracts\LeadRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use InvalidArgumentException;

class LeadService
{
    public function __construct(
        private readonly LeadRepositoryInterface $leadRepository,
    ) {}

    public function paginateByStatus(?string $status = null, int $perPage = 15): LengthAwarePaginator
    {
        return $this->leadRepository->paginateByStatus($status, $perPage);
    }

    public function updateStatus(Lead $lead, string $status): Lead
    {
        if (! in_array($status, ['pending', 'contacted'], true)) {
            throw new InvalidArgumentException('Invalid lead status.');
        }

        return $this->leadRepository->update($lead, [
            'status' => $status,
        ]);
    }
}
