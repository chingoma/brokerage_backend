<?php

namespace Modules\Orders\Entities;

use App\Models\MasterModel;
use App\Traits\UuidForKey;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class OrderDetails extends MasterModel
{
    use SoftDeletes;
    use UuidForKey;

    protected $table = "orders";
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime:Y-m-d',
        'updated_at' => 'datetime:Y-m-d',
    ];

    protected $appends = ['security','custodian','executions'];

    public function getExecutionsAttribute()
    {
        return DB::table('dealing_sheets')->where('order_id', $this->getAttribute('id'))->get();
    }

    public function getSecurityAttribute()
    {
        $result = \DB::table('securities')->select(['fullname','name'])
            ->where('id',$this->getAttribute('security_id'))
            ->first();
        if(empty($result)){
            return "";
        }else{
            return  $result->fullname.' ('.$result->name.')';
        }
    }

    public function getCustodianAttribute()
    {
        return \DB::table('custodians')
            ->select('name')
            ->where('id',$this->getAttribute('custodian_id'))
            ->first()->name??"";
    }

    public function getPayoutAttribute($value)
    {
        return round(floatval($value), 2);
    }

    public function getTotalFeesAttribute($value)
    {
        return round(floatval($value), 2);
    }

    public function getDseAttribute($value)
    {
        return round(floatval($value), 2);
    }

    public function getCdsAttribute($value)
    {
        return round(floatval($value), 2);
    }

    public function getFidelityAttribute($value)
    {
        return round(floatval($value), 2);
    }

    public function getCmsaAttribute($value)
    {
        return round(floatval($value), 2);
    }

    public function getVatAttribute($value)
    {
        return round(floatval($value), 2);
    }
}
