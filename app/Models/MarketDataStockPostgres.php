<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarketDataStockPostgres extends Model
{
    protected $connection = "pgsql";

    protected $table = "dse_stock_market_data";
}
