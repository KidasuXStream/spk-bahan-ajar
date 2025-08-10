<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class AhpComparison extends Model
{
    use HasFactory;

    protected $fillable = [
        'ahp_session_id',
        'kriteria_1_id',
        'kriteria_2_id',
        'nilai',
    ];

    protected $casts = [
        'nilai' => 'float',
        'ahp_session_id' => 'integer',
        'kriteria_1_id' => 'integer',
        'kriteria_2_id' => 'integer',
    ];

    protected $attributes = [
        'nilai' => 1.0,
    ];

    // Relationships
    public function session(): BelongsTo
    {
        return $this->belongsTo(AhpSession::class, 'ahp_session_id');
    }

    public function kriteria1(): BelongsTo
    {
        return $this->belongsTo(Kriteria::class, 'kriteria_1_id');
    }

    public function kriteria2(): BelongsTo
    {
        return $this->belongsTo(Kriteria::class, 'kriteria_2_id');
    }

    /**
     * Simpan matriks perbandingan untuk session tertentu
     */
    public static function saveMatrixForSession($sessionId, array $matrixData): bool
    {
        try {
            Log::info('Starting matrix save process', [
                'session_id' => $sessionId,
                'matrix_data_count' => count($matrixData),
                'matrix_data_keys' => array_keys($matrixData)
            ]);

            // Validasi session exists
            $session = AhpSession::find($sessionId);
            if (!$session) {
                throw new \Exception("Session AHP dengan ID {$sessionId} tidak ditemukan");
            }

            // Validasi kriteria ada
            $criteria = Kriteria::orderBy('id')->get();
            if ($criteria->count() < 2) {
                throw new \Exception("Minimal 2 kriteria diperlukan untuk AHP");
            }

            DB::beginTransaction();

            // Hapus perbandingan yang sudah ada untuk session ini
            self::where('ahp_session_id', $sessionId)->delete();

            $comparisons = [];
            $savedCount = 0;

            // Simpan hanya upper triangular matrix untuk menghindari duplikasi
            foreach ($criteria as $i => $c1) {
                foreach ($criteria as $j => $c2) {
                    if ($i < $j) { // Hanya segitiga atas
                        $key = "matrix_{$c1->id}_{$c2->id}";

                        if (isset($matrixData[$key])) {
                            $value = (float) $matrixData[$key];

                            // Validasi nilai
                            if ($value <= 0) {
                                throw new \Exception("Nilai perbandingan harus lebih besar dari 0");
                            }

                            // Simpan perbandingan (bahkan jika nilai = 1)
                            $comparisons[] = [
                                'ahp_session_id' => $sessionId,
                                'kriteria_1_id' => $c1->id,
                                'kriteria_2_id' => $c2->id,
                                'nilai' => $value,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ];
                            $savedCount++;

                            Log::debug("Prepared comparison", [
                                'criteria_1' => $c1->kode_kriteria,
                                'criteria_2' => $c2->kode_kriteria,
                                'value' => $value,
                                'key' => $key
                            ]);
                        } else {
                            // Jika key tidak ada, gunakan nilai default 1
                            $comparisons[] = [
                                'ahp_session_id' => $sessionId,
                                'kriteria_1_id' => $c1->id,
                                'kriteria_2_id' => $c2->id,
                                'nilai' => 1.0,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ];
                            $savedCount++;

                            Log::debug("Using default value for missing key", [
                                'key' => $key,
                                'criteria_1' => $c1->kode_kriteria,
                                'criteria_2' => $c2->kode_kriteria,
                                'value' => 1.0
                            ]);
                        }
                    }
                }
            }

            // Bulk insert untuk performa yang lebih baik
            if (!empty($comparisons)) {
                self::insert($comparisons);
                Log::info("Matrix saved successfully", [
                    'session_id' => $sessionId,
                    'comparisons_saved' => $savedCount,
                    'total_possible' => ($criteria->count() * ($criteria->count() - 1)) / 2
                ]);
            } else {
                Log::warning("No comparisons to save", [
                    'session_id' => $sessionId,
                    'matrix_data' => $matrixData,
                    'criteria_count' => $criteria->count()
                ]);
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to save AHP matrix', [
                'session_id' => $sessionId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Ambil matriks lengkap untuk session
     */
    public static function getMatrixForSession($sessionId): array
    {
        $comparisons = self::where('ahp_session_id', $sessionId)->get();
        $criteria = Kriteria::orderBy('id')->get();
        $matrix = [];

        Log::info('Loading matrix for session', [
            'session_id' => $sessionId,
            'comparisons_count' => $comparisons->count(),
            'criteria_count' => $criteria->count()
        ]);

        // Inisialisasi matriks dengan nilai default
        foreach ($criteria as $c1) {
            foreach ($criteria as $c2) {
                $key = "matrix_{$c1->id}_{$c2->id}";
                if ($c1->id === $c2->id) {
                    $matrix[$key] = 1; // Diagonal selalu 1
                } else {
                    $matrix[$key] = 1; // Default untuk non-diagonal
                }
            }
        }

        // Isi dengan perbandingan yang tersimpan
        foreach ($comparisons as $comparison) {
            $key1 = "matrix_{$comparison->kriteria_1_id}_{$comparison->kriteria_2_id}";
            $key2 = "matrix_{$comparison->kriteria_2_id}_{$comparison->kriteria_1_id}";

            $matrix[$key1] = $comparison->nilai;
            if ($comparison->nilai != 0) {
                $matrix[$key2] = 1 / $comparison->nilai; // Reciprocal
            }
        }

        Log::info('Matrix loaded successfully', [
            'session_id' => $sessionId,
            'matrix_size' => count($matrix)
        ]);

        return $matrix;
    }

    /**
     * Hitung bobot AHP untuk session
     */
    public static function calculateAHPWeights($sessionId): array
    {
        try {
            $comparisons = self::where('ahp_session_id', $sessionId)->get();

            if ($comparisons->isEmpty()) {
                Log::warning('No AHP comparisons found', ['session_id' => $sessionId]);
                return [
                    'weights' => [],
                    'ci' => 0,
                    'cr' => 0,
                    'consistent' => false,
                    'lambda_max' => 0,
                ];
            }

            $criteria = Kriteria::orderBy('id')->get();
            $n = $criteria->count();

            if ($n < 2) {
                Log::warning('Insufficient criteria for AHP', ['criteria_count' => $n]);
                return [
                    'weights' => [],
                    'ci' => 0,
                    'cr' => 0,
                    'consistent' => false,
                    'lambda_max' => 0,
                ];
            }

            Log::info('Starting AHP calculation', [
                'session_id' => $sessionId,
                'criteria_count' => $n,
                'comparisons_count' => $comparisons->count()
            ]);

            // Bangun matriks lengkap
            $matrix = self::buildCalculationMatrix($sessionId, $criteria);

            Log::info('Matrix built', [
                'matrix_size' => count($matrix),
                'matrix' => $matrix
            ]);

            // Hitung bobot menggunakan geometric mean method
            $weights = self::calculateGeometricMeanWeights($matrix, $criteria);

            Log::info('Weights calculated', [
                'weights' => $weights
            ]);

            // Hitung metrik konsistensi - gunakan indexed array untuk weights
            $weightValues = array_values($weights);
            $consistency = self::calculateConsistencyMetrics($matrix, $weightValues, $n);

            Log::info('Consistency calculated', [
                'consistency' => $consistency
            ]);

            Log::info('AHP weights calculated successfully', [
                'session_id' => $sessionId,
                'weights_count' => count($weights),
                'consistency_ratio' => $consistency['cr']
            ]);

            return [
                'weights' => $weights,
                'ci' => $consistency['ci'],
                'cr' => $consistency['cr'],
                'consistent' => $consistency['cr'] < 0.1,
                'lambda_max' => $consistency['lambda_max'],
            ];
        } catch (\Exception $e) {
            Log::error('AHP calculation failed', [
                'session_id' => $sessionId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'weights' => [],
                'ci' => 0,
                'cr' => 0,
                'consistent' => false,
                'lambda_max' => 0,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Method public untuk testing matrix building
     */
    public static function testBuildMatrix($sessionId): array
    {
        $criteria = Kriteria::orderBy('id')->get();
        return self::buildCalculationMatrix($sessionId, $criteria);
    }

    /**
     * Bangun matriks untuk perhitungan
     */
    private static function buildCalculationMatrix($sessionId, $criteria): array
    {
        $comparisons = self::where('ahp_session_id', $sessionId)->get();
        $n = $criteria->count();
        $matrix = [];

        Log::info('Building calculation matrix', [
            'session_id' => $sessionId,
            'criteria_count' => $n,
            'comparisons_count' => $comparisons->count(),
            'comparisons' => $comparisons->toArray()
        ]);

        // Buat mapping criteria ID ke index
        $criteriaMap = [];
        foreach ($criteria as $index => $criterion) {
            $criteriaMap[$criterion->id] = $index;
        }

        Log::debug('Criteria mapping', [
            'criteria_map' => $criteriaMap
        ]);

        // Inisialisasi matriks dengan identitas
        for ($i = 0; $i < $n; $i++) {
            for ($j = 0; $j < $n; $j++) {
                $matrix[$i][$j] = ($i === $j) ? 1.0 : 1.0;
            }
        }

        // Isi matriks dengan perbandingan
        $filledCells = 0;
        foreach ($comparisons as $comparison) {
            $i = $criteriaMap[$comparison->kriteria_1_id] ?? null;
            $j = $criteriaMap[$comparison->kriteria_2_id] ?? null;

            if ($i !== null && $j !== null) {
                $value = (float) $comparison->nilai;
                $matrix[$i][$j] = $value;

                if ($value != 0) {
                    $matrix[$j][$i] = 1.0 / $value; // Reciprocal
                }

                $filledCells++;

                Log::debug('Matrix cell filled', [
                    'i' => $i,
                    'j' => $j,
                    'value' => $value,
                    'reciprocal' => 1.0 / $value,
                    'criteria_1' => $criteria[$i]->nama_kriteria,
                    'criteria_2' => $criteria[$j]->nama_kriteria
                ]);
            } else {
                Log::warning('Comparison not found in criteria', [
                    'kriteria_1_id' => $comparison->kriteria_1_id,
                    'kriteria_2_id' => $comparison->kriteria_2_id,
                    'criteria_map' => $criteriaMap
                ]);
            }
        }

        // Validasi matriks
        $hasValidData = false;
        for ($i = 0; $i < $n; $i++) {
            for ($j = 0; $j < $n; $j++) {
                if ($i !== $j && $matrix[$i][$j] != 1.0) {
                    $hasValidData = true;
                    break 2;
                }
            }
        }

        if (!$hasValidData) {
            Log::warning('Matrix contains only default values (1.0)', [
                'session_id' => $sessionId,
                'filled_cells' => $filledCells,
                'comparisons_count' => $comparisons->count()
            ]);
        }

        Log::info('Matrix built successfully', [
            'matrix_size' => count($matrix),
            'has_valid_data' => $hasValidData,
            'filled_cells' => $filledCells,
            'matrix' => $matrix
        ]);

        return $matrix;
    }

    /**
     * Hitung bobot menggunakan geometric mean method
     */
    private static function calculateGeometricMeanWeights(array $matrix, $criteria): array
    {
        $n = count($matrix);
        $weights = [];

        Log::info('Calculating geometric mean weights', [
            'matrix_size' => $n,
            'criteria_count' => $criteria->count(),
            'matrix' => $matrix
        ]);

        // Hitung geometric mean untuk setiap baris
        for ($i = 0; $i < $n; $i++) {
            $product = 1.0;
            $validValues = 0;

            for ($j = 0; $j < $n; $j++) {
                if ($matrix[$i][$j] > 0) {
                    $product *= $matrix[$i][$j];
                    $validValues++;
                }
            }

            // Hitung geometric mean hanya jika ada nilai valid
            if ($validValues > 0) {
                $geometricMean = pow($product, 1.0 / $validValues);
                $weights[$i] = $geometricMean;
            } else {
                $weights[$i] = 1.0; // Default value jika tidak ada nilai valid
            }

            Log::debug('Row calculation', [
                'row' => $i,
                'product' => $product,
                'valid_values' => $validValues,
                'geometric_mean' => $weights[$i],
                'criteria' => $criteria[$i]->nama_kriteria ?? 'Unknown'
            ]);
        }

        Log::info('Raw weights calculated', [
            'weights' => $weights
        ]);

        // Normalisasi bobot
        $totalWeight = array_sum($weights);
        $normalizedWeights = [];

        if ($totalWeight > 0) {
            for ($i = 0; $i < $n; $i++) {
                $weight = $weights[$i] / $totalWeight;
                $criteriaName = $criteria[$i]->nama_kriteria ?? "Criteria_{$i}";
                $normalizedWeights[$criteriaName] = $weight;
            }
        } else {
            // Jika total weight 0, berikan bobot yang sama
            $equalWeight = 1.0 / $n;
            for ($i = 0; $i < $n; $i++) {
                $criteriaName = $criteria[$i]->nama_kriteria ?? "Criteria_{$i}";
                $normalizedWeights[$criteriaName] = $equalWeight;
            }
        }

        Log::info('Weights calculated successfully', [
            'total_weight' => $totalWeight,
            'normalized_weights' => $normalizedWeights
        ]);

        return $normalizedWeights;
    }

    /**
     * Hitung metrik konsistensi
     */
    private static function calculateConsistencyMetrics(array $matrix, array $weights, int $n): array
    {
        if ($n < 2) {
            return [
                'lambda_max' => $n,
                'ci' => 0,
                'cr' => 0
            ];
        }

        Log::info('Calculating consistency metrics', [
            'matrix_size' => $n,
            'weights_count' => count($weights),
            'weights' => $weights
        ]);

        // Hitung lambda max
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
                $lambdaContribution = $sum / $weights[$i];
                $lambdaMax += $lambdaContribution;
                $validRows++;

                Log::debug('Row consistency calculation', [
                    'row' => $i,
                    'sum' => $sum,
                    'weight' => $weights[$i],
                    'lambda_contribution' => $lambdaContribution
                ]);
            }
        }

        $lambdaMax = $validRows > 0 ? $lambdaMax / $validRows : $n;

        // Hitung CI
        $ci = ($lambdaMax - $n) / ($n - 1);

        // Random Index berdasarkan ukuran matriks (Saaty's Random Index)
        $randomIndex = [
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

        $ri = $randomIndex[$n] ?? 1.59;

        // Hitung CR
        $cr = $ri > 0 ? $ci / $ri : 0;

        $result = [
            'lambda_max' => $lambdaMax,
            'ci' => $ci,
            'cr' => $cr
        ];

        Log::info('Consistency metrics calculated', [
            'lambda_max' => $lambdaMax,
            'ci' => $ci,
            'cr' => $cr,
            'ri' => $ri,
            'valid_rows' => $validRows
        ]);

        return $result;
    }

    /**
     * Validasi data matriks
     */
    public static function validateMatrixData($sessionId, array $matrixData): array
    {
        $errors = [];
        $warnings = [];

        // Check session exists
        $session = AhpSession::find($sessionId);
        if (!$session) {
            $errors[] = "Session AHP tidak ditemukan";
        }

        // Check criteria count
        $criteria = Kriteria::all();
        if ($criteria->count() < 2) {
            $errors[] = "Minimal 2 kriteria diperlukan";
        }

        // Check matrix completeness
        $requiredComparisons = ($criteria->count() * ($criteria->count() - 1)) / 2;
        $providedComparisons = 0;

        foreach ($matrixData as $key => $value) {
            if (str_starts_with($key, 'matrix_') && $value != 1) {
                $providedComparisons++;
            }
        }

        if ($providedComparisons == 0) {
            $warnings[] = "Tidak ada perbandingan yang diisi (semua nilai masih 1)";
        }

        $completionRate = $requiredComparisons > 0 ? ($providedComparisons / $requiredComparisons) * 100 : 0;

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings,
            'completion_rate' => $completionRate,
            'provided_comparisons' => $providedComparisons,
            'required_comparisons' => $requiredComparisons
        ];
    }

    /**
     * Cek apakah matriks sudah lengkap
     */
    public static function isMatrixComplete($sessionId): bool
    {
        $criteria = Kriteria::all();
        $n = $criteria->count();

        if ($n < 2) return false;

        $requiredComparisons = ($n * ($n - 1)) / 2;
        $existingComparisons = self::where('ahp_session_id', $sessionId)->count();

        return $existingComparisons >= $requiredComparisons;
    }

    /**
     * Statistik session
     */
    public static function getSessionStatistics($sessionId): array
    {
        $criteria = Kriteria::all();
        $n = $criteria->count();
        $requiredComparisons = $n > 1 ? ($n * ($n - 1)) / 2 : 0;
        $existingComparisons = self::where('ahp_session_id', $sessionId)->count();

        return [
            'total_criteria' => $n,
            'required_comparisons' => $requiredComparisons,
            'existing_comparisons' => $existingComparisons,
            'completion_percentage' => $requiredComparisons > 0 ?
                round(($existingComparisons / $requiredComparisons) * 100, 2) : 0,
            'is_complete' => $existingComparisons >= $requiredComparisons
        ];
    }

    /**
     * Scope untuk session tertentu
     */
    public function scopeForSession($query, $sessionId)
    {
        return $query->where('ahp_session_id', $sessionId);
    }

    /**
     * Scope untuk perbandingan spesifik
     */
    public function scopeForComparison($query, $kriteria1Id, $kriteria2Id)
    {
        return $query->where('kriteria_1_id', $kriteria1Id)
            ->where('kriteria_2_id', $kriteria2Id);
    }

    /**
     * Debug method untuk memeriksa data AHP
     */
    public static function debugAHPData($sessionId): array
    {
        $criteria = Kriteria::orderBy('id')->get();
        $comparisons = self::where('ahp_session_id', $sessionId)->get();
        $results = AhpResult::where('ahp_session_id', $sessionId)->get();

        // Cek matrix completeness
        $requiredComparisons = ($criteria->count() * ($criteria->count() - 1)) / 2;
        $isComplete = $comparisons->count() >= $requiredComparisons;

        // Cek apakah ada nilai yang bukan 1
        $hasNonDefaultValues = false;
        foreach ($comparisons as $comparison) {
            if ($comparison->nilai != 1.0) {
                $hasNonDefaultValues = true;
                break;
            }
        }

        return [
            'session_id' => $sessionId,
            'criteria_count' => $criteria->count(),
            'criteria' => $criteria->map(function ($c) {
                return [
                    'id' => $c->id,
                    'kode' => $c->kode_kriteria,
                    'nama' => $c->nama_kriteria,
                    'active' => true
                ];
            }),
            'comparisons_count' => $comparisons->count(),
            'comparisons' => $comparisons->map(function ($c) {
                return [
                    'kriteria_1_id' => $c->kriteria_1_id,
                    'kriteria_2_id' => $c->kriteria_2_id,
                    'nilai' => $c->nilai
                ];
            }),
            'results_count' => $results->count(),
            'results' => $results->map(function ($r) {
                return [
                    'kriteria_id' => $r->kriteria_id,
                    'bobot' => $r->bobot
                ];
            }),
            'matrix_complete' => $isComplete,
            'has_non_default_values' => $hasNonDefaultValues,
            'required_comparisons' => $requiredComparisons,
            'completion_rate' => $requiredComparisons > 0 ? ($comparisons->count() / $requiredComparisons) * 100 : 0
        ];
    }
}
