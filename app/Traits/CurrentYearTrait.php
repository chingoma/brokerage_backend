<?php

namespace App\Traits;

use Modules\Orders\Scopes\CurrentYearScope;

trait CurrentYearTrait
{
    /**
     * Boot the soft deleting trait for a model.
     *
     * @return void
     */
    public static function bootCurrentYearTrait()
    {
        // static::addGlobalScope(new CurrentYearScope());

    }

    /**
     * Initialize the soft deleting trait for an instance.
     *
     * @return void
     */
    public function initializeCurrentYearTrait()
    {

    }

    /**
     * Perform the actual delete query on this model instance.
     *
     * @return void
     */
    protected function runCurrentYearTrait()
    {

    }
}
