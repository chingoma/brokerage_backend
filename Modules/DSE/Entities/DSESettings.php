<?php

namespace Modules\DSE\Entities;

use App\Models\MasterModel;
use App\Traits\UuidForKey;
use OwenIt\Auditing\Contracts\Auditable;

class DSESettings extends MasterModel implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use UuidForKey;

    protected $table = 'dse_settings';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [];
}
