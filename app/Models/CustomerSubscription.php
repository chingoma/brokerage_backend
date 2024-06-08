<?php

namespace App\Models;

use OwenIt\Auditing\Contracts\Audit;

class CustomerSubscription extends MasterModel implements Audit
{
    use \OwenIt\Auditing\Audit;
}
