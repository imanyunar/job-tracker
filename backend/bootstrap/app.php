<?php

use App\Http\Middleware\EnsureUserIsAdmin;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => EnsureUserIsAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Global JSON Exception Rendering for API requests
        $exceptions->render(function (Throwable $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                // 1. Validation Errors
                if ($e instanceof ValidationException) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Data yang dikirimkan tidak valid.',
                        'errors' => $e->errors(),
                    ], 422);
                }

                // 2. Authentication Errors
                if ($e instanceof AuthenticationException) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Sesi Anda telah berakhir atau belum terautentikasi. Silakan masuk kembali.',
                    ], 401);
                }

                // 3. Not Found Errors
                if ($e instanceof ModelNotFoundException || $e instanceof NotFoundHttpException) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Data atau endpoint yang diminta tidak ditemukan.',
                    ], 404);
                }

                // 4. Access Denied / Forbidden
                if ($e instanceof AccessDeniedHttpException) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Anda tidak memiliki hak akses untuk melakukan tindakan ini.',
                    ], 403);
                }

                // 5. Rate Limiting
                if ($e instanceof TooManyRequestsHttpException) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Terlalu banyak permintaan. Silakan tunggu beberapa saat.',
                    ], 429);
                }

                // 6. Generic / Server Errors (Hide sensitive debug info in production)
                Log::error('API Exception caught: ' . $e->getMessage(), [
                    'exception' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString(),
                ]);

                $isDebug = config('app.debug', false);

                return response()->json([
                    'success' => false,
                    'message' => $isDebug
                        ? $e->getMessage()
                        : 'Terjadi kendala pada sistem. Silakan coba beberapa saat lagi.',
                    'debug' => $isDebug ? [
                        'exception' => get_class($e),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                    ] : null,
                ], 500);
            }
        });
    })->create();
