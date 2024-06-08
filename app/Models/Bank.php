<?php

namespace App\Models;

use App\Traits\UuidForKey;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bank extends MasterModel
{
    use SoftDeletes;
    use UuidForKey;
}
