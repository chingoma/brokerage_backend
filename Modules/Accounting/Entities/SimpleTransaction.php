<?php

namespace Modules\Accounting\Entities;

use App\Models\MasterModel;
use App\Traits\UuidForKey;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class SimpleTransaction extends MasterModel
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [];

    protected $table = 'simple_transactions';

    use SoftDeletes;
    use UuidForKey;

    protected $appends = ['client'];

    public function getClientAttribute()
    {
        return DB::table('users')->select('name', 'id')->find($this->getAttribute('client_id'));
    }
}
