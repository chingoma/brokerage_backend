<?php

namespace Modules\Calendar\Entities;

use App\Models\MasterModel;
use App\Traits\UuidForKey;
use Illuminate\Database\Eloquent\SoftDeletes;

class Calendar extends MasterModel implements \OwenIt\Auditing\Contracts\Auditable
{
    use \App\Traits\UuidForKey;
    use \OwenIt\Auditing\Auditable;
    use SoftDeletes;
    use UuidForKey;

    protected $fillable = [];

    protected $appends = ['classNames'];

    public function generateTags(): array
    {
        return [
            'admin, calendar',
        ];
    }

    public function getClassNamesAttribute(): string
    {
        return match (strtolower($this->getAttribute('calendar'))) {
            'holiday' => 'bg-info text-white text-light',
            default => '',
        };
    }
}
