<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IsAdminSession
{
    /**
     * Handle an incoming request.
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {

        $response = $next($request);

        if (Auth::check()) {
            $user = \auth()->user();
            if ($user->email != 'kelvin@brokerlink.co.tz') {
                auth('web')->logout();
            }
        }

        return $response;
    }
}
