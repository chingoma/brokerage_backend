<?php

namespace App\Models;

use App\Traits\UuidForKey;
use Illuminate\Support\Facades\DB;

class Statement extends MasterModel
{
    use UuidForKey;

    protected $appends = ['client', 'amount'];

    public function getClientAttribute()
    {
        return DB::table('users')->select(['id', 'name'])->find($this->getAttribute('client_id'));
    }

    public function getAmountAttribute()
    {
        return $this->getAttribute('credit') + $this->getAttribute('debit');
    }
}
