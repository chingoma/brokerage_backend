<?php

namespace Modules\Orders\Entities;

use App\Models\MasterModel;
use App\Models\Security;
use App\Models\User;
use App\Traits\UuidForKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class OrderReconcile extends MasterModel implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;
    use SoftDeletes;
    use UuidForKey;

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
        'client',
        'security',
        'bond',
    ];

    public function getSecurityAttribute()
    {
        return Security::find($this->getAttribute('security_id'));
    }

    public function getBondAttribute()
    {
        return Security::find($this->getAttribute('security_id'));
    }

    public function getClientAttribute()
    {
        $profile = User::find($this->getAttribute('client_id'));
        if (empty($profile)) {
            return '';
        }

        return $profile->profile;
    }
}
