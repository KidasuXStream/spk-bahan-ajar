<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ApiResponse
{
    /**
     * Success response
     */
    public static function success($data = null, string $message = 'Success', int $statusCode = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'timestamp' => now()->toISOString(),
            'status_code' => $statusCode
        ], $statusCode);
    }

    /**
     * Error response
     */
    public static function error(string $message = 'Error occurred', string $errorCode = 'ERROR', int $statusCode = 400, $details = null): JsonResponse
    {
        $response = [
            'success' => false,
            'error' => [
                'code' => $errorCode,
                'message' => $message
            ],
            'timestamp' => now()->toISOString(),
            'status_code' => $statusCode
        ];

        if ($details && config('app.debug')) {
            $response['error']['details'] = $details;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Validation error response
     */
    public static function validationError($errors, string $message = 'Validation failed'): JsonResponse
    {
        return response()->json([
            'success' => false,
            'error' => [
                'code' => 'VALIDATION_ERROR',
                'message' => $message,
                'details' => $errors
            ],
            'timestamp' => now()->toISOString(),
            'status_code' => 422
        ], 422);
    }

    /**
     * Not found response
     */
    public static function notFound(string $message = 'Resource not found', string $errorCode = 'NOT_FOUND'): JsonResponse
    {
        return self::error($message, $errorCode, 404);
    }

    /**
     * Unauthorized response
     */
    public static function unauthorized(string $message = 'Unauthorized access', string $errorCode = 'UNAUTHORIZED'): JsonResponse
    {
        return self::error($message, $errorCode, 401);
    }

    /**
     * Forbidden response
     */
    public static function forbidden(string $message = 'Access forbidden', string $errorCode = 'FORBIDDEN'): JsonResponse
    {
        return self::error($message, $errorCode, 403);
    }

    /**
     * Server error response
     */
    public static function serverError(string $message = 'Internal server error', string $errorCode = 'INTERNAL_ERROR'): JsonResponse
    {
        return self::error($message, $errorCode, 500);
    }

    /**
     * AHP specific error responses
     */
    public static function ahpCalculationError(string $message = 'AHP calculation failed', $details = null): JsonResponse
    {
        return self::error($message, 'AHP_CALCULATION_ERROR', 500, $details);
    }

    public static function matrixIncomplete(string $message = 'Matrix comparison incomplete', $details = null): JsonResponse
    {
        return self::error($message, 'MATRIX_INCOMPLETE', 400, $details);
    }

    public static function inconsistentMatrix(string $message = 'Matrix is inconsistent', $details = null): JsonResponse
    {
        return self::error($message, 'INCONSISTENT_MATRIX', 400, $details);
    }

    public static function sessionNotFound(string $message = 'AHP session not found'): JsonResponse
    {
        return self::notFound($message, 'SESSION_NOT_FOUND');
    }

    public static function criteriaNotFound(string $message = 'Criteria not found'): JsonResponse
    {
        return self::notFound($message, 'CRITERIA_NOT_FOUND');
    }

    /**
     * Export specific error responses
     */
    public static function exportError(string $message = 'Export failed', $details = null): JsonResponse
    {
        return self::error($message, 'EXPORT_ERROR', 500, $details);
    }

    /**
     * File upload error responses
     */
    public static function fileUploadError(string $message = 'File upload failed', $details = null): JsonResponse
    {
        return self::error($message, 'FILE_UPLOAD_ERROR', 400, $details);
    }

    /**
     * Permission error responses
     */
    public static function permissionDenied(string $message = 'Permission denied', $details = null): JsonResponse
    {
        return self::forbidden($message, $details);
    }

    /**
     * Paginated response
     */
    public static function paginated($data, $pagination, string $message = 'Data retrieved successfully'): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'pagination' => [
                'current_page' => $pagination->currentPage(),
                'last_page' => $pagination->lastPage(),
                'per_page' => $pagination->perPage(),
                'total' => $pagination->total(),
                'from' => $pagination->firstItem(),
                'to' => $pagination->lastItem()
            ],
            'timestamp' => now()->toISOString(),
            'status_code' => 200
        ], 200);
    }

    /**
     * Created response
     */
    public static function created($data = null, string $message = 'Resource created successfully'): JsonResponse
    {
        return self::success($data, $message, 201);
    }

    /**
     * Updated response
     */
    public static function updated($data = null, string $message = 'Resource updated successfully'): JsonResponse
    {
        return self::success($data, $message, 200);
    }

    /**
     * Deleted response
     */
    public static function deleted(string $message = 'Resource deleted successfully'): JsonResponse
    {
        return self::success(null, $message, 200);
    }

    /**
     * No content response
     */
    public static function noContent(): JsonResponse
    {
        return response()->json(null, 204);
    }
}
