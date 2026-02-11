<?php

use App\Domain\Exceptions\EntityNotFoundException;
use App\Domain\Exceptions\ExternalServiceException;
use App\Domain\Exceptions\RateLimitExceededException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // middlewares would go here
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        /*
         * Centralized error handling: map domain exceptions to HTTP status codes.
         * This single configuration point makes it easy to add new domain
         * exceptions and their corresponding HTTP status codes.
         */
        $exceptionStatusMap = [
            EntityNotFoundException::class => 404,
            RateLimitExceededException::class => 429,
            ExternalServiceException::class => 502,
            ValidationException::class => 422,
        ];

        $exceptions->render(function (\Throwable $e, Request $request) use ($exceptionStatusMap) {
            if (! $request->is('api/*') && ! $request->expectsJson()) {
                return null; // Let Laravel handle non-API errors normally
            }

            // Handle validation exceptions specially (they have structured errors)
            if ($e instanceof ValidationException) {
                return response()->json([
                    'error' => true,
                    'message' => 'Validation failed.',
                    'errors' => $e->errors(),
                    'status' => 422,
                ], 422);
            }

            // Map known domain exceptions to HTTP status codes
            $statusCode = 500;
            $message = 'An unexpected error occurred. Please try again later.';

            foreach ($exceptionStatusMap as $exceptionClass => $code) {
                if ($e instanceof $exceptionClass) {
                    $statusCode = $code;
                    $message = $e->getMessage();
                    break;
                }
            }

            // Log unhandled (500) errors with full trace
            if ($statusCode === 500) {
                Log::error('Unhandled API exception', [
                    'exception' => get_class($e),
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }

            return response()->json([
                'error' => true,
                'message' => $message,
                'status' => $statusCode,
            ], $statusCode);
        });
    })->create();
