<?php

namespace Modules\CRM\Entities;

use App\Models\Accounting\AccountSetting;
use App\Models\Accounting\Transaction;
use App\Models\DealingSheet;
use App\Models\MasterModel;
use App\Models\ProfileFile;
use App\Models\Security;
use App\Models\Token;
use App\Models\User;
use App\Traits\UuidForKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Modules\Orders\Entities\OrderList;
use OwenIt\Auditing\Contracts\Auditable;

class Customer extends MasterModel
{
    use UuidForKey;

    protected $table = 'users';

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
        'jame'
    ];

    public function getJameAttribute(): string
    {
        if ($this->getAttribute('type') == 'joint') {
            $profile = \DB::table('profiles')->where('user_id', $this->getAttribute('id'))->first();
            $profileJ = \DB::table('joint_profiles')->where('user_id', $this->getAttribute('id'))->first();

            return strtoupper($profile->firstname.' '.$profile->middlename.' '.$profile->lastname.' & '.$profileJ->firstname.' '.$profileJ->middlename.' '.$profileJ->lastname);
        } else {
            return '';
        }
    }

    public function scopeCustomers()
    {
        return $this->whereIn('type', ['individual', 'corporate', 'joint', 'minor']);
    }
}
