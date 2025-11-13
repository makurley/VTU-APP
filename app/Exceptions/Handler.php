<?php

namespace App\Exceptions;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Throwable;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }
    protected function invalidJson($request, ValidationException $exception): JsonResponse
    {
        return new JsonResponse([
            'status' => 'error',
            'message' => $exception->getMessage(),
            // 'errors' => $exception->errors(),
        ], $exception->status);

    }
    public function render($request, Throwable $exception)
    {
        if ($request->expectsJson()) {
            // Custom JSON response for API requests
            $statusCode = 500; // Default status code for internal server error
            $response = [
                'status' => 'error',
                'message' => 'Something went wrong.',
            ];

            // Customize the response based on the exception type
            if ($exception instanceof HttpException) {
                $statusCode = $exception->getStatusCode();
                $response['status'] = "error";
                $response['message'] = $exception->getMessage();
            }
            if ($exception instanceof ValidationException) {
                return new JsonResponse([
                    'status' => 'error',
                    'message' => $exception->getMessage(),
                    'errors' => $exception->errors(),
                ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
            }
            if ($exception instanceof ModelNotFoundException) {
                return new JsonResponse([
                    'status' => 'error',
                    'message' => $exception->getMessage(),
                ], 404);
            }
            if ($exception instanceof ThrottleRequestsException) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Too Many Requests.',
                ], 429);
            }

            // Add more specific error handling if needed, for other exception types

            return response()->json($response, $statusCode);
        }

        return parent::render($request, $exception);
    }
}
