<?php

namespace App\DTOs\Customers;

use WendellAdriel\ValidatedDTO\SimpleDTO;

class CustomerDTO extends SimpleDTO
{
    public $name;

    public $email;

    public $contact_telephone;

    public $contact_email;

    public $dse_account;

    public $bot_account;

    public $country;

    public $bank;

    public $bank_account;

    public $id_type;

    public $identity;

    public $gender;

    public $address;

    public $status;

    public $client_type;

    public $profile_type;

    public $country_iso;

    public $wallet_balance;

    public $volume;

    public $securities;

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
