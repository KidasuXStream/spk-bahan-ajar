<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AhpController;
use App\Http\Controllers\ExportController;

Route::get('/', function () {
    return view('welcome');
});

// AHP API Routes
Route::prefix('api/ahp')->group(function () {
    Route::post('/calculate-weights', [AhpController::class, 'calculateWeights']);
    Route::post('/generate-results', [AhpController::class, 'generateResults']);
    Route::get('/results/{session_id}', [AhpController::class, 'getResults']);
    Route::get('/rankings/{session_id}', [AhpController::class, 'getRankings']);
    Route::get('/validate-matrix/{session_id}', [AhpController::class, 'validateMatrix']);
    Route::post('/save-matrix', [AhpController::class, 'saveMatrix']);
    Route::get('/matrix/{session_id}', [AhpController::class, 'getMatrix']);
    Route::get('/statistics/{session_id}', [AhpController::class, 'getSessionStatistics']);
});

// Test route untuk debug AHP
Route::get('/test-ahp/{session_id}', function ($sessionId) {
    try {
        $results = \App\Models\AhpComparison::calculateAHPWeights($sessionId);
        return response()->json([
            'success' => true,
            'data' => $results
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
});

// Debug route untuk memeriksa data AHP
Route::get('/debug-ahp/{session_id}', function ($sessionId) {
    try {
        $debugData = \App\Models\AhpComparison::debugAHPData($sessionId);

        // Coba hitung AHP weights
        $ahpResults = \App\Models\AhpComparison::calculateAHPWeights($sessionId);

        return response()->json([
            'success' => true,
            'debug_data' => $debugData,
            'ahp_results' => $ahpResults
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
});

// Route untuk test matrix building
Route::get('/test-matrix/{session_id}', function ($sessionId) {
    try {
        $criteria = \App\Models\Kriteria::orderBy('id')->get();
        $comparisons = \App\Models\AhpComparison::where('ahp_session_id', $sessionId)->get();

        // Test buildCalculationMatrix
        $matrix = \App\Models\AhpComparison::testBuildMatrix($sessionId);

        return response()->json([
            'success' => true,
            'criteria_count' => $criteria->count(),
            'comparisons_count' => $comparisons->count(),
            'matrix' => $matrix,
            'criteria' => $criteria->map(function ($c) {
                return [
                    'id' => $c->id,
                    'nama' => $c->nama_kriteria
                ];
            }),
            'comparisons' => $comparisons->map(function ($c) {
                return [
                    'kriteria_1_id' => $c->kriteria_1_id,
                    'kriteria_2_id' => $c->kriteria_2_id,
                    'nilai' => $c->nilai
                ];
            })
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
});

// Route untuk test lengkap AHP
Route::get('/test-ahp-complete/{session_id}', function ($sessionId) {
    try {
        // 1. Debug data
        $debugData = \App\Models\AhpComparison::debugAHPData($sessionId);

        // 2. Test matrix building
        $matrix = \App\Models\AhpComparison::testBuildMatrix($sessionId);

        // 3. Test AHP calculation
        $ahpResults = \App\Models\AhpComparison::calculateAHPWeights($sessionId);

        return response()->json([
            'success' => true,
            'debug_data' => $debugData,
            'matrix' => $matrix,
            'ahp_results' => $ahpResults
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
});

// Route untuk test step-by-step AHP
Route::get('/test-ahp-step/{session_id}', function ($sessionId) {
    try {
        $criteria = \App\Models\Kriteria::orderBy('id')->get();
        $comparisons = \App\Models\AhpComparison::where('ahp_session_id', $sessionId)->get();

        // Step 1: Build matrix
        $matrix = \App\Models\AhpComparison::testBuildMatrix($sessionId);

        // Step 2: Calculate weights (manual test)
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

        // Step 3: Normalize weights
        $totalWeight = array_sum($weights);
        $normalizedWeights = [];

        if ($totalWeight > 0) {
            for ($i = 0; $i < $n; $i++) {
                $weight = $weights[$i] / $totalWeight;
                $criteriaName = $criteria[$i]->nama_kriteria;
                $normalizedWeights[$criteriaName] = $weight;
            }
        }

        return response()->json([
            'success' => true,
            'step1_matrix' => $matrix,
            'step2_raw_weights' => $weights,
            'step3_normalized_weights' => $normalizedWeights,
            'total_weight' => $totalWeight,
            'criteria' => $criteria->map(function ($c) {
                return [
                    'id' => $c->id,
                    'nama' => $c->nama_kriteria
                ];
            })
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
});

// Route untuk memeriksa data yang sebenarnya tersimpan
Route::get('/check-data/{session_id}', function ($sessionId) {
    try {
        // 1. Cek criteria
        $criteria = \App\Models\Kriteria::orderBy('id')->get();

        // 2. Cek comparisons langsung dari database
        $comparisons = \App\Models\AhpComparison::where('ahp_session_id', $sessionId)->get();

        // 3. Cek apakah ada data di table ahp_comparisons
        $allComparisons = \App\Models\AhpComparison::all();

        // 4. Cek session
        $session = \App\Models\AhpSession::find($sessionId);

        return response()->json([
            'success' => true,
            'session' => $session ? [
                'id' => $session->id,
                'tahun_ajaran' => $session->tahun_ajaran,
                'semester' => $session->semester
            ] : null,
            'criteria_count' => $criteria->count(),
            'criteria' => $criteria->map(function ($c) {
                return [
                    'id' => $c->id,
                    'kode' => $c->kode_kriteria,
                    'nama' => $c->nama_kriteria,
                    'active' => true
                ];
            }),
            'comparisons_for_session' => $comparisons->map(function ($c) {
                return [
                    'id' => $c->id,
                    'ahp_session_id' => $c->ahp_session_id,
                    'kriteria_1_id' => $c->kriteria_1_id,
                    'kriteria_2_id' => $c->kriteria_2_id,
                    'nilai' => $c->nilai,
                    'created_at' => $c->created_at,
                    'updated_at' => $c->updated_at
                ];
            }),
            'all_comparisons_count' => $allComparisons->count(),
            'all_comparisons' => $allComparisons->map(function ($c) {
                return [
                    'id' => $c->id,
                    'ahp_session_id' => $c->ahp_session_id,
                    'kriteria_1_id' => $c->kriteria_1_id,
                    'kriteria_2_id' => $c->kriteria_2_id,
                    'nilai' => $c->nilai
                ];
            })
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
});

// Route untuk test menyimpan matrix data secara manual
Route::get('/test-save-matrix/{session_id}', function ($sessionId) {
    try {
        // Test data matrix (contoh nilai)
        $testMatrixData = [
            'matrix_1_2' => 3.0,  // Harga vs Jumlah
            'matrix_1_3' => 5.0,  // Harga vs Stok
            'matrix_1_4' => 2.0,  // Harga vs Urgensi
            'matrix_2_3' => 2.0,  // Jumlah vs Stok
            'matrix_2_4' => 4.0,  // Jumlah vs Urgensi
            'matrix_3_4' => 3.0,  // Stok vs Urgensi
        ];

        // Coba simpan matrix
        $saved = \App\Models\AhpComparison::saveMatrixForSession($sessionId, $testMatrixData);

        if ($saved) {
            // Cek apakah data tersimpan
            $comparisons = \App\Models\AhpComparison::where('ahp_session_id', $sessionId)->get();

            return response()->json([
                'success' => true,
                'message' => 'Matrix data saved successfully',
                'saved' => $saved,
                'comparisons_count' => $comparisons->count(),
                'comparisons' => $comparisons->map(function ($c) {
                    return [
                        'kriteria_1_id' => $c->kriteria_1_id,
                        'kriteria_2_id' => $c->kriteria_2_id,
                        'nilai' => $c->nilai
                    ];
                }),
                'test_matrix_data' => $testMatrixData
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save matrix data'
            ]);
        }
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
});

// Route untuk test AHP calculation dengan data yang sudah tersimpan
Route::get('/test-ahp-final/{session_id}', function ($sessionId) {
    try {
        // 1. Cek data yang tersimpan
        $comparisons = \App\Models\AhpComparison::where('ahp_session_id', $sessionId)->get();

        if ($comparisons->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No comparison data found for this session'
            ]);
        }

        // 2. Test matrix building
        $matrix = \App\Models\AhpComparison::testBuildMatrix($sessionId);

        // 3. Test AHP calculation
        $ahpResults = \App\Models\AhpComparison::calculateAHPWeights($sessionId);

        // 4. Test step-by-step calculation
        $criteria = \App\Models\Kriteria::orderBy('id')->get();
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
                $criteriaName = $criteria[$i]->nama_kriteria;
                $normalizedWeights[$criteriaName] = $weight;
            }
        }

        return response()->json([
            'success' => true,
            'comparisons_count' => $comparisons->count(),
            'matrix' => $matrix,
            'raw_weights' => $weights,
            'normalized_weights' => $normalizedWeights,
            'ahp_results' => $ahpResults,
            'total_weight' => $totalWeight
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
});

// Route untuk test dengan matrix yang lebih konsisten
Route::get('/test-ahp-consistent/{session_id}', function ($sessionId) {
    try {
        // Matrix yang lebih konsisten (CR < 0.1)
        $consistentMatrixData = [
            'matrix_1_2' => 2.0,  // Harga vs Jumlah (sedikit lebih penting)
            'matrix_1_3' => 3.0,  // Harga vs Stok (lebih penting)
            'matrix_1_4' => 4.0,  // Harga vs Urgensi (sangat penting)
            'matrix_2_3' => 2.0,  // Jumlah vs Stok (sedikit lebih penting)
            'matrix_2_4' => 3.0,  // Jumlah vs Urgensi (lebih penting)
            'matrix_3_4' => 2.0,  // Stok vs Urgensi (sedikit lebih penting)
        ];

        // Simpan matrix yang konsisten
        $saved = \App\Models\AhpComparison::saveMatrixForSession($sessionId, $consistentMatrixData);

        if ($saved) {
            // Test AHP calculation
            $ahpResults = \App\Models\AhpComparison::calculateAHPWeights($sessionId);

            return response()->json([
                'success' => true,
                'message' => 'Consistent matrix saved and calculated',
                'matrix_data' => $consistentMatrixData,
                'ahp_results' => $ahpResults,
                'consistency_status' => $ahpResults['consistent'] ? 'Konsisten' : 'Tidak Konsisten',
                'cr_value' => $ahpResults['cr'],
                'threshold' => 0.1
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save consistent matrix'
            ]);
        }
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
});

// Route untuk test apakah data tersimpan setelah save
Route::get('/test-save-status/{session_id}', function ($sessionId) {
    try {
        // 1. Cek comparisons
        $comparisons = \App\Models\AhpComparison::where('ahp_session_id', $sessionId)->get();

        // 2. Cek AHP results
        $ahpResults = \App\Models\AhpResult::where('ahp_session_id', $sessionId)->get();

        // 3. Cek matrix data
        $matrixData = \App\Models\AhpComparison::getMatrixForSession($sessionId);

        // 4. Cek apakah bisa dihitung ulang
        $calculatedResults = \App\Models\AhpComparison::calculateAHPWeights($sessionId);

        return response()->json([
            'success' => true,
            'session_id' => $sessionId,
            'comparisons_count' => $comparisons->count(),
            'comparisons' => $comparisons->map(function ($c) {
                return [
                    'kriteria_1_id' => $c->kriteria_1_id,
                    'kriteria_2_id' => $c->kriteria_2_id,
                    'nilai' => $c->nilai
                ];
            }),
            'ahp_results_count' => $ahpResults->count(),
            'ahp_results' => $ahpResults->map(function ($r) {
                return [
                    'kriteria_id' => $r->kriteria_id,
                    'bobot' => $r->bobot
                ];
            }),
            'matrix_data' => $matrixData,
            'calculated_results' => $calculatedResults,
            'status' => [
                'has_comparisons' => $comparisons->count() > 0,
                'has_ahp_results' => $ahpResults->count() > 0,
                'can_calculate' => !empty($calculatedResults['weights']),
                'is_consistent' => $calculatedResults['consistent'] ?? false
            ]
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
});

// Route untuk test API calculate-weights
Route::get('/test-api-calculate/{session_id}', function ($sessionId) {
    try {
        // Simulate POST request to API
        $request = new \Illuminate\Http\Request();
        $request->merge(['session_id' => $sessionId]);

        $controller = new \App\Http\Controllers\AhpController(new \App\Services\AhpService());
        $response = $controller->calculateWeights($request);

        return $response;
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
});

// AHP Analysis Routes
Route::get('/analyze-historical-data', function () {
    $analysis = \App\Filament\Resources\AhpResultResource::analyzeHistoricalData();

    return response()->json([
        'success' => true,
        'data' => $analysis,
        'recommendations' => [
            'current_thresholds' => [
                'A' => 0.3,
                'B' => 0.2,
                'C' => 0.15,
                'D' => 0.1,
                'E' => '< 0.1'
            ],
            'percentile_based_thresholds' => $analysis['recommended_thresholds']['percentile_based'] ?? [],
            'statistical_based_thresholds' => $analysis['recommended_thresholds']['statistical_based'] ?? []
        ]
    ]);
});

// Export Routes
Route::prefix('export')->group(function () {
    Route::get('/ranking/{prodiId?}', [ExportController::class, 'exportRankingPerProdi'])->name('export.ranking');
    Route::get('/ranking-advanced', [ExportController::class, 'exportRankingAdvanced'])->name('export.ranking.advanced');
    Route::get('/summary/{sessionId}', [ExportController::class, 'exportSummaryPerProdi'])->name('export.summary');
    Route::get('/procurement/{prodiId?}', [ExportController::class, 'exportProcurementList'])->name('export.procurement');
    Route::get('/ahp-results/{sessionId}', [ExportController::class, 'exportAHPResults'])->name('export.ahp-results');
    Route::get('/form/{sessionId?}', [ExportController::class, 'showExportForm'])->name('export.form');
    Route::get('/prodi-list', [ExportController::class, 'getProdiList'])->name('export.prodi-list');
    Route::get('/stats', [ExportController::class, 'getExportStats'])->name('export.stats');

    // Export routes untuk Tim Pengadaan
    Route::get('/pengajuan-per-prodi', [ExportController::class, 'exportPengajuanPerProdi'])->name('export.pengajuan.per.prodi');
    Route::get('/ranking-ahp-per-prodi', [ExportController::class, 'exportRankingPerProdiAdvanced'])->name('export.ranking.ahp.per.prodi');
    Route::get('/rekap-data', [ExportController::class, 'exportRekapData'])->name('export.rekap.data');
});

// Health Check Routes
Route::get('/health', function () {
    $health = [
        'status' => 'healthy',
        'timestamp' => now()->toISOString(),
        'version' => '1.0.0',
        'environment' => config('app.env'),
        'checks' => []
    ];

    // Database connection check
    try {
        \Illuminate\Support\Facades\DB::connection()->getPdo();
        $health['checks']['database'] = [
            'status' => 'healthy',
            'message' => 'Database connection successful',
            'connection' => config('database.default')
        ];
    } catch (\Exception $e) {
        $health['checks']['database'] = [
            'status' => 'unhealthy',
            'message' => 'Database connection failed: ' . $e->getMessage()
        ];
        $health['status'] = 'unhealthy';
    }

    // Queue check
    try {
        $queueConnection = config('queue.default');
        $health['checks']['queue'] = [
            'status' => 'healthy',
            'message' => 'Queue system available',
            'connection' => $queueConnection
        ];
    } catch (\Exception $e) {
        $health['checks']['queue'] = [
            'status' => 'unhealthy',
            'message' => 'Queue system failed: ' . $e->getMessage()
        ];
        $health['status'] = 'unhealthy';
    }

    // Storage check
    try {
        $storagePath = storage_path();
        $diskFreeSpace = disk_free_space($storagePath);
        $diskTotalSpace = disk_total_space($storagePath);
        $diskUsagePercent = (($diskTotalSpace - $diskFreeSpace) / $diskTotalSpace) * 100;

        $health['checks']['storage'] = [
            'status' => $diskUsagePercent < 90 ? 'healthy' : 'warning',
            'message' => 'Storage space available',
            'free_space' => formatBytes($diskFreeSpace),
            'total_space' => formatBytes($diskTotalSpace),
            'usage_percent' => round($diskUsagePercent, 2)
        ];

        if ($diskUsagePercent >= 90) {
            $health['status'] = 'warning';
        }
    } catch (\Exception $e) {
        $health['checks']['storage'] = [
            'status' => 'unhealthy',
            'message' => 'Storage check failed: ' . $e->getMessage()
        ];
        $health['status'] = 'unhealthy';
    }

    // Cache check
    try {
        \Illuminate\Support\Facades\Cache::put('health_check', 'ok', 10);
        $cacheValue = \Illuminate\Support\Facades\Cache::get('health_check');

        $health['checks']['cache'] = [
            'status' => $cacheValue === 'ok' ? 'healthy' : 'unhealthy',
            'message' => $cacheValue === 'ok' ? 'Cache system working' : 'Cache system not working',
            'driver' => config('cache.default')
        ];

        if ($cacheValue !== 'ok') {
            $health['status'] = 'unhealthy';
        }
    } catch (\Exception $e) {
        $health['checks']['cache'] = [
            'status' => 'unhealthy',
            'message' => 'Cache check failed: ' . $e->getMessage()
        ];
        $health['status'] = 'unhealthy';
    }

    // AHP System check
    try {
        $criteriaCount = \App\Models\Kriteria::count();
        $sessionCount = \App\Models\AhpSession::count();
        $activeSessionCount = \App\Models\AhpSession::active()->count();
        $pengajuanCount = \App\Models\PengajuanBahanAjar::count();

        $health['checks']['ahp_system'] = [
            'status' => 'healthy',
            'message' => 'AHP system operational',
            'active_criteria' => $criteriaCount,
            'total_sessions' => $sessionCount,
            'active_sessions' => $activeSessionCount,
            'total_pengajuan' => $pengajuanCount
        ];
    } catch (\Exception $e) {
        $health['checks']['ahp_system'] = [
            'status' => 'unhealthy',
            'message' => 'AHP system check failed: ' . $e->getMessage()
        ];
        $health['status'] = 'unhealthy';
    }

    // Response status code
    $statusCode = $health['status'] === 'healthy' ? 200 : ($health['status'] === 'warning' ? 200 : 503);

    return response()->json($health, $statusCode);
})->name('health');

// Helper function for formatting bytes
if (!function_exists('formatBytes')) {
    function formatBytes($bytes, $precision = 2)
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
