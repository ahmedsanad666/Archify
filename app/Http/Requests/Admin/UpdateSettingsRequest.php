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
        $bannerRules = ['nullable', 'image', 'max:8192', 'dimensions:min_width=1920,min_height=800'];

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
            'banner_about' => $bannerRules,
            'banner_services' => $bannerRules,
            'banner_projects' => $bannerRules,
            'banner_blogs' => $bannerRules,
            'banner_contact' => $bannerRules,
            'remove_banner_about' => ['sometimes', 'boolean'],
            'remove_banner_services' => ['sometimes', 'boolean'],
            'remove_banner_projects' => ['sometimes', 'boolean'],
            'remove_banner_blogs' => ['sometimes', 'boolean'],
            'remove_banner_contact' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        $dimensionsMessage = 'Banners must be at least 1920×800 pixels for a sharp full-width hero.';

        return [
            'banner_about.dimensions' => $dimensionsMessage,
            'banner_services.dimensions' => $dimensionsMessage,
            'banner_projects.dimensions' => $dimensionsMessage,
            'banner_blogs.dimensions' => $dimensionsMessage,
            'banner_contact.dimensions' => $dimensionsMessage,
            'banner_about.max' => 'Banner images may not be larger than 8MB.',
            'banner_services.max' => 'Banner images may not be larger than 8MB.',
            'banner_projects.max' => 'Banner images may not be larger than 8MB.',
            'banner_blogs.max' => 'Banner images may not be larger than 8MB.',
            'banner_contact.max' => 'Banner images may not be larger than 8MB.',
        ];
    }
}
