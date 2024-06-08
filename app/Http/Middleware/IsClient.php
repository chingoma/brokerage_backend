<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IsClient
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
            $profile = \auth()->user();
            if ($profile->is_admin) {
                return response()->json(['message' => 'Not Authorized'], 401);
            }
            $response->withHeaders(['user', $profile]);

        }

        return $response;
    }
}
