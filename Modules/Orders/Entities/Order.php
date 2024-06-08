<?php

namespace Modules\Orders\Entities;

use App\Helpers\Clients\Profile;
use App\Models\MasterModel;
use App\Traits\UuidForKey;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use OwenIt\Auditing\Contracts\Auditable;

class Order extends MasterModel implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use SoftDeletes;
    use UuidForKey;

    public function generateTags(): array
    {
        return [
            'admin, order',
        ];
    }

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
        'executions',
        'security',
        'executed',
    ];

    public function getExecutedAttribute()
    {
        return DB::table('dealing_sheets')->where('status', 'approved')->where('status', '!=', 'cancelled')->where('order_id', $this->getAttribute('id'))->sum('executed');
    }

    public function getSecurityAttribute()
    {
        return DB::table('securities')->find($this->getAttribute('security_id'));
    }

    public function getExecutionsAttribute()
    {
        return DB::table('dealing_sheets')->where('order_id', $this->getAttribute('id'))->get();
    }

    public function getClientAttribute()
    {
        return new Profile($this->getAttribute('client_id'));
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
