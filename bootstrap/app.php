<?php

use Carbon\Carbon;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Traits\ApiResponder;

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
//            'throttle:api',
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
                    $response['message'] = 'Resource not found.';
                    return response()->json($response, 404);
                }
                if ($e instanceof \Illuminate\Validation\ValidationException) {
                    $response['message'] = 'Validation error.';
                    $response['error'] = $e->errors();
                    return response()->json($response, 422);
                }
            }
            return app(\Illuminate\Contracts\Debug\ExceptionHandler::class)->render($request, $e);
        });
    })->create();
