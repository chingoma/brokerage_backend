<?php

namespace App\Rules;

use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;
use Illuminate\Validation\Validator;

class Age18Validator implements DataAwareRule, ValidationRule
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
        $date = Carbon::createFromFormat('Y-m-d', date('Y-m-d', strtotime($value)));
        $today = now(env('TIMEZONE'))->subYears(18);

        if (! $date->lessThanOrEqualTo($today)) {
            $fail('The :attribute must be a date in the past. '.$today->toDateString());
        }
    }
}