<?php

namespace App\Http\Controllers;

use App\Models\AhpSession;
use App\Models\AhpComparison;
use App\Models\AhpResult;
use App\Models\Kriteria;
use App\Models\PengajuanBahanAjar;
use App\Services\AhpService;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AhpController extends Controller
{
    protected AhpService $ahpService;

    public function __construct(AhpService $ahpService)
    {
        $this->ahpService = $ahpService;
    }

    /**
     * Calculate AHP weights and consistency for a session
     */
    public function calculateWeights(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'session_id' => 'required|integer|exists:ahp_sessions,id'
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors(), 'Data input tidak valid');
        }

        try {
            $sessionId = $request->input('session_id');

            // Check if matrix is complete
            $isComplete = AhpComparison::isMatrixComplete($sessionId);
            if (!$isComplete) {
                return ApiResponse::matrixIncomplete('Matriks perbandingan belum lengkap. Silakan lengkapi semua perbandingan yang diperlukan.');
            }

            // Calculate weights and consistency
            $results = $this->ahpService->calculateWeightsAndConsistency($sessionId);

            // Save results to database
            if (!empty($results['weights'])) {
                $this->saveResults($sessionId, $results);
            }

            return ApiResponse::success($results, 'Bobot AHP berhasil dihitung dan disimpan');
        } catch (\Exception $e) {
            Log::error('AHP calculation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return ApiResponse::ahpCalculationError('Gagal menghitung bobot AHP: ' . $e->getMessage(), [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Save AHP results to database
     */
    private function saveResults(int $sessionId, array $results): void
    {
        try {
            if (empty($results['weights'])) {
                Log::warning('No weights to save', ['session_id' => $sessionId]);
                return;
            }

            DB::beginTransaction();

            // Delete existing results
            AhpResult::where('ahp_session_id', $sessionId)->delete();

            // Save new results
            foreach ($results['weights'] as $criteriaName => $weight) {
                $criteria = Kriteria::where('nama_kriteria', $criteriaName)->first();
                if ($criteria && $weight > 0) {
                    AhpResult::create([
                        'ahp_session_id' => $sessionId,
                        'kriteria_id' => $criteria->id,
                        'bobot' => $weight,
                    ]);
                }
            }

            DB::commit();

            Log::info('AHP results saved successfully', [
                'session_id' => $sessionId,
                'weights_count' => count($results['weights']),
                'consistency_ratio' => $results['consistency_ratio']
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to save AHP results', [
                'session_id' => $sessionId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Generate complete AHP results including rankings
     */
    public function generateResults(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'session_id' => 'required|integer|exists:ahp_sessions,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $sessionId = $request->input('session_id');

            // Generate complete AHP results
            $results = $this->ahpService->generate($sessionId);

            if (!$results['success']) {
                return response()->json($results, 400);
            }

            return response()->json($results);
        } catch (\Exception $e) {
            Log::error('AHP generation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to generate AHP results: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get AHP results for a session
     */
    public function getResults(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'session_id' => 'required|integer|exists:ahp_sessions,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $sessionId = $request->input('session_id');

            $results = $this->ahpService->getSessionResults($sessionId);

            if (!$results) {
                return response()->json([
                    'success' => false,
                    'message' => 'No AHP results found for this session'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $results
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get AHP results', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get AHP results: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get rankings for a session
     */
    public function getRankings(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'session_id' => 'required|integer|exists:ahp_sessions,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $sessionId = $request->input('session_id');

            $rankings = $this->ahpService->getSessionRankings($sessionId);

            return response()->json([
                'success' => true,
                'data' => $rankings
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get rankings', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get rankings: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Validate matrix completeness
     */
    public function validateMatrix(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'session_id' => 'required|integer|exists:ahp_sessions,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $sessionId = $request->input('session_id');

            $isComplete = AhpComparison::isMatrixComplete($sessionId);
            $statistics = AhpComparison::getSessionStatistics($sessionId);

            return response()->json([
                'success' => true,
                'data' => [
                    'is_complete' => $isComplete,
                    'statistics' => $statistics
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to validate matrix', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to validate matrix: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Save matrix comparisons
     */
    public function saveMatrix(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'session_id' => 'required|integer|exists:ahp_sessions,id',
            'matrix_data' => 'required|array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $sessionId = $request->input('session_id');
            $matrixData = $request->input('matrix_data');

            // Validate matrix data
            $validation = AhpComparison::validateMatrixData($sessionId, $matrixData);
            if (!$validation['valid']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Matrix data validation failed',
                    'data' => $validation
                ], 400);
            }

            // Save matrix
            $saved = AhpComparison::saveMatrixForSession($sessionId, $matrixData);

            if (!$saved) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to save matrix data'
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'Matrix data saved successfully',
                'data' => $validation
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to save matrix', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to save matrix: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get matrix for a session
     */
    public function getMatrix(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'session_id' => 'required|integer|exists:ahp_sessions,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $sessionId = $request->input('session_id');

            $matrix = AhpComparison::getMatrixForSession($sessionId);
            $criteria = Kriteria::orderBy('id')->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'matrix' => $matrix,
                    'criteria' => $criteria
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get matrix', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get matrix: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get session statistics
     */
    public function getSessionStatistics(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'session_id' => 'required|integer|exists:ahp_sessions,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $sessionId = $request->input('session_id');

            $statistics = AhpComparison::getSessionStatistics($sessionId);
            $rankings = $this->ahpService->getSessionRankings($sessionId);

            return response()->json([
                'success' => true,
                'data' => [
                    'statistics' => $statistics,
                    'rankings' => $rankings
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get session statistics', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get session statistics: ' . $e->getMessage()
            ], 500);
        }
    }
}
