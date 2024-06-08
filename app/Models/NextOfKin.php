<?php

namespace App\Models;

use App\Traits\CurrentYearTrait;
use App\Traits\UuidForKey;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class NextOfKin extends MasterModel implements Auditable
{
    use CurrentYearTrait;
    use \OwenIt\Auditing\Auditable;
    use SoftDeletes;
    use UuidForKey;

    protected $fillable = ['parent'];

    public function generateTags(): array
    {
        return [
            'admin, nextOfKin',
        ];
    }
}
