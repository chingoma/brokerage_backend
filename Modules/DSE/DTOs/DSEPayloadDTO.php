<?php

namespace Modules\DSE\DTOs;

use WendellAdriel\ValidatedDTO\ValidatedDTO;

class DSEPayloadDTO extends ValidatedDTO
{
    public mixed $csdAccount = '';

    public mixed $nidaNumber = '';

    public mixed $securityReference = '';

    /**
     * Defines the validation rules for the DTO.
     */
    protected function rules(): array
    {
        return [
            'nidaNumber' => ['nullable'],
            'csdAccount' => ['nullable'],
            'securityReference' => ['nullable'],
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