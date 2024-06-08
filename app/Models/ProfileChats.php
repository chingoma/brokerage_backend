<?php

namespace App\Models;

use App\Traits\CurrentYearTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class ProfileChats extends MasterModel implements Auditable
{
    use CurrentYearTrait;
    use HasFactory;
    use HasFactory;
    use \OwenIt\Auditing\Auditable;
    use SoftDeletes;

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

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'unseenMsgs',
        'chat',
    ];

    public function getUnseenMsgsAttribute()
    {
        return 0;
    }

    public function getChatAttribute()
    {
        return null;
    }
}
