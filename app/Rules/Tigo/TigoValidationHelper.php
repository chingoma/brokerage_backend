<?php

namespace App\Rules\Tigo;

use App\Rules\ValidationHelper;

class TigoValidationHelper
{
    public static function accountToWalletRequestValidator(): array
    {
        return [
            'customer_msisdn' => self::msisdnValidator(),
            'id' => ValidationHelper::userValidator(),
            'amount' => ['required'],
            'sender_name' => ['required', 'string'],
            'language1' => ['required', 'string'],
        ];
    }

    public static function createRequestValidator(): array
    {
        return [
            'customer_msisdn' => self::msisdnValidator(),
            'id' => ValidationHelper::userValidator(),
            'amount' => ['required'],
            'remarks' => ['required'],
        ];
    }

    public static function refundTransactionValidator(): array
    {
        return [
            'customer_msisdn' => self::msisdnValidator(),
            'id' => ValidationHelper::userValidator(),
            'amount' => ['required'],
            'reference_id' => self::referenceIdValidator(),
            'transaction_id' => self::transactionIdValidator(),
        ];
    }

    public static function msisdnValidator(): array
    {
        return ['required', 'string', 'phone:TZ'];
    }

    public static function referenceIdValidator(): array
    {
        return ['required', 'string', new ReferenceAvailableValidation];
    }

    public static function transactionIdValidator(): array
    {
        return ['required', 'string', new TransactionAvailableValidation];
    }
}
