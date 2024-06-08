<?php

namespace Modules\MarketData\Entities;

use App\Models\MasterModel;
use App\Traits\UuidForKey;
use Illuminate\Database\Eloquent\SoftDeletes;

class MarketData extends MasterModel
{
    protected $fillable = ['market_cap','outstanding_offer','outstanding_bid','turnover','company_id', 'system_date', 'date', 'symbol', 'open', 'prev_close', 'close', 'high', 'low', 'turn_over', 'deals', 'out_standing_bid', 'out_standing_offer', 'volume', 'mcap', 'change'];

    use SoftDeletes;
    use UuidForKey;
}
