<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarketDataBondPostgres extends Model
{
    protected $connection = "pgsql";

    protected $table = "bond_data";
}
