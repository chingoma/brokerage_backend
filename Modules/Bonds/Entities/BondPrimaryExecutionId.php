<?php

namespace Modules\Bonds\Entities;

use App\Models\MasterModel;
use App\Traits\UuidForKey;
use Illuminate\Database\Eloquent\SoftDeletes;

class BondPrimaryExecutionId extends MasterModel
{
    use SoftDeletes;
    use UuidForKey;

    protected $fillable = [];
}
