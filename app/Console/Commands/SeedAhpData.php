<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AhpSession;
use App\Models\AhpComparison;
use App\Models\AhpResult;
use App\Models\Kriteria;
use Illuminate\Support\Facades\DB;

class SeedAhpData extends Command
{
    protected $signature = 'ahp:seed-data {--session-id= : Seed for specific session ID} {--all : Seed for all sessions} {--truncate : Truncate existing data first}';
    protected $description = 'Seed AHP comparison and result data with option to truncate existing data';

    public function handle()
    {
        $this->info('🚀 Starting AHP Data Seeding...');

        if ($this->option('truncate')) {
            $this->truncateAhpData();
        }

        if ($this->option('session-id')) {
            $sessionId = $this->option('session-id');
            $this->seedForSession($sessionId);
        } elseif ($this->option('all')) {
            $this->seedForAllSessions();
        } else {
            $this->error('Please specify --session-id=X or --all');
            return 1;
        }

        $this->info('✅ AHP Data seeding completed!');
        return 0;
    }

    private function truncateAhpData()
    {
        $this->warn('🗑️ Truncating existing AHP data...');

        // Disable foreign key checks temporarily
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Truncate tables
        AhpComparison::truncate();
        AhpResult::truncate();

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->info('   ✅ Truncated ahp_comparisons table');
        $this->info('   ✅ Truncated ahp_results table');
    }

    private function seedForAllSessions()
    {
        $sessions = AhpSession::all();
        $this->info("Found {$sessions->count()} sessions");

        foreach ($sessions as $session) {
            $this->info("Processing Session {$session->id}: {$session->tahun_ajaran} {$session->semester}");
            $this->seedForSession($session->id);
        }
    }

    private function seedForSession($sessionId)
    {
        $session = AhpSession::find($sessionId);
        if (!$session) {
            $this->error("Session {$sessionId} not found!");
            return;
        }

        $this->info("Seeding data for Session {$sessionId}...");

        // Get all criteria (is_active column was removed)
        $criteria = Kriteria::orderBy('id')->get();
        if ($criteria->count() < 2) {
            $this->error("Need at least 2 active criteria!");
            return;
        }

        $this->info("Found {$criteria->count()} criteria: " . $criteria->pluck('nama_kriteria')->implode(', '));

        // Generate AHP comparisons
        $this->generateComparisons($sessionId, $criteria);

        // Calculate and save AHP results
        $this->calculateAndSaveResults($sessionId);
    }

    private function generateComparisons($sessionId, $criteria)
    {
        $this->info("Generating AHP comparisons...");

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
