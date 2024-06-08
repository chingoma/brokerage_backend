<?php

namespace App\Models\Payroll;

use App\Models\MasterModel;
use App\Traits\UuidForKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class PayrollEmployeePension extends MasterModel implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;
    use SoftDeletes;
    use UuidForKey;

    protected $appends = [
        'pension',
    ];

    public function getPensionAttribute($key)
    {
        return PayrollPension::find($this->getAttribute('payroll_pension_id'));
    }
}
