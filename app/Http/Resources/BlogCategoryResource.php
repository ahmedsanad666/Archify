<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BlogCategoryResource extends JsonResource
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
            'color' => $this->color,
            'order' => $this->order,
            'type' => 'blog',
            'translations' => $translations,
            'name' => $default['name'] ?? null,
            'slug' => $default['slug'] ?? null,
            'blogs_count' => $this->when(
                isset($this->blogs_count),
                fn () => (int) $this->blogs_count,
            ),
        ];
    }
}
