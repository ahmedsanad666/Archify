<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FaqResource extends JsonResource
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
                    'question' => $row->question,
                    'answer' => $row->answer,
                ];
            }
        }

        return [
            'id' => $this->id,
            'order' => $this->order,
            'translations' => $translations,
        ];
    }
}
