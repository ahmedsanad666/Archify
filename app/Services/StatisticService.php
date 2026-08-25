<?php

namespace App\Services;

use App\Repositories\Contracts\StatisticRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class StatisticService
{
    public function __construct(
        private readonly StatisticRepositoryInterface $statisticRepository,
    ) {}

    public function all(): Collection
    {
        return $this->statisticRepository->all();
    }
}
