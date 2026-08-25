<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreTeamMemberRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'linkedin_url' => ['nullable', 'url', 'max:255'],
            'behance_url' => ['nullable', 'url', 'max:255'],
            'instagram_url' => ['nullable', 'url', 'max:255'],
            'order' => ['sometimes', 'integer', 'min:0'],
            'avatar' => ['nullable', 'image', 'max:5120'],
            'remove_avatar' => ['sometimes', 'boolean'],
            'auto_translate' => ['sometimes', 'boolean'],
            'source_locale' => ['required', 'string', Rule::exists('languages', 'code')],
            'translations' => ['required', 'array'],
            'translations.*.role' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $source = (string) $this->input('source_locale', 'en');
            $role = trim((string) data_get($this->input('translations'), "{$source}.role", ''));

            if ($role === '') {
                $validator->errors()->add(
                    "translations.{$source}.role",
                    'The role for the source locale is required.',
                );
            }
        });
    }
}
