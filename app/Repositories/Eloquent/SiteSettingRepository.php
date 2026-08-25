<?php

namespace App\Repositories\Eloquent;

use App\Models\SiteSetting;
use App\Repositories\Contracts\SiteSettingRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class SiteSettingRepository implements SiteSettingRepositoryInterface
{
    public function find(int $id): ?SiteSetting
    {
        return SiteSetting::query()->find($id);
    }

    public function all(): Collection
    {
        return SiteSetting::query()->get();
    }

    public function getSingleton(): ?SiteSetting
    {
        return SiteSetting::query()->first();
    }

    public function update(SiteSetting $siteSetting, array $data): SiteSetting
    {
        $siteSetting->update($data);

        return $siteSetting->fresh();
    }
}
