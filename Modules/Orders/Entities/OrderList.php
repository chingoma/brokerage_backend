<?php

namespace Modules\Orders\Entities;

use App\Models\DealingSheet;
use App\Models\MasterModel;
use App\Models\Security;
use App\Models\User;
use App\Traits\UuidForKey;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class OrderList extends MasterModel implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use SoftDeletes;
    use UuidForKey;

    protected $table = 'orders';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime:Y-m-d',
        'updated_at' => 'datetime:Y-m-d',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'client',
        'files',
        'executions',
        'security',
        'balance',
        'executed',
        'brokerage',
    ];

    public function getBrokerageAttribute()
    {
        return DealingSheet::where('status', 'approved')->where('order_id', $this->getAttribute('id'))->sum('brokerage');
    }

    public function getExecutedAttribute()
    {
        return DealingSheet::where('status', 'approved')->where('status', '!=', 'cancelled')->where('order_id', $this->getAttribute('id'))->sum('executed');
    }

    public function getBalanceAttribute($value)
    {
        $executed = DealingSheet::where('status', 'approved')->where('order_id', $this->getAttribute('id'))->sum('executed');

        return $this->getAttribute('volume') - $executed;
    }

    public function getStatusAttribute($value)
    {
        $executed = DealingSheet::where('status', 'approved')->where('order_id', $this->getAttribute('id'))->sum('executed');
        $balance = $this->getAttribute('volume') - $executed;
        if ($balance < 1) {
            return 'complete';
        } else {
            return $value;
        }
    }

    public function getSecurityAttribute()
    {
        return Security::find($this->getAttribute('security_id'));
    }

    public function getExecutionsAttribute()
    {
        return DealingSheet::where('order_id', $this->getAttribute('id'))->get();
    }

    public function getClientAttribute()
    {
        return User::find($this->getAttribute('client_id'));
    }

    public function getFilesAttribute()
    {
        return OrderDocument::where('order_id', $this->getAttribute('id'))->get();
    }
}
