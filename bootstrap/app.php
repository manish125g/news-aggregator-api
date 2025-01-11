<?php

use Carbon\Carbon;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Log;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->api(prepend: [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (Throwable $e, $request) {
            $metaDefinition = [
                'timestamp' => Carbon::now(),
                'version' => env('API_VERSION')
            ];
            $response = [
                'success' => false,
                'message' => '',
                'meta' => $metaDefinition
            ];
            if ($request->is('api/*')) {
                if ($e instanceof \Illuminate\Auth\AuthenticationException) {
                    $response['message'] = 'Unauthenticated.';
                    return response()->json($response, 401);
                }

                if ($e instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
                    $response['message'] = 'Page not found.';
                    return response()->json($response, 404);
                }
                if ($e instanceof \Illuminate\Validation\ValidationException) {
                    $response['message'] = 'Validation error.';
                    $response['errors'] = $e->errors();
                    return response()->json($response, 422);
                }
                if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                    $response['message'] = 'Resource not found.';
                    return response()->json($response, 404);
                }
                if ($e instanceof \Illuminate\Database\UniqueConstraintViolationException) {
                    $response['message'] = 'Database error.';
                    Log::error($e->getMessage());
                    return response()->json($response, 500);
                }
                if ($e instanceof \Illuminate\Database\QueryException) {
                    $response['message'] = 'Database error.';
                    Log::error($e->getMessage());
                    return response()->json($response, 500);
                }
                // For handling all other type of errors
                if ($e instanceof Exception) {
                    $response['message'] = "Something went wrong.";
                    Log::error($e->getMessage());
                    return response()->json($response, 500);
                }
            }
            return app(\Illuminate\Contracts\Debug\ExceptionHandler::class)->render($request, $e);
        });
    })->create();
