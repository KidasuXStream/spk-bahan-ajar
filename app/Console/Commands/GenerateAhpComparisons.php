<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AhpSession;
use App\Models\AhpComparison;
use App\Models\Kriteria;
use App\Models\AhpResult;

class GenerateAhpComparisons extends Command
{
    protected $signature = 'ahp:generate-comparisons {--session-id= : Generate for specific session ID} {--all : Generate for all sessions}';
    protected $description = 'Generate AHP comparison data for sessions';

    public function handle()
    {
        $this->info('🚀 Generating AHP Comparison Data...');

        if ($this->option('session-id')) {
            $sessionId = $this->option('session-id');
            $this->generateForSession($sessionId);
        } elseif ($this->option('all')) {
            $this->generateForAllSessions();
        } else {
            $this->error('Please specify --session-id=X or --all');
            return 1;
        }

        $this->info('✅ AHP Comparison generation completed!');
        return 0;
    }

    private function generateForAllSessions()
    {
        $sessions = AhpSession::all();
        $this->info("Found {$sessions->count()} sessions");

        foreach ($sessions as $session) {
            $this->info("Processing Session {$session->id}: {$session->tahun_ajaran} {$session->semester}");
            $this->generateForSession($session->id);
        }
    }

    private function generateForSession($sessionId)
    {
        $session = AhpSession::find($sessionId);
        if (!$session) {
            $this->error("Session {$sessionId} not found!");
            return;
        }

        // Check if already has comparisons
        $existingCount = AhpComparison::where('ahp_session_id', $sessionId)->count();
        if ($existingCount > 0) {
            $this->warn("Session {$sessionId} already has {$existingCount} comparisons. Skipping...");
            return;
        }

        $this->info("Generating comparisons for Session {$sessionId}...");

        // Get active criteria
        $criteria = Kriteria::orderBy('id')->get();
        if ($criteria->count() < 2) {
            $this->error("Need at least 2 active criteria!");
            return;
        }

        $this->info("Found {$criteria->count()} criteria: " . $criteria->pluck('nama_kriteria')->implode(', '));

        // Generate realistic comparison values
        $comparisons = [];
        $comparisonCount = 0;

        foreach ($criteria as $i => $c1) {
            foreach ($criteria as $j => $c2) {
                if ($i < $j) { // Only upper triangular
                    $value = $this->generateRealisticComparison($c1, $c2);
                    
                    $comparisons[] = [
                        'ahp_session_id' => $sessionId,
                        'kriteria_1_id' => $c1->id,
                        'kriteria_2_id' => $c2->id,
                        'nilai' => $value,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                    $comparisonCount++;

                    $this->line("  {$c1->nama_kriteria} vs {$c2->nama_kriteria}: {$value}");
                }
            }
        }

        // Save comparisons
        if (!empty($comparisons)) {
            AhpComparison::insert($comparisons);
            $this->info("✅ Saved {$comparisonCount} comparisons for Session {$sessionId}");

            // Calculate and save AHP results
            $this->calculateAndSaveResults($sessionId);
        } else {
            $this->error("No comparisons generated!");
        }
    }

    private function generateRealisticComparison($criteria1, $criteria2)
    {
        // Generate realistic AHP comparison values based on criteria characteristics
        $c1Name = strtolower($criteria1->nama_kriteria);
        $c2Name = strtolower($criteria2->nama_kriteria);

        // Define priority relationships
        $priorityMatrix = [
            'harga' => [
                'jumlah' => 0.33,    // Harga lebih penting dari jumlah
                'stok' => 0.5,       // Harga lebih penting dari stok
                'urgensi' => 0.25    // Harga lebih penting dari urgensi
            ],
            'jumlah' => [
                'harga' => 3,        // Jumlah kurang penting dari harga
                'stok' => 2,         // Jumlah lebih penting dari stok
                'urgensi' => 0.5     // Jumlah kurang penting dari urgensi
            ],
            'stok' => [
                'harga' => 2,        // Stok kurang penting dari harga
                'jumlah' => 0.5,     // Stok kurang penting dari jumlah
                'urgensi' => 0.33    // Stok kurang penting dari urgensi
            ],
            'urgensi' => [
                'harga' => 4,        // Urgensi lebih penting dari harga
                'jumlah' => 2,       // Urgensi lebih penting dari jumlah
                'stok' => 3          // Urgensi lebih penting dari stok
            ]
        ];

        // Check if we have a defined relationship
        foreach ($priorityMatrix as $key1 => $relations) {
            if (str_contains($c1Name, $key1)) {
                foreach ($relations as $key2 => $value) {
                    if (str_contains($c2Name, $key2)) {
                        return $value;
                    }
                }
            }
        }

        // Default: random realistic value between 0.2 and 5
        $values = [0.2, 0.25, 0.33, 0.5, 1, 2, 3, 4, 5];
        return $values[array_rand($values)];
    }

    private function calculateAndSaveResults($sessionId)
    {
        $this->info("Calculating AHP results for Session {$sessionId}...");

        try {
            // Use the existing AHP calculation method
            $results = \App\Models\AhpComparison::calculateAHPWeights($sessionId);

            if (empty($results['weights'])) {
                $this->error("Failed to calculate AHP weights!");
                return;
            }

            // Save results
            foreach ($results['weights'] as $criteriaName => $weight) {
                $criteria = Kriteria::where('nama_kriteria', $criteriaName)->first();
                if ($criteria) {
                    AhpResult::updateOrCreate(
                        [
                            'ahp_session_id' => $sessionId,
                            'kriteria_id' => $criteria->id,
                        ],
                        [
                            'bobot' => $weight,
                        ]
                    );
                }
            }

            $this->info("✅ AHP results saved. CR: " . number_format($results['cr'], 4) . 
                       " (" . ($results['consistent'] ? 'Consistent' : 'Inconsistent') . ")");

        } catch (\Exception $e) {
            $this->error("Failed to calculate AHP results: " . $e->getMessage());
        }
    }
}
