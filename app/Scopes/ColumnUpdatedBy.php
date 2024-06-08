<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ColumnUpdatedBy implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        if (! Schema::hasColumn('orders', 'updated_by')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->string('updated_by');
            });
        }

        if (! Schema::hasColumn('dealing_sheets', 'updated_by')) {
            Schema::table('dealing_sheets', function (Blueprint $table) {
                $table->string('updated_by');
            });
        }

        if (\Auth::check()) {
            $user = \Auth::user();

        }
    }
}
