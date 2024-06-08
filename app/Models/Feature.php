<?php

namespace App\Models;

use App\Traits\UuidForKey;
use Illuminate\Database\Eloquent\Model;

class Feature extends Model
{
    use UuidForKey;

    protected $appends = [
        'permissions',
    ];

    public function getPermissionsAttribute()
    {
        return Permission::where('feature_id', $this->getAttribute('id'))->get();
    }
}
