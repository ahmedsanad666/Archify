<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class MediaResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $mime = (string) ($this->mime_type ?? '');
        $size = (int) ($this->size ?? 0);

        return [
            'id' => $this->id,
            'url' => $this->getUrl(),
            'name' => $this->name,
            'file_name' => $this->file_name,
            'mime_type' => $this->mime_type,
            'size' => $size,
            'human_size' => $this->humanSize($size),
            'collection_name' => $this->collection_name,
            'model_type' => $this->model_type,
            'model_label' => class_basename((string) $this->model_type),
            'model_id' => $this->model_id,
            'is_image' => Str::startsWith($mime, 'image/'),
            'is_video' => Str::startsWith($mime, 'video/'),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }

    private function humanSize(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes.' B';
        }

        if ($bytes < 1024 * 1024) {
            return round($bytes / 1024, 1).' KB';
        }

        return round($bytes / (1024 * 1024), 1).' MB';
    }
}
