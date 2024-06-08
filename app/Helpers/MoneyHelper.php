<?php

// Code within app\Helpers\Helper.php

namespace App\Helpers;

class MoneyHelper
{
    public static function sanitize($number = 0): string
    {
        $number = str_ireplace(',', '', $number);

        return trim($number);
    }
}
