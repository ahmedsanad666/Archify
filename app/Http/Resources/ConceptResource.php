<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConceptResource extends JsonResource
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
                    'title' => $row->title,
                    'short_description' => $row->short_description,
                ];
            }
        }

        $default = collect($translations)->first(fn ($t) => filled($t['title'] ?? null))
            ?? ['title' => null, 'short_description' => null];

        return [
            'id' => $this->id,
            'icon' => $this->icon,
            'translations' => $translations,
            'title' => $default['title'] ?? null,
            'short_description' => $default['short_description'] ?? null,
        ];
    }
}
