<?php

namespace Modules\Audits\Entities;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Audits extends Model implements \OwenIt\Auditing\Contracts\Audit
{
    use \OwenIt\Auditing\Audit;

    /**
     * {@inheritdoc}
     */
    protected $guarded = [];

    /**
     * {@inheritdoc}
     */
    protected $casts = [
        'old_values' => 'json',
        'new_values' => 'json',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
        // Note: Please do not add 'auditable_id' in here, as it will break non-integer PK models
    ];

    protected $appends = ['user', 'type'];

    public function getUserAttribute()
    {
        return \DB::table('users')->select(['name','id'])->find($this->getAttribute('user_id'));
    }

    public function getTypeAttribute()
    {
        return match (strtolower($this->getAttribute('auditable_type'))) {
            "Modules\Orders\Entities\Order" => 'order',
            "App\Models\User" => 'user',
            "App\Models\Profile" => 'profile',
            "App\Models\NextOfKin" => 'next of kin',
            "App\Models\DealingSheet" => 'contract note',
            default => '',
        };
    }

    public function getSerializedDate($date)
    {
        return $this->serializeDate($date);
    }
}
