<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class ThrottleConnectorTest
{
    public function handle(Request $request, Closure $next): Response
    {
        $key = 'connector_test:' . $request->user()->id;

        if (RateLimiter::tooManyAttempts($key, 5)) {
            return response()->json([
                'message' => 'Trop de tests. Réessayez dans ' . RateLimiter::availableIn($key) . ' secondes.',
            ], 429);
        }

        RateLimiter::hit($key, 60);
        return $next($request);
    }
}