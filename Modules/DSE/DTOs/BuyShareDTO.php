<?php

namespace Modules\DSE\DTOs;

use WendellAdriel\ValidatedDTO\SimpleDTO;

class BuyShareDTO extends SimpleDTO
{
    public float $price;

    public float $shares;

    public mixed $nidaNumber;

    public string $securityReference;

    public string | null $orderId = "";

    /**
     * Defines the validation rules for the DTO.
     */
    protected function rules(): array
    {
        return [];
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
