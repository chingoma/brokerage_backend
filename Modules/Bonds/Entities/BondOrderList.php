<?php

namespace Modules\Bonds\Entities;

use App\Helpers\Clients\Profile;
use App\Models\MasterModel;
use App\Models\User;
use App\Traits\UuidForKey;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use OwenIt\Auditing\Auditable;

class BondOrderList extends MasterModel implements \OwenIt\Auditing\Contracts\Auditable
{
    use UuidForKey;
    use Auditable;
    use SoftDeletes;
    use UuidForKey;

    protected $table = 'bond_orders';

    public function generateTags(): array
    {
        return [
            'admin, bond-order',
        ];
    }

    protected $fillable = [];

    protected $appends = ['customer','bond','custodian'];


    public function getCustomerAttribute()
    {
        $result = \DB::table('users')->select(['name'])
            ->where('id',$this->getAttribute('client_id'))
            ->first();
        if(empty($result)){
            return "";
        }else{
            return  $result->name;
        }
    }

    public function getCustodianAttribute()
    {
        return \DB::table('custodians')
            ->select('name')
            ->where('id',$this->getAttribute('custodian_id'))
            ->first()->name??"";
    }

    public function getBondAttribute()
    {
        return \DB::table('bonds')
            ->select('security_name')
            ->where('id',$this->getAttribute('bond_id'))
            ->first()->security_name??"";
    }

}
