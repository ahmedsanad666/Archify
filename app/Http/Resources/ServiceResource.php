<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceResource extends JsonResource
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
                    'included_items' => $row->included_items ?? [],
                ];
            }
        }

        $firstTitle = collect($translations)->pluck('title')->first(fn ($t) => filled($t));

        return [
            'id' => $this->id,
            'icon' => $this->icon,
            'order' => $this->order,
            'show_on_home' => $this->show_on_home,
            'translations' => $translations,
            'title' => $firstTitle,
            'items_count' => collect($translations)->map(fn ($t) => count($t['included_items'] ?? []))->max() ?? 0,
        ];
    }
}
