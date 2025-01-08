<?php

namespace App\Exceptions;

use App\Traits\ApiResponder;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;

class Handler extends Exception
{
    use ApiResponder;

    public function render($request, \Throwable $exception)
    {
//        if ($request->is('api/*')) {
//            if ($exception instanceof ValidationException) {
//                return $this->sendError('Validation error', $exception->errors(), 422);
//            }
//            if ($exception instanceof AuthenticationException) {
//                return $this->sendError('Unauthenticated', [], 401);
//            }
//            if ($exception instanceof ModelNotFoundException) {
//                return $this->sendError('Resource not found', [], 404);
//            }
//            return $this->sendError('Server error', [$exception->getMessage()], 500);
//        }
//
//        return parent::render($request, $exception);
            if ($request->expectsJson() || $request->isJson()) {   //add Accept: application/json in request
                return $this->sendError('Validation error', $exception->errors(), 422);
//                return $this->handleApiException($request, $exception);
            } else {
                $retval = parent::render($request, $exception);
            }

            return $retval;
    }

    private function handleApiException($request, Exception $exception)
    {
        $exception = $this->prepareException($exception);

        if ($exception instanceof \Illuminate\Http\Exception\HttpResponseException) {
            $exception = $exception->getResponse();
        }

        if ($exception instanceof \Illuminate\Auth\AuthenticationException) {
            $exception = $this->unauthenticated($request, $exception);
        }

        if ($exception instanceof \Illuminate\Validation\ValidationException) {
            $exception = $this->convertValidationExceptionToResponse($exception, $request);
        }

        return $this->sendError('Validation Failed', $exception, 422);
    }
}
