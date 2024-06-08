<?php

namespace App\Models\Payroll;

use App\Models\MasterModel;
use App\Models\SimpleProfile;
use App\Traits\UuidForKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class PayrollEmployeeSetup extends MasterModel implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;
    use SoftDeletes;
    use UuidForKey;

    protected $appends = [
        'employee',
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d',
        'updated_at' => 'datetime:Y-m-d',
    ];

    public function getEmployeeAttribute()
    {
        return SimpleProfile::where('user_id', $this->getAttribute('user_id'))->first();
    }
}
