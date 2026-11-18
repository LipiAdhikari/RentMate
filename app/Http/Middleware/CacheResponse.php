<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CacheResponse
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only cache GET requests
        if (!$request->isMethod('GET')) {
            return $next($request);
        }

        $cacheKey = 'response_' . md5($request->fullUrl());
        $cacheTime = 300; // 5 minutes

        // Check if we have a cached response
        $cached = \Illuminate\Support\Facades\Cache::get($cacheKey);
        if ($cached) {
            return response($cached['content'], $cached['status'], $cached['headers']);
        }

        $response = $next($request);

        // Cache successful GET responses (200 OK)
        if ($response->getStatusCode() === 200 && $request->isMethod('GET')) {
            \Illuminate\Support\Facades\Cache::put($cacheKey, [
                'content' => $response->getContent(),
                'status' => $response->getStatusCode(),
                'headers' => $response->headers->all(),
            ], $cacheTime);
        }

        return $response;
    }
}
