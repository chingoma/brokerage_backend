<?php

namespace Modules\Custodians\Entities;

use App\Models\MasterModel;
use App\Traits\UuidForKey;
use Illuminate\Database\Eloquent\SoftDeletes;

class Custodian extends MasterModel
{
    use SoftDeletes;
    use UuidForKey;
}
