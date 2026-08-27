<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $interest = $this->input('interest');
        $interestIsServiceId = filled($interest) && $interest !== 'other';

        return [
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'interest' => [
                'nullable',
                'string',
                ...($interestIsServiceId ? [Rule::exists('services', 'id')] : []),
            ],
            'interest_other' => [
                'nullable',
                'string',
                'max:255',
                Rule::requiredIf(fn () => $this->input('interest') === 'other'),
            ],
            'message' => ['required', 'string', 'max:5000'],
        ];
    }
}
