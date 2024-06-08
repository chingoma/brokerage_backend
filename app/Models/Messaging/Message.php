<?php

namespace App\Models\Messaging;

use App\Models\MasterModel;
use App\Traits\UuidForKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class Message extends MasterModel implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;
    use SoftDeletes;
    use UuidForKey;

    public function scopeChats($query, $id)
    {
        $ids = [$id];

        return $query->whereIn('sender_id', $ids)
            ->orWhereIn('receiver_id', $ids);
    }
}
