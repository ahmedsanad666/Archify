<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectCategoryResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $translations = [];

        if ($this->relationLoaded('translations')) {
            foreach ($this->translations as $row) {
                $code = $row->relationLoaded('language')
                    ? $row->language?->code
                    : null;

                if (! $code) {
                    continue;
                }

                $translations[$code] = [
                    'name' => $row->name,
                    'slug' => $row->slug,
                ];
            }
        }

        $default = collect($translations)->first(fn ($t) => filled($t['name'] ?? null))
            ?? ['name' => null, 'slug' => null];

        return [
            'id' => $this->id,
            'order' => $this->order,
            'type' => 'project',
            'translations' => $translations,
            'name' => $default['name'] ?? null,
            'slug' => $default['slug'] ?? null,
            'projects_count' => $this->when(
                isset($this->projects_count),
                fn () => (int) $this->projects_count,
            ),
        ];
    }
}
