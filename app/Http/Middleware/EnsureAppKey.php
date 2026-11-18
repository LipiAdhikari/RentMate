<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAppKey
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('app.key')) {
            return response()->view('errors.500', [
                'message' => 'Application key (APP_KEY) is not configured. Please set APP_KEY in your Railway environment variables.',
                'details' => config('app.debug') ? null : null,
            ], 500);
        }

        return $next($request);
    }
}
