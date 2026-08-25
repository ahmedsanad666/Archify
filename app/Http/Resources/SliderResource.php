<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SliderResource extends JsonResource
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
                    'description' => $row->description,
                ];
            }
        }

        return [
            'id' => $this->id,
            'order' => $this->order,
            'is_active' => $this->is_active,
            'translations' => $translations,
            'image_url' => $this->getFirstMediaUrl('image') ?: null,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
