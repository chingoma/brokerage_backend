<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class CurrentYearScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        if (! empty(auth()->user())) {
            $business = auth()->user()->profile->business;
            if (! empty($business)) {
                $builder->whereBetween('created_at', [$business->year_start, $business->year_end]);
            }
        }

    }
}
