<?php

namespace App\Models;

use App\Traits\UuidForKey;
use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    use UuidForKey;

    protected $appends = [
        'permissions',
    ];

    public function getPermissionsAttribute()
    {
        return Permission::where('module', $this->getAttribute('name'))->get();
    }
}
