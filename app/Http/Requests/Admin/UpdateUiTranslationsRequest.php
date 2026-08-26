<?php

namespace App\Http\Requests\Admin;

use App\Services\UiTranslationService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateUiTranslationsRequest extends FormRequest
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
        $locales = app(UiTranslationService::class)->allowedLocales();

        return [
            'locale' => ['required', 'string', Rule::in($locales)],
            'translations' => ['required', 'array'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $translations = $this->input('translations');

            if (! is_array($translations)) {
                return;
            }

            foreach ($translations as $key => $value) {
                if (! is_string($key)) {
                    $validator->errors()->add('translations', 'Translation keys must be strings.');
                    continue;
                }

                if (is_array($value)) {
                    $validator->errors()->add(
                        "translations.{$key}",
                        'Translation values must be strings.',
                    );
                    continue;
                }

                if (! is_string($value) && ! is_numeric($value)) {
                    $validator->errors()->add(
                        "translations.{$key}",
                        'Translation values must be strings.',
                    );
                }
            }
        });
    }
}
