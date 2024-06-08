<?php

namespace Modules\Whatsapp\Entities;

use App\Models\MasterModel;
use App\Traits\UuidForKey;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class WhatsappSetting extends MasterModel implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use SoftDeletes;
    use UuidForKey;
}
