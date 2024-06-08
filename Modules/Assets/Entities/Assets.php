<?php

namespace Modules\Assets\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Assets\Database\factories\AssetsFactory;

class Assets extends MasterModel
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [];

    protected $appends = ['category'];

    public function getCategoryAttribute($key)
    {
        return AssetCategory::find($this->getAttribute('category_id'));
    }

    public function getUidAttribute($value)
    {
        $data = str_ireplace('-', '/', $value);

        return strtoupper($data);
    }

    protected static function newFactory()
    {
        return AssetsFactory::new();
    }
}
