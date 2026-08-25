<?php

namespace App\Services;

use App\Repositories\Contracts\FaqRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class FaqService
{
    public function __construct(
        private readonly FaqRepositoryInterface $faqRepository,
    ) {}

    public function all(): Collection
    {
        return $this->faqRepository->all();
    }
}
