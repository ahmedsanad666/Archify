<?php

namespace App\Services;

use App\Repositories\Contracts\CoreValueRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class CoreValueService
{
    public function __construct(
        private readonly CoreValueRepositoryInterface $coreValueRepository,
    ) {}

    public function all(): Collection
    {
        return $this->coreValueRepository->all();
    }
}
