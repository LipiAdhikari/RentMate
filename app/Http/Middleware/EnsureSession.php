<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class EnsureSession
{
    public function handle(Request $request, Closure $next)
    {
        // Ensure session is started and has a token
        $request->session()->token();

        return $next($request);
    }
}
