<?php

namespace App\Http\Middleware;

use Closure;

class EnsureJsonAcceptHeader
{
    public function handle($request, Closure $next)
    {
        if ($request->header('Accept') !== 'application/json') {
            return response()->json(['error' => 'Invalid Accept header'], 406);
        }

        return $next($request);
    }
}
