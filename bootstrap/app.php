<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Log;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');

        // Add APP_KEY check middleware
        $middleware->appendToGroup('web', [
            \App\Http\Middleware\EnsureAppKey::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Illuminate\Database\QueryException $e, $request) {
            if (config('app.debug')) {
                return response()->json([
                    'message' => 'Database error',
                    'error' => $e->getMessage(),
                ], 500);
            }

            Log::error('Database error: '.$e->getMessage());

            return response()->view('errors.500', [], 500);
        })->render(function (\Illuminate\Session\TokenMismatchException $e, $request) {
            return back()->withErrors(['session' => 'Your session has expired. Please refresh the page and try again.'])->withInput();
        })->render(function (\Throwable $e, $request) {
            Log::error('Unhandled exception: '.$e->getMessage().' at '.$e->getFile().':'.$e->getLine());

            if (config('app.debug')) {
                return response()->json([
                    'message' => 'Server error',
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ], 500);
            }

            return response()->view('errors.500', [], 500);
        });
    })->create();
