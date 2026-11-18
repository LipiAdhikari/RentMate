<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Don't log common HTTP errors to reduce log volume
        $exceptions->dontReport([
            \Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class,
            \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException::class,
            \Illuminate\Session\TokenMismatchException::class,
            \Illuminate\Auth\AuthenticationException::class,
        ]);

        // Handle AuthenticationException - redirect to login instead of 500 error
        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, $request) {
            return redirect()->guest(route('login'));
        });

        $exceptions->render(function (\Illuminate\Database\QueryException $e, $request) {
            Log::error('Database error: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            if (config('app.debug')) {
                return response()->json([
                    'message' => 'Database error',
                    'error' => $e->getMessage(),
                ], 500);
            }

            return response()->view('errors.500', [
                'message' => 'Database connection error. Please check your database connection settings.',
            ], 500);
        })->render(function (\Illuminate\Session\TokenMismatchException $e, $request) {
            Log::warning('Session expired: ' . $e->getMessage());
            return back()->withErrors(['session' => 'Your session has expired. Please refresh the page and try again.'])->withInput();
        })->render(function (\Throwable $e, $request) {
            Log::error('Unhandled exception: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

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
