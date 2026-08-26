<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BlogResource extends JsonResource
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
                    'slug' => $row->slug,
                    'content' => $row->content,
                    'read_time' => $row->read_time,
                    'meta_title' => $row->meta_title,
                    'meta_description' => $row->meta_description,
                    'meta_keywords' => $row->meta_keywords,
                    'translation_status' => $row->translation_status,
                ];
            }
        }

        $locale = app()->getLocale();
        $localized = $translations[$locale]
            ?? $translations['en']
            ?? collect($translations)->first(fn ($t) => filled($t['title'] ?? null))
            ?? ['title' => null, 'slug' => null, 'read_time' => null];

        $category = null;
        if ($this->relationLoaded('category') && $this->category) {
            $categoryName = null;

            if ($this->category->relationLoaded('translations')) {
                $byCode = [];
                foreach ($this->category->translations as $row) {
                    $code = $row->relationLoaded('language')
                        ? $row->language?->code
                        : null;
                    if ($code) {
                        $byCode[$code] = $row->name;
                    }
                }
                $categoryName = $byCode[$locale]
                    ?? $byCode['en']
                    ?? collect($byCode)->first(fn ($n) => filled($n));
            }

            $category = [
                'id' => $this->category->id,
                'name' => $categoryName,
                'color' => $this->category->color,
            ];
        }

        return [
            'id' => $this->id,
            'blog_category_id' => $this->blog_category_id,
            'views_count' => $this->views_count,
            'category' => $category,
            'translations' => $translations,
            'title' => $localized['title'] ?? null,
            'slug' => $localized['slug'] ?? null,
            'read_time' => $localized['read_time'] ?? null,
            'thumbnail_url' => $this->getFirstMediaUrl('thumbnail') ?: null,
            'cover_url' => $this->getFirstMediaUrl('cover') ?: null,
        ];
    }
}
