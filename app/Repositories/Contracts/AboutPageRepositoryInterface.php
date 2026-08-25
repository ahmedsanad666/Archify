<?php

namespace App\Repositories\Contracts;

use App\Models\AboutPage;
use Illuminate\Database\Eloquent\Collection;

interface AboutPageRepositoryInterface
{
    public function find(int $id): ?AboutPage;

    public function all(): Collection;

    public function getSingleton(): ?AboutPage;

    public function update(AboutPage $aboutPage, array $data = []): AboutPage;
}
