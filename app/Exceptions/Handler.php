<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Throwable;
use Illuminate\Http\JsonResponse;

class Handler extends ExceptionHandler
{
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    public function render($request, Throwable $exception): JsonResponse
    {
        // Handle RouteNotFoundException
        if ($exception instanceof RouteNotFoundException) {
            return response()->json([
                'success' => false,
                'message' => 'Route not found',
                'error_code' => 'ROUTE_NOT_FOUND',
            ], 404);
        }

        // Handle AuthenticationException
        if ($exception instanceof AuthenticationException) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
                'error_code' => 'UNAUTHENTICATED',
            ], 401);
        }

        // Handle ValidationException
        if ($exception instanceof ValidationException) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
                'errors' => $exception->errors(),
            ], 422);
        }

        // Handle other exceptions
        return parent::render($request, $exception);
    }
}
