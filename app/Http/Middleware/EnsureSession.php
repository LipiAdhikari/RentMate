<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSession
{
    public function handle(Request $request, Closure $next): Response
    {
        // Ensure session is started
        $request->session()->token();

        return $next($request);
    }
}
