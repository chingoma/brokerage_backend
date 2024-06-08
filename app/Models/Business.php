<?php

namespace App\Models;

use App\Helpers\Helper;
use App\Traits\UuidForKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Business extends Model
{
    use SoftDeletes;
    use UuidForKey;

    protected $loggable = ['name', 'created_at', 'updated_at'];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['financial_year'];

    public function getLogoAttribute($value)
    {
        if (! empty($value)) {
            return url('/').'/storage/'.$value;
        } else {
            return '';
        }
    }

    public function getFinancialYearAttribute()
    {
        return Helper::financialYear($this->getAttribute('id'))->id;
    }
}
