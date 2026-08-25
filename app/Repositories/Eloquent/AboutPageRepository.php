<?php

namespace App\Repositories\Eloquent;

use App\Models\AboutPage;
use App\Repositories\Contracts\AboutPageRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class AboutPageRepository implements AboutPageRepositoryInterface
{
    public function find(int $id): ?AboutPage
    {
        return AboutPage::query()->with(['translations.language', 'media'])->find($id);
    }

    public function all(): Collection
    {
        return AboutPage::query()->with(['translations', 'media'])->get();
    }

    public function getSingleton(): ?AboutPage
    {
        return AboutPage::query()->with(['translations.language', 'media'])->first();
    }

    public function update(AboutPage $aboutPage, array $data = []): AboutPage
    {
        if ($data !== []) {
            $aboutPage->update($data);
        }

        return $aboutPage->fresh(['translations.language', 'media']);
    }
}
