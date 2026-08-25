<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CoreValueResource extends JsonResource
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

        return [
            'id' => $this->id,
            'icon' => $this->icon,
            'order' => $this->order,
            'translations' => $translations,
        ];
    }
}
