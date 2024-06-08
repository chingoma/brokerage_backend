<?php

namespace App\Models;

use App\Traits\CurrentYearTrait;
use App\Traits\UuidForKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class SimpleProfile extends MasterModel
{
    use CurrentYearTrait;
    use HasFactory;
    use HasFactory;
    use SoftDeletes;
    use UuidForKey;

    protected $table = 'profiles';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime:Y-m-d',
        'updated_at' => 'datetime:Y-m-d',
    ];

    public function getNameAttribute($key)
    {
        return ucwords(strtolower($key));
    }
}
