<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreServiceRequest extends FormRequest
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
            'icon' => ['nullable', 'string', 'max:100'],
            'order' => ['nullable', 'integer', 'min:0'],
            'show_on_home' => ['sometimes', 'boolean'],
            'auto_translate' => ['sometimes', 'boolean'],
            'source_locale' => ['required', 'string', Rule::exists('languages', 'code')],
            'translations' => ['required', 'array'],
            'translations.*.title' => ['nullable', 'string', 'max:255'],
            'translations.*.short_description' => ['nullable', 'string'],
            'translations.*.included_items' => ['nullable', 'array'],
            'translations.*.included_items.*' => ['nullable', 'string', 'max:255'],
        ];
    }
}
