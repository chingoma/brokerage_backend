<?php

namespace Modules\Reports\Entities;

use App\Models\MasterModel;
use App\Traits\UuidForKey;
use OwenIt\Auditing\Contracts\Auditable;

class InvestorPortfolio extends MasterModel implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use UuidForKey;
}
