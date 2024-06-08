<?php

namespace Modules\EndOfDayReport\Entities;

use App\Models\MasterModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class EndDayReport extends MasterModel
{
    use SoftDeletes;

    protected $fillable = [];

    public function generateTags(): array
    {
        return [
            'admin, end-of-day-report',
        ];
    }
}
