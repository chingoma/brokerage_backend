<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class IsAllowedIp
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $response = $next($request);
        $ips = Config::get('allowed-ips');
        if (! in_array($request->ip(), $ips)) {
            return response()->json(['message' => 'Not Authorized '.$request->ip()], 401);
        }

        return $response;
    }
}
