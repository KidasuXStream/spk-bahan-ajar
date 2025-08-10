<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;
use Illuminate\Support\Facades\Log;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
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
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        // Handle different types of exceptions
        $this->renderable(function (Throwable $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return $this->handleApiException($e, $request);
            }

            return $this->handleWebException($e, $request);
        });
    }

    /**
     * Handle API exceptions
     */
    protected function handleApiException(Throwable $e, Request $request): JsonResponse
    {
        $statusCode = 500;
        $errorCode = 'INTERNAL_ERROR';
        $message = 'Terjadi kesalahan internal pada sistem';

        // Determine status code and error message based on exception type
        if ($e instanceof ValidationException) {
            $statusCode = 422;
            $errorCode = 'VALIDATION_ERROR';
            $message = 'Data yang dimasukkan tidak valid';
        } elseif ($e instanceof ModelNotFoundException) {
            $statusCode = 404;
            $errorCode = 'NOT_FOUND';
            $message = 'Data yang dicari tidak ditemukan';
        } elseif ($e instanceof QueryException) {
            $statusCode = 500;
            $errorCode = 'DATABASE_ERROR';
            $message = 'Terjadi kesalahan pada database';
        } elseif ($e instanceof AuthenticationException) {
            $statusCode = 401;
            $errorCode = 'UNAUTHORIZED';
            $message = 'Anda harus login terlebih dahulu';
        } elseif ($e instanceof AuthorizationException) {
            $statusCode = 403;
            $errorCode = 'FORBIDDEN';
            $message = 'Anda tidak memiliki akses ke fitur ini';
        } elseif ($e instanceof NotFoundHttpException) {
            $statusCode = 404;
            $errorCode = 'ENDPOINT_NOT_FOUND';
            $message = 'Endpoint yang diminta tidak ditemukan';
        } elseif ($e instanceof MethodNotAllowedHttpException) {
            $statusCode = 405;
            $errorCode = 'METHOD_NOT_ALLOWED';
            $message = 'Metode HTTP yang digunakan tidak diizinkan';
        } elseif ($e instanceof HttpException) {
            $statusCode = $e->getStatusCode();
            $errorCode = 'HTTP_ERROR';
            $message = $e->getMessage() ?: 'Terjadi kesalahan HTTP';
        }

        // Log the error for debugging
        Log::error('API Exception', [
            'error_code' => $errorCode,
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'user_id' => $request->user()?->id,
            'trace' => $e->getTraceAsString()
        ]);

        // Return standardized error response
        return response()->json([
            'success' => false,
            'error' => [
                'code' => $errorCode,
                'message' => $message,
                'details' => config('app.debug') ? [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTrace()
                ] : null
            ],
            'timestamp' => now()->toISOString(),
            'path' => $request->path()
        ], $statusCode);
    }

    /**
     * Handle web exceptions
     */
    protected function handleWebException(Throwable $e, Request $request)
    {
        // Log the error
        Log::error('Web Exception', [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'user_id' => $request->user()?->id
        ]);

        // For web requests, let Laravel handle normally
        return parent::render($request, $e);
    }

    /**
     * Custom error messages for common scenarios
     */
    protected function getCustomErrorMessage(string $errorCode): string
    {
        $messages = [
            'VALIDATION_ERROR' => 'Data yang dimasukkan tidak valid. Silakan periksa kembali.',
            'NOT_FOUND' => 'Data yang dicari tidak ditemukan dalam sistem.',
            'DATABASE_ERROR' => 'Terjadi kesalahan pada database. Silakan coba lagi nanti.',
            'UNAUTHORIZED' => 'Anda harus login terlebih dahulu untuk mengakses fitur ini.',
            'FORBIDDEN' => 'Anda tidak memiliki hak akses untuk fitur ini.',
            'ENDPOINT_NOT_FOUND' => 'Halaman atau fitur yang diminta tidak ditemukan.',
            'METHOD_NOT_ALLOWED' => 'Metode yang digunakan tidak diizinkan.',
            'INTERNAL_ERROR' => 'Terjadi kesalahan internal pada sistem. Silakan coba lagi nanti.',
            'AHP_CALCULATION_ERROR' => 'Terjadi kesalahan dalam perhitungan AHP. Silakan periksa data input.',
            'MATRIX_INCOMPLETE' => 'Matriks perbandingan belum lengkap. Silakan lengkapi semua perbandingan.',
            'INCONSISTENT_MATRIX' => 'Matriks perbandingan tidak konsisten. Silakan periksa nilai perbandingan.',
            'SESSION_NOT_FOUND' => 'Session AHP tidak ditemukan atau sudah tidak aktif.',
            'CRITERIA_NOT_FOUND' => 'Kriteria yang diminta tidak ditemukan.',
            'EXPORT_ERROR' => 'Terjadi kesalahan dalam proses export data.',
            'FILE_UPLOAD_ERROR' => 'Terjadi kesalahan dalam upload file.',
            'PERMISSION_DENIED' => 'Anda tidak memiliki izin untuk melakukan aksi ini.'
        ];

        return $messages[$errorCode] ?? 'Terjadi kesalahan yang tidak diketahui.';
    }
}
