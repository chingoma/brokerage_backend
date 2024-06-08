<?php

namespace App\Rules;

use App\Helpers\Helper;
use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;
use Illuminate\Translation\PotentiallyTranslatedString;
use Illuminate\Validation\Validator;

class UserHasCustodianValidation implements DataAwareRule, ValidationRule
{
    /**
     * The validator instance.
     *
     * @var Validator
     */
    protected $validator;

    /**
     * All of the data under validation.
     *
     * @var array<string, mixed>
     */
    protected $data = [];

    /**
     * Set the data under validation.
     *
     * @param  array<string, mixed>  $data
     */
    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Set the current validator.
     */
    public function setValidator(Validator $validator): static
    {
        $this->validator = $validator;

        return $this;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! empty($value)) {
            $id = Helper::clientId();
            $user = DB::table('users')->find($id);
            if ($user->has_custodian != 'yes' || $user->custodian_approved != 'yes') {
                $fail(__('This account is not approved for custodian'));
            }

            $custodian = DB::table('customer_custodians')
                ->where('user_id', $user->id)
                ->where('custodian_id', $value)
                ->where('status', 'active')
                ->first();

            if (empty($custodian->id)) {
                $fail(__('This account has not approved custodian'));
            }
        }
    }
}
