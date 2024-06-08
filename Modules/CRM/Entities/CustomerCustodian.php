<?php

namespace Modules\CRM\Entities;

use App\Models\MasterModel;
use App\Traits\UuidForKey;

class CustomerCustodian extends MasterModel
{
    use UuidForKey;

    protected $fillable = [];

    protected $appends = ['name'];

    public function getNameAttribute()
    {
        return \DB::table('custodians')->find($this->getAttribute('custodian_id'))->name ?? '';
    }
}
