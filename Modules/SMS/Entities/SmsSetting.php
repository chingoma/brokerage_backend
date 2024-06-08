<?php

namespace Modules\SMS\Entities;

use App\Models\MasterModel;
use App\Traits\UuidForKey;
use Illuminate\Database\Eloquent\SoftDeletes;

class SmsSetting extends MasterModel
{
    use SoftDeletes;
    use UuidForKey;

    protected $fillable = ['sms_provider'];
}
