<?php

namespace Modules\CRM\Entities;

use App\Models\MasterModel;
use App\Traits\UuidForKey;

class ClientCustodian extends MasterModel
{
    use UuidForKey;

    protected $table = 'users';

    protected $fillable = [];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'custodians',
        'category',
    ];

    public function getCategoryAttribute()
    {
        return CustomerCategory::find($this->getAttribute('category_id'));
    }

    public function getCustodiansAttribute()
    {
        return CustomerCustodian::where('user_id', $this->getAttribute('id'))->get();
    }

    public function scopeCustomers()
    {
        return $this->whereIn('type', ['individual', 'corporate', 'joint', 'minor']);
    }
}
