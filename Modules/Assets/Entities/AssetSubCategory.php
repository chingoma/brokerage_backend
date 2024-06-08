<?php

namespace Modules\Assets\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Assets\Database\factories\AssetSubCategoryFactory;

class AssetSubCategory extends MasterModel
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [];

    protected $appends = ['category'];

    protected static function newFactory()
    {
        return AssetSubCategoryFactory::new();
    }

    public function getCategoryAttribute($key)
    {
        return AssetCategory::find($this->getAttribute('asset_category_id'));
    }
}
