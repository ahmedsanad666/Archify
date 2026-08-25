<?php

namespace App\Repositories\Contracts;

use App\Models\SiteSetting;
use Illuminate\Database\Eloquent\Collection;

interface SiteSettingRepositoryInterface
{
    public function find(int $id): ?SiteSetting;

    public function all(): Collection;

    public function getSingleton(): ?SiteSetting;

    public function update(SiteSetting $siteSetting, array $data): SiteSetting;
}
