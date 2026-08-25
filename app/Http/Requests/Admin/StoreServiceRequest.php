<?php

namespace App\Http\Requests\Admin;

use App\Support\AppIcons;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

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
            'icon' => ['required', 'string', Rule::in(AppIcons::names())],
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

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $source = (string) $this->input('source_locale', 'en');
            $title = trim((string) data_get($this->input('translations'), "{$source}.title", ''));

            if ($title === '') {
                $validator->errors()->add(
                    "translations.{$source}.title",
                    'The title for the source locale is required.',
                );
            }
        });
    }
}
