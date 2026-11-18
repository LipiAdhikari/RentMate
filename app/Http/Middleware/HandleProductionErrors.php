<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;
use Illuminate\Session\TokenMismatchException;

class HandleProductionErrors
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            return $next($request);
        } catch (\Throwable $e) {
            // Log the actual error for debugging
            Log::error('Production Error: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'url' => $request->fullUrl(),
                'method' => $request->method(),
            ]);

            // If APP_KEY is missing, show specific error
            if (! config('app.key')) {
                return response()->view('errors.500', [
                    'message' => 'Application key (APP_KEY) is not configured. Please set APP_KEY in your Railway environment variables.',
                    'showDetails' => false,
                ], 500);
            }

            // If it's a database error
            if ($e instanceof QueryException) {
                return response()->view('errors.500', [
                    'message' => 'Database connection error. Please check your Supabase connection settings in Railway Variables.',
                    'details' => config('app.debug') ? $e->getMessage() : null,
                    'showDetails' => config('app.debug'),
                ], 500);
            }

            // If it's a session/CSRF error
            if ($e instanceof TokenMismatchException) {
                return back()->withErrors(['session' => 'Your session has expired. Please refresh the page and try again.'])->withInput();
            }

            // Generic error
            return response()->view('errors.500', [
                'message' => 'Something went wrong. Please try again later.',
                'details' => config('app.debug') ? $e->getMessage() : null,
                'showDetails' => config('app.debug'),
            ], 500);
        }
    }
}
