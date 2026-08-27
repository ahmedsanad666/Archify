<?php

namespace App\Services;

use App\Models\Lead;
use App\Repositories\Contracts\LanguageRepositoryInterface;
use App\Repositories\Contracts\LeadRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use InvalidArgumentException;

class LeadService
{
    public function __construct(
        private readonly LeadRepositoryInterface $leadRepository,
        private readonly LanguageRepositoryInterface $languageRepository,
    ) {}

    public function paginateByStatus(?string $status = null, int $perPage = 15): LengthAwarePaginator
    {
        return $this->leadRepository->paginateByStatus($status, $perPage);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data, Request $request): Lead
    {
        $interest = isset($data['interest']) ? trim((string) $data['interest']) : '';
        $serviceId = null;
        $interestOther = null;

        if ($interest === 'other') {
            $interestOther = filled($data['interest_other'] ?? null)
                ? trim((string) $data['interest_other'])
                : null;
        } elseif ($interest !== '' && ctype_digit($interest)) {
            $serviceId = (int) $interest;
        }

        $language = $this->languageRepository->findByCode(app()->getLocale());

        return $this->leadRepository->create([
            'full_name' => trim((string) $data['full_name']),
            'email' => trim((string) $data['email']),
            'phone' => filled($data['phone'] ?? null) ? trim((string) $data['phone']) : null,
            'service_id' => $serviceId,
            'interest_other' => $interestOther,
            'message' => trim((string) $data['message']),
            'status' => 'pending',
            'language_id' => $language?->id,
            'ip_address' => $request->ip(),
        ]);
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
