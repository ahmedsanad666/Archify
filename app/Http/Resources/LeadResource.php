<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeadResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $service = null;

        if ($this->relationLoaded('service') && $this->service) {
            $title = null;

            if ($this->service->relationLoaded('translations')) {
                $locale = app()->getLocale();
                $byCode = [];

                foreach ($this->service->translations as $row) {
                    $code = $row->relationLoaded('language')
                        ? $row->language?->code
                        : null;

                    if ($code) {
                        $byCode[$code] = $row->title;
                    }
                }

                $title = $byCode[$locale]
                    ?? $byCode['en']
                    ?? collect($byCode)->first(fn ($t) => filled($t));
            }

            $service = [
                'id' => $this->service->id,
                'title' => $title,
            ];
        }

        $language = null;

        if ($this->relationLoaded('language') && $this->language) {
            $language = [
                'id' => $this->language->id,
                'code' => $this->language->code,
                'name' => $this->language->name,
            ];
        }

        return [
            'id' => $this->id,
            'full_name' => $this->full_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'message' => $this->message,
            'status' => $this->status,
            'interest_other' => $this->interest_other,
            'internal_notes' => $this->internal_notes,
            'ip_address' => $this->ip_address,
            'created_at' => $this->created_at?->toISOString(),
            'service' => $service,
            'language' => $language,
        ];
    }
}
