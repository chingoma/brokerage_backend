<?php

namespace App\Helpers;

class PayrollHelper
{
    public static function paye($amount): float|int
    {
        if ($amount <= 271000) {
            return 0;
        } elseif ($amount > 270000 && $amount <= 520000) {
            return ($amount - 270000) * 0.08;
        } elseif ($amount > 520000 && $amount <= 760000) {
            return 20000 + (($amount - 520000) * 0.2);
        } elseif ($amount > 760000 && $amount <= 1000000) {
            return 68000 + (($amount - 760000) * 0.25);
        } else {
            return 128000 + (($amount - 1000000) * 0.3);
        }
    }

    public static function setTransaction($model, $amount, $action): void
    {
        if (strtolower($action) == 'debit') {
            $model->debit = MoneyHelper::sanitize($amount);
            $model->credit = 0;
        } else {
            $model->credit = MoneyHelper::sanitize($amount);
            $model->debit = 0;
        }

        $model->amount = MoneyHelper::sanitize($amount);
        $model->action = $action;
    }
}
