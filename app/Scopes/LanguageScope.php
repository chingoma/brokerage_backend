<?php

namespace App\Scopes;

use App\Helpers\Helper;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Schema;

class LanguageScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        if (Schema::hasColumn($model->getTable(), 'language')) {
            $profile = Helper::farmer_profile();
            if (! empty($profile) && $profile->account_type == 'farmer') {
                $builder->where('language', App::getLocale());
            }
        }
    }
}
