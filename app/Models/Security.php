<?php

namespace App\Models;

use App\Traits\UuidForKey;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\MarketData\Entities\MarketData;
use Modules\Securities\Entities\SecuritySector;
use OwenIt\Auditing\Contracts\Auditable;

class Security extends MasterModel implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use SoftDeletes;
    use UuidForKey;

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime:Y-m-d',
        'updated_at' => 'datetime:Y-m-d',
    ];

    protected $appends = ["sector"];


    public function getSectorAttribute()
    {
        return \DB::table('security_sectors')->find($this->getAttribute('sector_id'))->name ?? "";
    }

    public function getLedgerAttribute($value)
    {
        return !empty($value) ? $value: "";
    }

    public function getPriceAttribute()
    {
        return MarketData::where('company_id', $this->getAttribute('id'))->orderBy('date', 'desc')->limit(1)->first()->close ?? 0;
    }
}
