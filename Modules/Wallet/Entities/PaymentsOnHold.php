<?php

namespace Modules\Wallet\Entities;

use App\Models\MasterModel;
use App\Traits\UuidForKey;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentsOnHold extends MasterModel
{
    use SoftDeletes;
    use UuidForKey;
}
