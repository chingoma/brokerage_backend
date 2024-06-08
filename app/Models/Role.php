<?php

namespace App\Models;

use App\Traits\UuidForKey;
use OwenIt\Auditing\Contracts\Auditable;

class Role extends \Spatie\Permission\Models\Role implements Auditable
{
    //    use UuidForKey;
    use \OwenIt\Auditing\Auditable;

    public $guarded = [];

    protected $appends = ['permissions'];

    public function getPermissionsAttribute()
    {
        $permissions = $this->permissions()->pluck('name');

        return $permissions;
    }
}
