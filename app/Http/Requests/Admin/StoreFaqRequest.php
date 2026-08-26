<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreFaqRequest extends FormRequest
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
            'order' => ['sometimes', 'integer', 'min:0'],
            'auto_translate' => ['sometimes', 'boolean'],
            'source_locale' => ['required', 'string', Rule::exists('languages', 'code')],
            'translations' => ['required', 'array'],
            'translations.*.question' => ['nullable', 'string', 'max:255'],
            'translations.*.answer' => ['nullable', 'string'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $source = (string) $this->input('source_locale', 'en');
            $question = trim((string) data_get($this->input('translations'), "{$source}.question", ''));
            $answer = trim((string) data_get($this->input('translations'), "{$source}.answer", ''));

            if ($question === '') {
                $validator->errors()->add(
                    "translations.{$source}.question",
                    'The question for the source locale is required.',
                );
            }

            if ($answer === '') {
                $validator->errors()->add(
                    "translations.{$source}.answer",
                    'The answer for the source locale is required.',
                );
            }
        });
    }
}
