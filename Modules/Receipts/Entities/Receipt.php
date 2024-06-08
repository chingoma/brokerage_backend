<?php

namespace Modules\Receipts\Entities;

use App\Models\MasterModel;
use App\Traits\UuidForKey;
use Illuminate\Support\Facades\DB;
use OwenIt\Auditing\Contracts\Auditable;

class Receipt extends MasterModel implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use UuidForKey;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [];

    protected $appends = ['client'];

    public function getClientAttribute()
    {
        return DB::table('users')->select(['name', 'id'])->find($this->getAttribute('client_id'));
    }
}
