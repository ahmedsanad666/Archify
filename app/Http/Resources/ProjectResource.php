<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
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
                    'name' => $row->name,
                    'slug' => $row->slug,
                    'short_description' => $row->short_description,
                    'description' => $row->description,
                    'meta_title' => $row->meta_title,
                    'meta_description' => $row->meta_description,
                    'meta_keywords' => $row->meta_keywords,
                    'translation_status' => $row->translation_status,
                ];
            }
        }

        $default = collect($translations)->first(fn ($t) => filled($t['name'] ?? null))
            ?? ['name' => null, 'slug' => null];

        $categoryName = null;
        if ($this->relationLoaded('category') && $this->category) {
            $category = $this->category;
            if ($category->relationLoaded('translations')) {
                $categoryName = $category->translations
                    ->first(fn ($t) => filled($t->name))?->name;
            }
        }

        $conceptIds = [];
        $concepts = [];
        if ($this->relationLoaded('concepts')) {
            foreach ($this->concepts as $concept) {
                $conceptIds[] = $concept->id;
                $title = null;
                if ($concept->relationLoaded('translations')) {
                    $title = $concept->translations
                        ->first(fn ($t) => filled($t->title))?->title;
                }
                $concepts[] = [
                    'id' => $concept->id,
                    'icon' => $concept->icon,
                    'title' => $title,
                ];
            }
        }

        return [
            'id' => $this->id,
            'project_category_id' => $this->project_category_id,
            'client_name' => $this->client_name,
            'location' => $this->location,
            'year' => $this->year,
            'video_url' => $this->video_url,
            'views_count' => $this->views_count,
            'category' => $this->when(
                $this->relationLoaded('category') && $this->category,
                fn () => [
                    'id' => $this->category->id,
                    'name' => $categoryName,
                ],
            ),
            'translations' => $translations,
            'name' => $default['name'] ?? null,
            'slug' => $default['slug'] ?? null,
            'concept_ids' => $conceptIds,
            'concepts' => $concepts,
            'thumbnail_url' => $this->getFirstMediaUrl('thumbnail') ?: null,
            'preview_video_url' => $this->getFirstMediaUrl('preview_video') ?: null,
            'images_2d' => $this->mediaItems('images_2d'),
            'images_3d' => $this->mediaItems('images_3d'),
            'images_outdoor' => $this->mediaItems('images_outdoor'),
        ];
    }

    /**
     * @return array<int, array{id: int, url: string}>
     */
    private function mediaItems(string $collection): array
    {
        return $this->getMedia($collection)
            ->map(fn ($media) => [
                'id' => $media->id,
                'url' => $media->getUrl(),
            ])
            ->values()
            ->all();
    }
}
