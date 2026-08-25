<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSettingsRequest extends FormRequest
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
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'whatsapp' => ['nullable', 'string', 'max:50'],
            'map_lat' => ['nullable', 'numeric'],
            'map_lng' => ['nullable', 'numeric'],
            'instagram_url' => ['nullable', 'url', 'max:255'],
            'youtube_url' => ['nullable', 'url', 'max:255'],
            'twitter_url' => ['nullable', 'url', 'max:255'],
            'google_analytics_id' => ['nullable', 'string', 'max:100'],
            'gtm_id' => ['nullable', 'string', 'max:100'],
            'facebook_pixel_id' => ['nullable', 'string', 'max:100'],
            'google_site_verification' => ['nullable', 'string', 'max:255'],
            'robots_txt' => ['nullable', 'string'],
            'auto_translate_enabled' => ['sometimes', 'boolean'],
            'auto_translate' => ['sometimes', 'boolean'],
            'source_locale' => ['required', 'string', Rule::exists('languages', 'code')],
            'translations' => ['required', 'array'],
            'translations.*.name' => ['nullable', 'string', 'max:255'],
            'translations.*.slogan' => ['nullable', 'string', 'max:255'],
            'translations.*.address' => ['nullable', 'string'],
            'translations.*.meta_title' => ['nullable', 'string', 'max:255'],
            'translations.*.meta_description' => ['nullable', 'string'],
            'translations.*.meta_keywords' => ['nullable', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'max:5120'],
            'favicon' => ['nullable', 'image', 'max:1024'],
            'og_image' => ['nullable', 'image', 'max:5120'],
            'remove_logo' => ['sometimes', 'boolean'],
            'remove_favicon' => ['sometimes', 'boolean'],
            'remove_og_image' => ['sometimes', 'boolean'],
        ];
    }
}
