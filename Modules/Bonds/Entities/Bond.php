<?php

namespace Modules\Bonds\Entities;

use App\Models\MasterModel;
use App\Traits\UuidForKey;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bond extends MasterModel implements \OwenIt\Auditing\Contracts\Auditable
{
    use \App\Traits\UuidForKey;
    use \OwenIt\Auditing\Auditable;
    use SoftDeletes;
    use UuidForKey;

    public function generateTags(): array
    {
        return [
            'admin, bond',
        ];
    }

    protected $fillable = [];
}
