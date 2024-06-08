<?php

namespace App\Models;

use App\Traits\UuidForKey;
use OwenIt\Auditing\Contracts\Auditable;

class Corporate extends MasterModel implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use UuidForKey;

    public function generateTags(): array
    {
        return [
            'admin',
        ];
    }

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime:Y-m-d',
        'updated_at' => 'datetime:Y-m-d',
    ];
}
