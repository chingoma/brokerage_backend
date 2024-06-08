<?php

namespace App\Models\Accounting;

use App\Models\MasterModel;
use App\Traits\UuidForKey;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class AccountCategory extends MasterModel implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use SoftDeletes;
    use UuidForKey;

    protected $appends = [
        'debit',
        'credit',
    ];

    public function getCreditAttribute()
    {
        return AccountPlain::find($this->getAttribute('credit_account'));
    }

    public function getDebitAttribute()
    {
        return AccountPlain::find($this->getAttribute('debit_account'));
    }
}
