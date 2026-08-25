<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TestimonialResource extends JsonResource
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
                    'quote' => $row->quote,
                ];
            }
        }

        return [
            'id' => $this->id,
            'client_name' => $this->client_name,
            'order' => $this->order,
            'translations' => $translations,
            'avatar_url' => $this->getFirstMediaUrl('avatar') ?: null,
        ];
    }
}
