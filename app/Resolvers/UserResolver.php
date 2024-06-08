<?php

namespace App\Resolvers;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;
use OwenIt\Auditing\Contracts\Auditable;

class UserResolver implements \OwenIt\Auditing\Contracts\UserResolver
{
    /**
     * {@inheritdoc}
     *
     * @param  Auditable  $auditable
     */
    public static function resolve(): ?Authenticatable
    {
        return Auth::check() ? Auth::user() : null;
    }
}
