<?php

namespace Modules\Payments\Entities;

use App\Models\MasterModel;
use App\Traits\UuidForKey;

class Payment extends MasterModel
{
    use UuidForKey;
}
