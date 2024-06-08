<?php

namespace Modules\CRM\Entities;

use App\DTOs\Customers\CustomerDTO;
use App\Models\MasterModel;
use App\Traits\UuidForKey;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class CustomerList extends MasterModel implements Auditable
{
    use \OwenIt\Auditing\Auditable;
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

    protected $appends = ['category'];

    public function getCategoryAttribute()
    {
        $data = CustomerCategory::find($this->getAttribute('category_id'));
        if (! empty($data)) {
            return CustomerDTO::fromModel($data);
        }

        return '';
    }
}
