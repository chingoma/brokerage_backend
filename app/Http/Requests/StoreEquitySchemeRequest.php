<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreEquitySchemeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:1', 'max:191'],
            'step_one' => ['nullable', 'numeric'],
            'broker_fee' => ['nullable', 'string', 'min:1', 'max:191'],
            'mode' => ['required', 'string', 'min:1', 'max:191'],
            'flat_rate' => ['nullable', 'numeric'],
            'step_two' => ['nullable', 'numeric'],
            'step_three' => ['nullable', 'numeric'],
            'dse_fee' => ['nullable', 'numeric'],
            'csdr_fee' => ['nullable', 'numeric'],
            'cmsa_fee' => ['nullable', 'numeric'],
            'fidelity_fee' => ['nullable', 'numeric'],
        ];
    }
}
