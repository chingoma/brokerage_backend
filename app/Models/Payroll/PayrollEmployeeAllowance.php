<?php

namespace App\Models\Payroll;

use App\Models\MasterModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class PayrollEmployeeAllowance extends MasterModel implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;
    use SoftDeletes;

    protected $appends = [
        'allowance',
    ];

    public function getAllowanceAttribute($key)
    {
        return PayrollAllowance::find($this->getAttribute('payroll_allowance_id'));
    }
}
