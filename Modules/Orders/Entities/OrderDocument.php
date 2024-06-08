<?php

namespace Modules\Orders\Entities;

use App\Models\DocumentType;
use App\Models\MasterModel;
use App\Traits\UuidForKey;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class OrderDocument extends MasterModel implements Auditable
{
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
        'type',
    ];

    public function getTypeAttribute()
    {
        return DocumentType::find($this->getAttribute('freight_document_type_id'));
    }
}
