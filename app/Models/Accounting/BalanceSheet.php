<?php

namespace App\Models\Accounting;

use App\Models\MasterModel;
use App\Traits\UuidForKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class BalanceSheet extends MasterModel implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;
    use SoftDeletes;
    use UuidForKey;

    protected $table = 'accounts';

    protected $appends = [
        'debit',
        'credit',
        'balance',
    ];

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

    public function getDebitAttribute()
    {
        return Transaction::where('status', 'approved')->where('account_id', $this->getAttribute('id'))->sum('debit');
    }

    public function getCreditAttribute()
    {
        return Transaction::where('status', 'approved')->where('account_id', $this->getAttribute('id'))->sum('credit');
    }
}
