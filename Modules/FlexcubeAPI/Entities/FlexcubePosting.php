<?php

namespace Modules\FlexcubeAPI\Entities;

use App\Models\MasterModel;
use App\Traits\UuidForKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FlexcubePosting extends MasterModel
{

    use SoftDeletes;
    Use UuidForKey;
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [];

}
