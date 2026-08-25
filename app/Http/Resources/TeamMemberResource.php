<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeamMemberResource extends JsonResource
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
                    'role' => $row->role,
                ];
            }
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'order' => $this->order,
            'linkedin_url' => $this->linkedin_url,
            'behance_url' => $this->behance_url,
            'instagram_url' => $this->instagram_url,
            'translations' => $translations,
            'avatar_url' => $this->getFirstMediaUrl('avatar') ?: null,
        ];
    }
}
