<?php

namespace Modules\MarketData\Entities;

use App\Models\MasterModel;
use App\Traits\UuidForKey;
use Illuminate\Database\Eloquent\SoftDeletes;

class InvestorsData extends MasterModel
{

    
    protected $table = 'investments';
    protected $connection = 'pgsql';

    protected $fillable = ['date','client_code','fund_name','investor_name','investment', 'nav', 'units', 'redemptions', 'redemption_nav', 'redemption_units', 'net_investment', 'net_units', 'fund_nav', 'valuation', 'gain_or_loss'];

}
