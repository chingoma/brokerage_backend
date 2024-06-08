<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next): mixed
    {

        $response = $next($request);

        if (Auth::check()) {
            $user = \auth()->user();
            if (! $user->is_admin) {
                return response()->json(['message' => 'Not Authorized'], 401);
            }

        }

        return $response;
    }
}
