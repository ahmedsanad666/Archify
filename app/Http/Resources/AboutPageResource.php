<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AboutPageResource extends JsonResource
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
                    'story_title' => $row->story_title,
                    'story_description' => $row->story_description,
                    'vision_title' => $row->vision_title,
                    'vision_description' => $row->vision_description,
                    'mission_title' => $row->mission_title,
                    'mission_description' => $row->mission_description,
                ];
            }
        }

        return [
            'id' => $this->id,
            'translations' => $translations,
            'story_image_url' => $this->getFirstMediaUrl('story_image') ?: null,
        ];
    }
}
