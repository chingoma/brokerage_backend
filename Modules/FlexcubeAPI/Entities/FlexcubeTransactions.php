<?php

namespace Modules\FlexcubeAPI\Entities;

use App\Models\MasterModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Audit;

class FlexcubeTransactions extends MasterModel
{
   use SoftDeletes;
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [];

}
