<?php

namespace App\Services;

use App\Models\AhpSession;
use App\Models\AhpComparison;
use App\Models\AhpResult;
use App\Models\Kriteria;
use App\Models\PengajuanBahanAjar;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class AhpService
{
    // Random Index values for consistency calculation (Saaty's Random Index)
    private const RANDOM_INDEX = [
        0 => 0,
        1 => 0,
        2 => 0,
        3 => 0.52,
        4 => 0.89,
        5 => 1.11,
        6 => 1.25,
        7 => 1.35,
        8 => 1.40,
        9 => 1.45,
        10 => 1.49,
        11 => 1.52,
        12 => 1.54,
        13 => 1.56,
        14 => 1.58,
        15 => 1.59
    ];

    // AHP Scale meanings
    private const AHP_SCALE = [
        1 => 'Sama penting',
        2 => 'Sedikit menuju lebih penting',
        3 => 'Sedikit lebih penting',
        4 => 'Menuju lebih penting',
        5 => 'Lebih penting',
        6 => 'Sangat menuju lebih penting',
        7 => 'Sangat lebih penting',
        8 => 'Sangat menuju mutlak penting',
        9 => 'Mutlak lebih penting'
    ];

    /**
     * Generate complete AHP results for a session
     */
    public function generate(int $sessionId): array
    {
        try {
            DB::beginTransaction();

            Log::info('Starting AHP generation', ['session_id' => $sessionId]);

            // Validate session exists
            $session = AhpSession::findOrFail($sessionId);

            // Check if we have comparisons
            $comparisons = AhpComparison::where('ahp_session_id', $sessionId)->get();
            if ($comparisons->isEmpty()) {
                Log::warning('No AHP comparisons found', ['session_id' => $sessionId]);
                return [
                    'success' => false,
                    'message' => 'No AHP comparisons found for this session',
                    'data' => null
                ];
            }

            // Validate matrix completeness
            $validation = $this->validateSessionMatrix($sessionId);
            if (!$validation['is_complete']) {
                Log::warning('Incomplete AHP matrix', [
                    'session_id' => $sessionId,
                    'completion_rate' => $validation['completion_rate']
                ]);
            }

            // Calculate weights and consistency
            $results = $this->calculateWeightsAndConsistency($sessionId);

            // Validate calculation results
            if (empty($results['weights'])) {
                throw new \Exception('Failed to calculate AHP weights - empty results');
            }

            // Save results to database
            $this->saveResults($sessionId, $results);

            // Update rankings for all submissions in this session
            $rankingResults = $this->updateRankings($sessionId);

            // Clear related caches
            $this->clearSessionCaches($sessionId);

            DB::commit();

            return [
                'success' => true,
                'message' => 'AHP results generated successfully',
                'data' => [
                    'weights' => $results['weights'],
                    'consistency_ratio' => $results['consistency_ratio'],
                    'is_consistent' => $results['is_consistent'],
                    'lambda_max' => $results['lambda_max'],
                    'rankings_updated' => $rankingResults['updated_count']
                ]
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('AHP generation failed', [
                'session_id' => $sessionId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to generate AHP results: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Calculate AHP weights and consistency metrics
     */
    public function calculateWeightsAndConsistency(int $sessionId): array
    {
        Log::info('Calculating AHP weights and consistency', ['session_id' => $sessionId]);

        $criteria = Kriteria::orderBy('id')->get();
        $n = $criteria->count();

        if ($n < 2) {
            throw new \Exception('At least 2 criteria required for AHP calculation');
        }

        // Build comparison matrix
        $matrix = $this->buildComparisonMatrix($sessionId, $criteria);

        // Validate matrix completeness
        $this->validateMatrix($matrix, $n);

        // Calculate priority vector using geometric mean method
        $weights = $this->calculatePriorityVector($matrix, $criteria);

        // Calculate consistency metrics
        $consistency = $this->calculateConsistencyMetrics($matrix, $weights, $n);

        // Additional matrix analysis
        $analysis = $this->analyzeMatrix($matrix, $criteria);

        Log::info('AHP calculation completed', [
            'session_id' => $sessionId,
            'criteria_count' => $n,
            'consistency_ratio' => $consistency['cr'],
            'is_consistent' => $consistency['cr'] < 0.1
        ]);

        return [
            'weights' => $weights,
            'consistency_index' => $consistency['ci'],
            'consistency_ratio' => $consistency['cr'],
            'lambda_max' => $consistency['lambda_max'],
            'is_consistent' => $consistency['cr'] < 0.1,
            'matrix' => $matrix,
            'analysis' => $analysis,
            'criteria_count' => $n
        ];
    }

    /**
     * Build the complete comparison matrix from database
     */
    private function buildComparisonMatrix(int $sessionId, $criteria): array
    {
        $comparisons = AhpComparison::where('ahp_session_id', $sessionId)->get();
        $n = $criteria->count();
        $matrix = [];

        // Initialize matrix with identity
        for ($i = 0; $i < $n; $i++) {
            for ($j = 0; $j < $n; $j++) {
                $matrix[$i][$j] = ($i === $j) ? 1.0 : 1.0;
            }
        }

        // Fill matrix with comparisons
        foreach ($comparisons as $comparison) {
            $i = $criteria->search(fn($c) => $c->id === $comparison->kriteria_1_id);
            $j = $criteria->search(fn($c) => $c->id === $comparison->kriteria_2_id);

            if ($i !== false && $j !== false) {
                $matrix[$i][$j] = (float) $comparison->nilai;
                if ($comparison->nilai != 0) {
                    $matrix[$j][$i] = 1.0 / (float) $comparison->nilai; // Reciprocal
                }
            }
        }

        Log::info('Matrix built successfully', [
            'session_id' => $sessionId,
            'matrix_size' => count($matrix)
        ]);

        return $matrix;
    }

    /**
     * Calculate priority vector using geometric mean method
     */
    private function calculatePriorityVector(array $matrix, $criteria): array
    {
        $n = count($matrix);
        $weights = [];

        // Calculate geometric mean for each row
        for ($i = 0; $i < $n; $i++) {
            $product = 1.0;
            $validValues = 0;

            for ($j = 0; $j < $n; $j++) {
                if ($matrix[$i][$j] > 0) {
                    $product *= $matrix[$i][$j];
                    $validValues++;
                }
            }

            if ($validValues > 0) {
                $geometricMean = pow($product, 1.0 / $validValues);
                $weights[$i] = $geometricMean;
            } else {
                $weights[$i] = 1.0;
            }
        }

        // Normalize weights
        $totalWeight = array_sum($weights);
        $normalizedWeights = [];

        if ($totalWeight > 0) {
            for ($i = 0; $i < $n; $i++) {
                $weight = $weights[$i] / $totalWeight;
                $normalizedWeights[$criteria[$i]->nama_kriteria] = $weight;
            }
        } else {
            // Equal weights if total is 0
            $equalWeight = 1.0 / $n;
            for ($i = 0; $i < $n; $i++) {
                $normalizedWeights[$criteria[$i]->nama_kriteria] = $equalWeight;
            }
        }

        return $normalizedWeights;
    }

    /**
     * Calculate consistency metrics
     */
    private function calculateConsistencyMetrics(array $matrix, array $weights, int $n): array
    {
        if ($n < 2) {
            return [
                'lambda_max' => $n,
                'ci' => 0,
                'cr' => 0
            ];
        }

        // Calculate lambda max
        $lambdaMax = 0;
        $validRows = 0;

        for ($i = 0; $i < $n; $i++) {
            $sum = 0;
            $rowValid = false;

            for ($j = 0; $j < $n; $j++) {
                if ($matrix[$i][$j] > 0 && isset($weights[$j]) && $weights[$j] > 0) {
                    $sum += $matrix[$i][$j] * $weights[$j];
                    $rowValid = true;
                }
            }

            if ($rowValid && isset($weights[$i]) && $weights[$i] > 0) {
                $lambdaMax += $sum / $weights[$i];
                $validRows++;
            }
        }

        $lambdaMax = $validRows > 0 ? $lambdaMax / $validRows : $n;

        // Calculate CI
        $ci = ($lambdaMax - $n) / ($n - 1);

        // Get Random Index
        $ri = self::RANDOM_INDEX[$n] ?? 1.59;

        // Calculate CR
        $cr = $ri > 0 ? $ci / $ri : 0;

        return [
            'lambda_max' => $lambdaMax,
            'ci' => $ci,
            'cr' => $cr
        ];
    }

    /**
     * Validate matrix completeness and structure
     */
    private function validateMatrix(array $matrix, int $n): void
    {
        if (count($matrix) !== $n) {
            throw new \Exception("Matrix size mismatch: expected {$n}x{$n}, got " . count($matrix) . "x" . count($matrix[0] ?? 0));
        }

        // Check for zero or negative values
        for ($i = 0; $i < $n; $i++) {
            for ($j = 0; $j < $n; $j++) {
                if ($matrix[$i][$j] <= 0) {
                    throw new \Exception("Matrix contains invalid value at position [{$i}][{$j}]: {$matrix[$i][$j]}");
                }
            }
        }
    }

    /**
     * Analyze matrix for additional insights
     */
    private function analyzeMatrix(array $matrix, $criteria): array
    {
        $n = count($matrix);
        $analysis = [
            'matrix_size' => $n,
            'total_comparisons' => ($n * ($n - 1)) / 2,
            'max_value' => 0,
            'min_value' => PHP_FLOAT_MAX,
            'average_value' => 0,
            'consistency_issues' => []
        ];

        $sum = 0;
        $count = 0;

        for ($i = 0; $i < $n; $i++) {
            for ($j = 0; $j < $n; $j++) {
                if ($i !== $j) {
                    $value = $matrix[$i][$j];
                    $analysis['max_value'] = max($analysis['max_value'], $value);
                    $analysis['min_value'] = min($analysis['min_value'], $value);
                    $sum += $value;
                    $count++;
                }
            }
        }

        $analysis['average_value'] = $count > 0 ? $sum / $count : 0;
        $analysis['min_value'] = $analysis['min_value'] === PHP_FLOAT_MAX ? 0 : $analysis['min_value'];

        return $analysis;
    }

    /**
     * Save AHP results to database
     */
    private function saveResults(int $sessionId, array $results): void
    {
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

        Log::info('AHP results saved', [
            'session_id' => $sessionId,
            'weights_count' => count($results['weights'])
        ]);
    }

    /**
     * Update rankings for all submissions in session
     */
    private function updateRankings(int $sessionId): array
    {
        Log::info('Updating rankings for session', ['session_id' => $sessionId]);

        $submissions = PengajuanBahanAjar::where('ahp_session_id', $sessionId)->get();
        $weights = AhpResult::where('ahp_session_id', $sessionId)
            ->with('kriteria')
            ->get()
            ->pluck('bobot', 'kriteria.nama_kriteria')
            ->toArray();

        if (empty($weights)) {
            Log::warning('No weights found for ranking update', ['session_id' => $sessionId]);
            return ['updated_count' => 0, 'rankings' => []];
        }

        $updatedCount = 0;
        $rankings = [];

        // Calculate scores for each submission
        foreach ($submissions as $submission) {
            $score = $this->calculateSubmissionScore($submission, $weights);
            $submission->update(['ahp_score' => $score]);
            $updatedCount++;

            Log::debug('Updated submission score', [
                'submission_id' => $submission->id,
                'score' => $score
            ]);
        }

        // Update rankings based on scores
        $rankedSubmissions = PengajuanBahanAjar::where('ahp_session_id', $sessionId)
            ->orderBy('ahp_score', 'desc')
            ->get();

        foreach ($rankedSubmissions as $index => $submission) {
            $rankingPosition = $index + 1;
            $submission->update(['ranking_position' => $rankingPosition]);

            $rankings[] = [
                'id' => $submission->id,
                'nama_bahan_ajar' => $submission->nama_barang,
                'pengusul' => $submission->user->name ?? 'Unknown',
                'ahp_score' => $submission->ahp_score,
                'ranking_position' => $submission->ranking_position,
                'harga_satuan' => $submission->harga_satuan,
                'jumlah' => $submission->jumlah,
                'stok' => $submission->stok,
                'urgensi_prodi' => $submission->urgensi_prodi,
                'urgensi_institusi' => $submission->urgensi_institusi,
            ];
        }

        Log::info('Rankings updated successfully', [
            'session_id' => $sessionId,
            'updated_count' => $updatedCount
        ]);

        return [
            'updated_count' => $updatedCount,
            'rankings' => $rankings
        ];
    }

    /**
     * Calculate AHP score for a submission
     */
    private function calculateSubmissionScore($submission, array $weights): float
    {
        // Normalize values based on criteria
        $normalizedValues = [
            'Harga' => $this->normalizeHarga($submission),
            'Jumlah' => $this->normalizeJumlah($submission),
            'Stok' => $this->normalizeStok($submission),
            'Urgensi' => $this->normalizeUrgensi($submission),
        ];

        $score = 0;
        $totalWeight = 0;

        foreach ($normalizedValues as $criteriaName => $value) {
            $weight = $weights[$criteriaName] ?? 0;
            $score += $value * $weight;
            $totalWeight += $weight;

            Log::debug('Score component calculated', [
                'criteria' => $criteriaName,
                'normalized_value' => $value,
                'weight' => $weight,
                'component_score' => $value * $weight
            ]);
        }

        // Normalize final score if needed
        $finalScore = $totalWeight > 0 ? $score / $totalWeight : $score;

        Log::debug('Final submission score', [
            'submission_id' => $submission->id,
            'raw_score' => $score,
            'total_weight' => $totalWeight,
            'final_score' => $finalScore
        ]);

        return $finalScore;
    }

    /**
     * Normalize harga (cost criteria - lower is better)
     */
    private function normalizeHarga($submission): float
    {
        $maxHarga = PengajuanBahanAjar::where('ahp_session_id', $submission->ahp_session_id)->max('harga_satuan');
        $minHarga = PengajuanBahanAjar::where('ahp_session_id', $submission->ahp_session_id)->min('harga_satuan');

        if ($maxHarga == $minHarga) return 1.0;

        return ($maxHarga - $submission->harga_satuan) / ($maxHarga - $minHarga);
    }

    /**
     * Normalize jumlah (benefit criteria - higher is better)
     */
    private function normalizeJumlah($submission): float
    {
        $maxJumlah = PengajuanBahanAjar::where('ahp_session_id', $submission->ahp_session_id)->max('jumlah');
        $minJumlah = PengajuanBahanAjar::where('ahp_session_id', $submission->ahp_session_id)->min('jumlah');

        if ($maxJumlah == $minJumlah) return 1.0;

        return ($submission->jumlah - $minJumlah) / ($maxJumlah - $minJumlah);
    }

    /**
     * Normalize stok (cost criteria - lower stock = higher priority)
     */
    private function normalizeStok($submission): float
    {
        $maxStok = PengajuanBahanAjar::where('ahp_session_id', $submission->ahp_session_id)->max('stok');
        $minStok = PengajuanBahanAjar::where('ahp_session_id', $submission->ahp_session_id)->min('stok');

        if ($maxStok == $minStok) return 1.0;

        return ($maxStok - $submission->stok) / ($maxStok - $minStok);
    }

    /**
     * Normalize urgensi (benefit criteria - higher is better)
     */
    private function normalizeUrgensi($submission): float
    {
        // Combine all three urgency values
        $prodiValue = match ($submission->urgensi_prodi) {
            'tinggi' => 3,
            'sedang' => 2,
            'rendah' => 1,
            default => 1
        };

        $institusiValue = match ($submission->urgensi_institusi) {
            'tinggi' => 3,
            'sedang' => 2,
            'rendah' => 1,
            default => 1
        };

        $timPengadaanValue = match ($submission->urgensi_tim_pengadaan) {
            'tinggi' => 3,
            'sedang' => 2,
            'rendah' => 1,
            default => 1
        };

        // Calculate weighted average (Tim Pengadaan has more weight)
        $totalValue = ($prodiValue + $institusiValue + $timPengadaanValue);
        return $totalValue / 9; // Max is 9
    }

    /**
     * Validate session matrix completeness
     */
    private function validateSessionMatrix(int $sessionId): array
    {
        $criteria = Kriteria::all();
        $n = $criteria->count();

        if ($n < 2) {
            return [
                'is_complete' => false,
                'completion_rate' => 0,
                'message' => 'At least 2 criteria required'
            ];
        }

        $requiredComparisons = ($n * ($n - 1)) / 2;
        $existingComparisons = AhpComparison::where('ahp_session_id', $sessionId)->count();

        $completionRate = $requiredComparisons > 0 ? ($existingComparisons / $requiredComparisons) * 100 : 0;

        return [
            'is_complete' => $existingComparisons >= $requiredComparisons,
            'completion_rate' => $completionRate,
            'required_comparisons' => $requiredComparisons,
            'existing_comparisons' => $existingComparisons
        ];
    }

    /**
     * Clear session-related caches
     */
    private function clearSessionCaches(int $sessionId): void
    {
        Cache::forget("ahp_results_{$sessionId}");
        Cache::forget("ahp_rankings_{$sessionId}");
        Cache::forget("ahp_matrix_{$sessionId}");
    }

    /**
     * Get AHP results for a session
     */
    public function getSessionResults(int $sessionId): ?array
    {
        $cacheKey = "ahp_results_{$sessionId}";

        return Cache::remember($cacheKey, 3600, function () use ($sessionId) {
            $results = AhpResult::where('ahp_session_id', $sessionId)
                ->with('kriteria')
                ->get();

            if ($results->isEmpty()) {
                return null;
            }

            $weights = [];
            foreach ($results as $result) {
                $weights[$result->kriteria->nama_kriteria] = $result->bobot;
            }

            return [
                'weights' => $weights,
                'is_consistent' => true, // You might want to store this in the database
                'cached_at' => now()
            ];
        });
    }

    /**
     * Get ranking results for a session
     */
    public function getSessionRankings(int $sessionId): array
    {
        $cacheKey = "ahp_rankings_{$sessionId}";

        return Cache::remember($cacheKey, 1800, function () use ($sessionId) {
            $submissions = PengajuanBahanAjar::where('ahp_session_id', $sessionId)
                ->whereNotNull('ahp_score')
                ->orderBy('ranking_position', 'asc')
                ->with(['user', 'ahpSession'])
                ->get();

            return $submissions->map(function ($submission) {
                return [
                    'id' => $submission->id,
                    'nama_bahan_ajar' => $submission->nama_barang,
                    'pengusul' => $submission->user->name ?? 'Unknown',
                    'ahp_score' => $submission->ahp_score,
                    'ranking_position' => $submission->ranking_position,
                    'harga_satuan' => $submission->harga_satuan,
                    'jumlah' => $submission->jumlah,
                    'stok' => $submission->stok,
                    'urgensi_prodi' => $submission->urgensi_prodi,
                    'urgensi_institusi' => $submission->urgensi_institusi,
                ];
            })->toArray();
        });
    }

    /**
     * Export AHP results to array for Excel/CSV
     */
    public function exportSessionResults(int $sessionId): array
    {
        $results = $this->getSessionResults($sessionId);
        $rankings = $this->getSessionRankings($sessionId);
        $session = AhpSession::find($sessionId);
        $statistics = $this->validateSessionMatrix($sessionId);

        return [
            'session_info' => [
                'id' => $session->id,
                'tahun_ajaran' => $session->tahun_ajaran,
                'semester' => $session->semester,
                'exported_at' => now()->format('Y-m-d H:i:s')
            ],
            'weights' => $results['weights'] ?? [],
            'consistency' => [
                'consistency_ratio' => $results['consistency_ratio'] ?? 0,
                'consistency_index' => $results['consistency_index'] ?? 0,
                'lambda_max' => $results['lambda_max'] ?? 0,
                'is_consistent' => $results['is_consistent'] ?? false
            ],
            'statistics' => $statistics,
            'rankings' => $rankings
        ];
    }

    /**
     * Compare two sessions
     */
    public function compareSessions(int $sessionId1, int $sessionId2): array
    {
        $results1 = $this->getSessionResults($sessionId1);
        $results2 = $this->getSessionResults($sessionId2);

        if (!$results1 || !$results2) {
            throw new \Exception('One or both sessions do not have calculated results');
        }

        $comparison = [
            'session_1' => [
                'id' => $sessionId1,
                'results' => $results1
            ],
            'session_2' => [
                'id' => $sessionId2,
                'results' => $results2
            ],
            'weight_differences' => [],
            'consistency_comparison' => [
                'session_1_cr' => $results1['consistency_ratio'],
                'session_2_cr' => $results2['consistency_ratio'],
                'better_consistency' => $results1['consistency_ratio'] < $results2['consistency_ratio'] ? 1 : 2
            ]
        ];

        // Compare weights for common criteria
        foreach ($results1['weights'] as $criteria => $weight1) {
            if (isset($results2['weights'][$criteria])) {
                $weight2 = $results2['weights'][$criteria];
                $comparison['weight_differences'][$criteria] = [
                    'session_1' => $weight1,
                    'session_2' => $weight2,
                    'difference' => abs($weight1 - $weight2),
                    'percentage_change' => $weight1 > 0 ? (($weight2 - $weight1) / $weight1) * 100 : 0
                ];
            }
        }

        return $comparison;
    }

    /**
     * Validate AHP input data before processing
     */
    public function validateInputData(int $sessionId, array $matrixData): array
    {
        $errors = [];
        $warnings = [];

        // Check session exists
        $session = AhpSession::find($sessionId);
        if (!$session) {
            $errors[] = "Session AHP dengan ID {$sessionId} tidak ditemukan";
        }

        // Check criteria
        $criteria = Kriteria::all();
        if ($criteria->count() < 2) {
            $errors[] = "Minimal 2 kriteria diperlukan untuk AHP";
        }

        // Validate matrix data structure
        $expectedKeys = [];
        foreach ($criteria as $c1) {
            foreach ($criteria as $c2) {
                if ($c1->id !== $c2->id) {
                    $expectedKeys[] = "matrix_{$c1->id}_{$c2->id}";
                }
            }
        }

        $providedKeys = array_keys($matrixData);
        $missingKeys = array_diff($expectedKeys, $providedKeys);

        if (!empty($missingKeys)) {
            $warnings[] = "Data matrix tidak lengkap. Missing: " . implode(', ', array_slice($missingKeys, 0, 5));
        }

        // Validate values
        foreach ($matrixData as $key => $value) {
            if (!is_numeric($value)) {
                $errors[] = "Nilai '{$key}' bukan angka: {$value}";
            } elseif ($value <= 0) {
                $errors[] = "Nilai '{$key}' harus lebih besar dari 0: {$value}";
            } elseif ($value > 9) {
                $warnings[] = "Nilai '{$key}' sangat tinggi (>{9}): {$value}";
            }
        }

        // Check for reciprocal consistency
        foreach ($criteria as $c1) {
            foreach ($criteria as $c2) {
                if ($c1->id !== $c2->id) {
                    $key1 = "matrix_{$c1->id}_{$c2->id}";
                    $key2 = "matrix_{$c2->id}_{$c1->id}";

                    if (isset($matrixData[$key1]) && isset($matrixData[$key2])) {
                        $value1 = $matrixData[$key1];
                        $value2 = $matrixData[$key2];
                        $expected = 1.0 / $value1;

                        if (abs($value2 - $expected) > 0.01) {
                            $warnings[] = "Inkonsistensi reciprocal: {$key1}={$value1}, {$key2}={$value2} (expected: " . number_format($expected, 3) . ")";
                        }
                    }
                }
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings,
            'criteria_count' => $criteria->count(),
            'expected_comparisons' => count($expectedKeys),
            'provided_comparisons' => count($providedKeys)
        ];
    }

    /**
     * Generate sensitivity analysis
     */
    public function performSensitivityAnalysis(int $sessionId, string $targetCriteria, float $perturbation = 0.1): array
    {
        $originalResults = $this->getSessionResults($sessionId);
        if (!$originalResults) {
            throw new \Exception('No original results found for sensitivity analysis');
        }

        $originalWeight = $originalResults['weights'][$targetCriteria] ?? null;
        if ($originalWeight === null) {
            throw new \Exception("Criteria '{$targetCriteria}' not found in results");
        }

        // Get original matrix
        $criteria = Kriteria::orderBy('id')->get();
        $originalMatrix = $this->buildComparisonMatrix($sessionId, $criteria);

        $sensitivityResults = [];

        // Test different perturbation levels
        $perturbationLevels = [-$perturbation, -$perturbation / 2, $perturbation / 2, $perturbation];

        foreach ($perturbationLevels as $delta) {
            // Create perturbed matrix (simplified - adjust all comparisons involving target criteria)
            $perturbedMatrix = $originalMatrix;

            $targetIndex = null;
            foreach ($criteria as $index => $criterion) {
                if ($criterion->nama_kriteria === $targetCriteria) {
                    $targetIndex = $index;
                    break;
                }
            }

            if ($targetIndex !== null) {
                // Adjust comparisons
                for ($j = 0; $j < count($criteria); $j++) {
                    if ($j !== $targetIndex) {
                        $currentValue = $perturbedMatrix[$targetIndex][$j];
                        $newValue = $currentValue * (1 + $delta);
                        $perturbedMatrix[$targetIndex][$j] = $newValue;
                        $perturbedMatrix[$j][$targetIndex] = 1 / $newValue;
                    }
                }

                // Calculate new weights
                $newWeights = $this->calculatePriorityVector($perturbedMatrix, $criteria);

                $sensitivityResults[] = [
                    'perturbation' => $delta * 100, // As percentage
                    'new_weight' => $newWeights[$targetCriteria],
                    'weight_change' => $newWeights[$targetCriteria] - $originalWeight,
                    'weight_change_percent' => (($newWeights[$targetCriteria] - $originalWeight) / $originalWeight) * 100,
                    'all_weights' => $newWeights
                ];
            }
        }

        return [
            'target_criteria' => $targetCriteria,
            'original_weight' => $originalWeight,
            'perturbation_levels' => $perturbationLevels,
            'results' => $sensitivityResults
        ];
    }

    /**
     * Get AHP scale reference
     */
    public function getAHPScaleReference(): array
    {
        return self::AHP_SCALE;
    }

    /**
     * Get random index reference
     */
    public function getRandomIndexReference(): array
    {
        return self::RANDOM_INDEX;
    }

    /**
     * Health check for AHP service
     */
    public function healthCheck(): array
    {
        $health = [
            'status' => 'healthy',
            'checks' => [],
            'timestamp' => now()
        ];

        // Check database connections
        try {
            DB::connection()->getPdo();
            $health['checks']['database'] = 'ok';
        } catch (\Exception $e) {
            $health['checks']['database'] = 'failed: ' . $e->getMessage();
            $health['status'] = 'unhealthy';
        }

        // Check cache
        try {
            Cache::put('ahp_health_check', true, 60);
            $cached = Cache::get('ahp_health_check');
            $health['checks']['cache'] = $cached ? 'ok' : 'failed';
        } catch (\Exception $e) {
            $health['checks']['cache'] = 'failed: ' . $e->getMessage();
            $health['status'] = 'unhealthy';
        }

        // Check required models
        $requiredModels = [
            'AhpSession' => AhpSession::class,
            'AhpComparison' => AhpComparison::class,
            'AhpResult' => AhpResult::class,
            'Kriteria' => Kriteria::class
        ];

        foreach ($requiredModels as $name => $model) {
            try {
                $model::query()->limit(1)->get();
                $health['checks']["model_{$name}"] = 'ok';
            } catch (\Exception $e) {
                $health['checks']["model_{$name}"] = 'failed: ' . $e->getMessage();
                $health['status'] = 'unhealthy';
            }
        }

        return $health;
    }
}
