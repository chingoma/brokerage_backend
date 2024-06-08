<?php

namespace Modules\Bonds\Entities;

use App\Models\MasterModel;
use App\Traits\UuidForKey;
use Illuminate\Database\Eloquent\SoftDeletes;

class BondId extends MasterModel implements \OwenIt\Auditing\Contracts\Auditable
{
    use \App\Traits\UuidForKey;
    use \OwenIt\Auditing\Auditable;
    use SoftDeletes;
    use UuidForKey;

    protected $fillable = [];
}
