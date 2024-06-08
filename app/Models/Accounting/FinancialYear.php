<?php

namespace App\Models\Accounting;

use App\Models\MasterModel;
use App\Traits\UuidForKey;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class FinancialYear extends MasterModel implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use SoftDeletes;
    use UuidForKey;
}
