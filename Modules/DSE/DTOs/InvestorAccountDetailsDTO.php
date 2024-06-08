<?php

namespace Modules\DSE\DTOs;

use WendellAdriel\ValidatedDTO\SimpleDTO;

class InvestorAccountDetailsDTO extends SimpleDTO
{
    public $birthDistrict;

    public $birthWard;

    public $country;

    public $dob;

    public $email;

    public $firstName;

    public $gender;

    public $lastName;

    public $middleName;

    public $nationality;

    public $nidaNumber;

    public $phoneNumber;

    public $photo;

    public $physicalAddress;

    public $placeOfBirth;

    public $region;

    public $requestId;

    public $residentDistrict;

    public $residentHouseNo = 'HOUSE 102';

    public $residentPostCode = '10233';

    public $residentRegion;

    public $residentVillage = 'Masaki';

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
