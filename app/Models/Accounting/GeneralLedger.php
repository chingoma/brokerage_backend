<?php

namespace App\Models\Accounting;

use App\Models\MasterModel;
use App\Models\User;
use App\Traits\UuidForKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class GeneralLedger extends MasterModel implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;
    use SoftDeletes;
    use UuidForKey;

    protected $table = 'transactions';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d',
    ];

    protected $appends = [
        'client',
        'files',
        'account',
    ];

    public function getClientAttribute()
    {
        if (! empty($this->getAttribute('client_id'))) {
            return User::find($this->getAttribute('client_id'))->profile;
        }

        return null;
    }

    public function getFilesAttribute()
    {
        return TransactionFile::where('transaction_id', $this->getAttribute('id'))->get();
    }

    public function getAccountAttribute()
    {
        return Account::find($this->getAttribute('account_id'));
    }

    public function scopeVouchers($query)
    {
        return $query->where('category', 'Voucher');
    }

    public function scopeJournals($query)
    {
        return $query->where('category', 'Journal');
    }
}
