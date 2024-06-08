<?php

namespace Modules\Assets\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Assets\Database\factories\AssetRequestFactory;

class AssetRequest extends MasterModel
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [];

    protected static function newFactory()
    {
        return AssetRequestFactory::new();
    }
}
