<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'project_category_id' => ['required', 'integer', Rule::exists('project_categories', 'id')],
            'client_name' => ['required', 'string', 'max:255'],
            'location' => ['required', 'string', 'max:255'],
            'year' => ['required', 'integer', 'min:1900', 'max:2100'],
            'video_url' => ['nullable', 'url', 'max:500'],
            'auto_translate' => ['sometimes', 'boolean'],
            'source_locale' => ['required', 'string', Rule::exists('languages', 'code')],
            'translations' => ['required', 'array'],
            'translations.*.name' => ['nullable', 'string', 'max:255'],
            'translations.*.slug' => ['nullable', 'string', 'max:255'],
            'translations.*.short_description' => ['nullable', 'string'],
            'translations.*.description' => ['nullable', 'string'],
            'translations.*.meta_title' => ['nullable', 'string', 'max:255'],
            'translations.*.meta_description' => ['nullable', 'string'],
            'translations.*.meta_keywords' => ['nullable', 'string', 'max:500'],
            'concept_ids' => ['nullable', 'array'],
            'concept_ids.*' => ['integer', Rule::exists('concepts', 'id')],
            // Requires PHP upload_max_filesize >= 64M and post_max_size >= 128M (local + production).
            'thumbnail' => ['nullable', 'image', 'max:5120'],
            'preview_video' => ['nullable', 'file', 'mimetypes:video/mp4,video/webm,video/quicktime', 'max:51200'],
            'remove_thumbnail' => ['sometimes', 'boolean'],
            'remove_preview_video' => ['sometimes', 'boolean'],
            'images_2d' => ['nullable', 'array'],
            'images_2d.*' => ['image', 'max:5120'],
            'images_3d' => ['nullable', 'array'],
            'images_3d.*' => ['image', 'max:5120'],
            'images_outdoor' => ['nullable', 'array'],
            'images_outdoor.*' => ['image', 'max:5120'],
            'remove_media_ids' => ['nullable', 'array'],
            'remove_media_ids.*' => ['integer'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $source = (string) $this->input('source_locale', 'en');
            $name = trim((string) data_get($this->input('translations'), "{$source}.name", ''));

            if ($name === '') {
                $validator->errors()->add(
                    "translations.{$source}.name",
                    'The name for the source locale is required.',
                );
            }
        });
    }
}
