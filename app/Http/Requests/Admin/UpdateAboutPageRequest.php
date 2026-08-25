<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAboutPageRequest extends FormRequest
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
            'auto_translate' => ['sometimes', 'boolean'],
            'source_locale' => ['required', 'string', Rule::exists('languages', 'code')],
            'translations' => ['required', 'array'],
            'translations.*.story_title' => ['nullable', 'string', 'max:255'],
            'translations.*.story_description' => ['nullable', 'string'],
            'translations.*.vision_title' => ['nullable', 'string', 'max:255'],
            'translations.*.vision_description' => ['nullable', 'string'],
            'translations.*.mission_title' => ['nullable', 'string', 'max:255'],
            'translations.*.mission_description' => ['nullable', 'string'],
            'story_image' => ['nullable', 'image', 'max:5120'],
            'remove_story_image' => ['sometimes', 'boolean'],
            'active_tab' => ['nullable', 'string', Rule::in(['story', 'vision', 'mission'])],
        ];
    }
}
