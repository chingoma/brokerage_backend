<?php

namespace Modules\CRM\Entities;

use App\Models\MasterModel;
use App\Traits\UuidForKey;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class CustomerCategory extends MasterModel implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use SoftDeletes;
    use UuidForKey;

    protected $casts = [
        'created_at' => 'datetime:Y-m-d',
        'updated_at' => 'datetime:Y-m-d',
    ];

    protected $appends = [
        'manager',
        'customers',
    ];

    public function getManagerAttribute()
    {
        return \DB::table('users')->find($this->getAttribute('manager_id'));
    }

    public function getCustomersAttribute()
    {
        return \DB::table('users')->where('manager_id', $this->getAttribute('manager_id'))->count();
    }
}
