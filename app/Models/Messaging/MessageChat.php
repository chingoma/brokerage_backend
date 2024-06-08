<?php

namespace App\Models\Messaging;

use App\Models\MasterModel;
use App\Traits\UuidForKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class MessageChat extends MasterModel implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;
    use SoftDeletes;
    use UuidForKey;

    protected $table = 'profiles';

    protected $appends = [
        'chats',
    ];

    public function getChatsAttribute()
    {
        return Message::chats($this->getAttribute('id'))->get();
    }

    public function getPictureAttribute($value)
    {
        if (! empty($value)) {
            return asset('storage/'.$value);
        } else {
            return asset('logo.png');
        }
    }
}
