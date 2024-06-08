<?php

namespace App\Models\Accounting;

use App\Models\DealingSheet;
use App\Models\MasterModel;
use App\Traits\UuidForKey;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Modules\Bonds\Entities\BondExecution;
use Modules\Orders\Entities\Order;
use OwenIt\Auditing\Contracts\Auditable;
use Thinktomorrow\DynamicAttributes\HasDynamicAttributes;

class AllTransaction extends MasterModel implements Auditable
{
    use HasDynamicAttributes;
    use \OwenIt\Auditing\Auditable;
    use SoftDeletes;
    use UuidForKey;

    protected $table = 'transactions';

    protected $dynamicKeys = ['reject_resoan'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    protected $appends = [
        'client',
        'type',
        'order_type',
    ];

    public function getOrderTypeAttribute()
    {
        $type = '';
        if (strtolower($this->getAttribute('category')) == 'custodian') {
            $type = 'equity';
            $order = DB::table('bond_orders')->find($this->getAttribute('order_id'));
            if (empty($order)) {
                $type = 'bond';
            }
        }

        return $type;
    }

    public function getAmountAttribute($value)
    {

        if (strtolower($this->getAttribute('category')) == 'order' || strtolower($this->getAttribute('category')) == 'custodian') {
            $sheet = DealingSheet::where('slip_no', $this->getAttribute('reference'))->first();

            return $sheet->payout ?? 0;
        } elseif (strtolower($this->getAttribute('category')) == 'bond') {
            $sheet = BondExecution::where('slip_no', $this->getAttribute('reference'))->first();

            return $sheet->payout ?? 0;
        } else {
            return $value;
        }

    }

    public function getTypeAttribute()
    {
        if (strtolower($this->getAttribute('category')) == 'receipt') {
            return 'Wallet';
        }

        if (strtolower($this->getAttribute('category')) == 'payment') {
            return 'Wallet';
        }

        if (strtolower($this->getAttribute('category')) == 'order') {
            return 'Wallet';
        }

        if (strtolower($this->getAttribute('category')) == 'bond') {
            return 'Wallet';
        }

        if (strtolower($this->getAttribute('category')) == 'custodian') {
            return 'Custodian';
        }

        if (strtolower($this->getAttribute('category')) == 'expense') {
            return 'Wallet';
        }

        if (strtolower($this->getAttribute('category')) == 'journal') {
            return 'Wallet';
        }

        if (strtolower($this->getAttribute('category')) == 'invoice') {
            return 'Wallet';
        }

        return 'N/A';
    }

    //    public function getReferenceAttribute($value){
    //
    //        if(strtolower($this->getAttribute("category")) == "receipt") {
    //            return $this->getAttribute("uid");
    //        }
    //
    //        if(strtolower($this->getAttribute("category")) == "payment") {
    //            return $this->getAttribute("uid");
    //        }
    //
    //        if(strtolower($this->getAttribute("category")) == "order") {
    //            return $this->getAttribute("reference");
    //        }
    //
    //        if(strtolower($this->getAttribute("category")) == "bond") {
    //            return $this->getAttribute("reference");
    //        }
    //
    //        if(strtolower($this->getAttribute("category")) == "custodian") {
    //            return $this->getAttribute("reference");
    //        }
    //
    //        if(strtolower($this->getAttribute("category")) == "expense") {
    //            return $this->getAttribute("uid");
    //        }
    //
    //        if(strtolower($this->getAttribute("category")) == "journal") {
    //            return $this->getAttribute("uid");
    //        }
    //
    //        if(strtolower($this->getAttribute("category")) == "invoice") {
    //            return $this->getAttribute("uid");
    //        }
    //
    //         return $this->getAttribute("uid");
    //    }

    public function getClientAttribute()
    {
        return DB::table('users')->select(['name', 'id'])->find($this->getAttribute('client_id'));
    }
}
