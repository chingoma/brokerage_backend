<?php

namespace App\Models\Accounting;

use App\Models\MasterModel;
use App\Traits\UuidForKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class AccountLedger extends MasterModel implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;
    use SoftDeletes;
    use UuidForKey;

    protected $table = 'accounts';

    protected $appends = [
        'classification',
        'balance',
    ];

    public function getClassificationAttribute()
    {
        return AccountClass::find($this->getAttribute('class_id'));
    }

    public function getBalanceAttribute()
    {
        return 0;
    }
}
