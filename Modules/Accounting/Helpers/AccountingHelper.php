<?php

namespace Modules\Accounting\Helpers;

use App\Helpers\Helper;
use App\Models\Accounting\Transaction;

class AccountingHelper
{
    public static function generateReference(): int
    {
        $reference = mt_rand(11111111111, 99999999999);
        $check = Transaction::where('reference', $reference)->first();
        if (! empty($check)) {
            self::generateReference();
        }

        return $reference;
    }

    public static function setBusinessYear($model): void
    {
        $model->financial_year_id = Helper::business()->financial_year;
    }
}
