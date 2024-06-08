<?php

namespace Modules\Audits\Entities;

use App\Models\MasterModel;
use App\Models\User;

class AuthLogs extends MasterModel
{
    protected $table = 'authentication_log';

    public $appends = ['name'];

    public function getNameAttribute()
    {
        return User::find($this->getAttribute('authenticatable_id'))->name ?? '';
    }
}
