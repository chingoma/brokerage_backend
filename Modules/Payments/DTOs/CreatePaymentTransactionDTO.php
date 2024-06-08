<?php

namespace Modules\Payments\DTOs;

use WendellAdriel\ValidatedDTO\ValidatedDTO;

class CreatePaymentTransactionDTO extends ValidatedDTO
{
    public mixed $amount = '';

    public mixed $payee = '';

    public mixed $category = '';

    public mixed $payment_method = '';

    public mixed $reference = '';

    public mixed $description = '';

    public mixed $realAccount = '';

    /**
     * Defines the validation rules for the DTO.
     */
    protected function rules(): array
    {
        return [
            'amount' => ['required','numeric'],
            'payee' => ['required'],
            'category' => ['required'],
            'reference' => ['nullable'],
            'payment_method' => ['required'],
            'description' => ['required'],
            'realAccount' => ['nullable'],
        ];
    }

    /**
     * Defines the default values for the properties of the DTO.
     */
    protected function defaults(): array
    {
        return [];
    }

    /**
     * Defines the type casting for the properties of the DTO.
     */
    protected function casts(): array
    {
        return [];
    }

    /**
     * Maps the DTO properties before the DTO instantiation.
     */
    protected function mapBeforeValidation(): array
    {
        return [];
    }

    /**
     * Maps the DTO properties before the DTO export.
     */
    protected function mapBeforeExport(): array
    {
        return [];
    }

    /**
     * Defines the custom messages for validator errors.
     */
    public function messages(): array
    {
        return [];
    }

    /**
     * Defines the custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [];
    }
}
