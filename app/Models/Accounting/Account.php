<?php

namespace App\Models\Accounting;

use App\Models\MasterModel;
use App\Traits\UuidForKey;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class Account extends MasterModel implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use SoftDeletes;
    use UuidForKey;

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
        $debit = Transaction::where('status', 'approved')->where('account_id', $this->getAttribute('id'))->sum('debit');
        $credit = Transaction::where('status', 'approved')->where('account_id', $this->getAttribute('id'))->sum('credit');
        if (strtolower($this->getAttribute('nature')) == 'debit') {
            return $debit - $credit;
        } else {
            return $credit - $debit;
        }
    }

    public function scopeList($query)
    {
        return $query->get();
    }

    public function scopeAssets($query)
    {
        return $query->whereIn('class_id', [2, 3, 4])->get();
    }

    public function scopeCash($query)
    {
        return $query->whereIn('class_id', [1])->get();
    }

    public function scopeReceivable($query)
    {
        return $query->whereIn('class_id', [2])->get();
    }

    public function scopeLiabilities($query)
    {
        return $query->whereIn('class_id', [6, 7, 8])->get();
    }

    public function scopeExpenses($query)
    {
        return $query->whereIn('class_id', [11, 12])->get();
    }

    public function scopeRevenue($query)
    {
        return $query->whereIn('class_id', [10])->get();
    }

    public function scopeEquities($query)
    {
        return $query->where('class_id', [9])->get();
    }
}
