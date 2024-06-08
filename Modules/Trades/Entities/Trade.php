<?php

namespace Modules\Trades\Entities;

use App\Models\DealingSheetFile;
use App\Models\MasterModel;
use App\Models\Security;
use App\Models\User;
use App\Traits\UuidForKey;
use Modules\Orders\Entities\Order;
use OwenIt\Auditing\Contracts\Auditable;

class Trade extends MasterModel implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use UuidForKey;

    protected $table = 'dealing_sheets';

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
        'settlement_date' => 'datetime:Y-m-d H:i:s',
    ];

    protected $appends = [
        'client',
        'files',
        'security',
        'order_number',
        'order_total',
        'order_balance',
        'audit',
    ];

    public function getAuditsAttribute()
    {
        return $this->audits()->with('user')->get();
    }

    public function getAuditAttribute()
    {
        if (! empty($this->audits()->latest()->first())) {
            return $this->audits()->latest()->first()->getMetadata();
        } else {
            return '';
        }
    }

    public function getClientAttribute()
    {
        return User::find($this->getAttribute('client_id'));
    }

    public function getOrderNumberAttribute()
    {
        return Order::find($this->getAttribute('order_id'))->uid ?? 0;
    }

    public function getOrderTotalAttribute()
    {
        return Order::find($this->getAttribute('order_id'))->volume ?? 0;
    }

    public function getOrderBalanceAttribute()
    {
        return Order::find($this->getAttribute('order_id'))->balance ?? 0;
    }

    public function getSecurityAttribute()
    {
        return Security::find($this->getAttribute('security_id'));
    }

    public function getFilesAttribute()
    {
        return DealingSheetFile::where('dealing_sheet_id', $this->getAttribute('id'))->get();
    }
}
