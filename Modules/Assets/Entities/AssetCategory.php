<?php

namespace Modules\Assets\Entities;

use App\Models\Accounting\Account;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Assets\Database\factories\AssetCategoryFactory;

class AssetCategory extends MasterModel
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [];

    protected $appends = ['d_account', 'c_account'];

    public function getDAccountAttribute($key)
    {
        return Account::find($this->getAttribute('debit_account'));
    }

    public function getCAccountAttribute($key)
    {
        return Account::find($this->getAttribute('credit_account'));
    }

    protected static function newFactory()
    {
        return AssetCategoryFactory::new();
    }
}
