<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SiteSettingResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $translation = $this->relationLoaded('translations')
            ? $this->translations->first()
            : null;

        $translationsByLocale = [];
        if ($this->relationLoaded('translations')) {
            foreach ($this->translations as $row) {
                $code = $row->relationLoaded('language')
                    ? $row->language?->code
                    : null;

                if (! $code) {
                    continue;
                }

                $translationsByLocale[$code] = [
                    'name' => $row->name,
                    'slogan' => $row->slogan,
                    'address' => $row->address,
                    'meta_title' => $row->meta_title,
                    'meta_description' => $row->meta_description,
                    'meta_keywords' => $row->meta_keywords,
                ];
            }
        }

        return [
            'id' => $this->id,
            'email' => $this->email,
            'phone' => $this->phone,
            'whatsapp' => $this->whatsapp,
            'map_lat' => $this->map_lat,
            'map_lng' => $this->map_lng,
            'instagram_url' => $this->instagram_url,
            'youtube_url' => $this->youtube_url,
            'twitter_url' => $this->twitter_url,
            'google_analytics_id' => $this->google_analytics_id,
            'gtm_id' => $this->gtm_id,
            'facebook_pixel_id' => $this->facebook_pixel_id,
            'google_site_verification' => $this->google_site_verification,
            'robots_txt' => $this->robots_txt,
            'auto_translate_enabled' => $this->auto_translate_enabled,
            'name' => $translation?->name,
            'slogan' => $translation?->slogan,
            'address' => $translation?->address,
            'meta_title' => $translation?->meta_title,
            'meta_description' => $translation?->meta_description,
            'meta_keywords' => $translation?->meta_keywords,
            'translations' => $translationsByLocale,
            'media' => [
                'logo' => $this->getFirstMediaUrl('logo') ?: null,
                'favicon' => $this->getFirstMediaUrl('favicon') ?: null,
                'og_image' => $this->getFirstMediaUrl('og_image') ?: null,
            ],
        ];
    }
}
