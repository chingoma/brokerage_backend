<?php

namespace App\Models;

use App\Traits\UuidForKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PermissionUser extends Model
{
    use SoftDeletes;
    use UuidForKey;

    protected $table = 'permission_user';
}
