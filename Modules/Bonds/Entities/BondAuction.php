<?php

namespace Modules\Bonds\Entities;

use App\Traits\UuidForKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BondAuction extends Model
{
    use UuidForKey;
    use SoftDeletes;

    protected $fillable = [
        'date',
        'auction_number',
        'coupon_frequency',
        'auction_title',
        'coupon',
        'auction_date',
        'maturity_date',
        'price',
        'highest_bid',
        'lowest_bid',
        'yield',
        'calculated_yield',
        'yield_differential',
        'calculated_price',
        'price_differential',
        'bond_id',
    ];

}
