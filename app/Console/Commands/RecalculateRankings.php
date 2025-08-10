<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AhpSession;
use App\Models\PengajuanBahanAjar;
use App\Models\AhpResult;
use App\Services\AhpService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class RecalculateRankings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ahp:recalculate-rankings {--session-id= : Specific AHP session ID to recalculate} {--force : Force recalculation even if rankings exist}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculate AHP rankings for all submissions in AHP sessions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Recalculating AHP Rankings...');

        $sessionId = $this->option('session-id');
        $force = $this->option('force');

        if ($sessionId) {
            $this->recalculateSpecificSession($sessionId, $force);
        } else {
            $this->recalculateAllSessions($force);
        }

        return 0;
    }

    private function recalculateSpecificSession($sessionId, $force)
    {
        $this->info("Recalculating rankings for session ID: {$sessionId}");

        try {
            $session = AhpSession::find($sessionId);
            if (!$session) {
                $this->error("Session ID {$sessionId} not found");
                return;
            }

            $this->recalculateSessionRankings($session, $force);
        } catch (\Exception $e) {
            $this->error("Error recalculating session {$sessionId}: " . $e->getMessage());
            Log::error("Ranking recalculation failed for session {$sessionId}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    private function recalculateAllSessions($force)
    {
        $this->info('Recalculating rankings for all AHP sessions...');

        $sessions = AhpSession::orderBy('created_at', 'desc')->get();

        if ($sessions->isEmpty()) {
            $this->warn('No AHP sessions found');
            return;
        }

        $this->info("Found {$sessions->count()} session(s)");

        $bar = $this->output->createProgressBar($sessions->count());
        $bar->start();

        foreach ($sessions as $session) {
            try {
                $this->recalculateSessionRankings($session, $force);
            } catch (\Exception $e) {
                $this->newLine();
                $this->error("Failed to recalculate session {$session->id}: " . $e->getMessage());
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
    }

    private function recalculateSessionRankings($session, $force)
    {
        $this->line("  📊 Session: {$session->tahun_ajaran} - {$session->semester}");

        // Check if rankings already exist
        $existingRankings = PengajuanBahanAjar::where('ahp_session_id', $session->id)
            ->whereNotNull('ranking_position')
            ->count();

        if ($existingRankings > 0 && !$force) {
            $this->line("    Rankings already exist ({$existingRankings} items). Use --force to recalculate.");
            return;
        }

        // Get AHP weights for this session
        $weights = AhpResult::where('ahp_session_id', $session->id)
            ->with('kriteria')
            ->get()
            ->pluck('bobot', 'kriteria.nama_kriteria')
            ->toArray();

        if (empty($weights)) {
            $this->warn("    No AHP weights found for session {$session->id}");
            return;
        }

        $this->line("    Found " . count($weights) . " criteria weights");

        // Get all submissions for this session
        $submissions = PengajuanBahanAjar::where('ahp_session_id', $session->id)->get();

        if ($submissions->isEmpty()) {
            $this->warn("    No submissions found for session {$session->id}");
            return;
        }

        $this->line("    Processing {$submissions->count()} submissions...");

        // Calculate scores and update submissions
        $updatedCount = 0;
        foreach ($submissions as $submission) {
            try {
                $score = $this->calculateSubmissionScore($submission, $weights);
                $submission->update(['ahp_score' => $score]);
                $updatedCount++;
            } catch (\Exception $e) {
                $this->error("    Failed to calculate score for submission {$submission->id}: " . $e->getMessage());
            }
        }

        $this->line("    Updated {$updatedCount} submission scores");

        // Update rankings based on scores
        $rankedSubmissions = PengajuanBahanAjar::where('ahp_session_id', $session->id)
            ->whereNotNull('ahp_score')
            ->orderBy('ahp_score', 'desc')
            ->get();

        $rankingCount = 0;
        foreach ($rankedSubmissions as $index => $submission) {
            $rankingPosition = $index + 1;
            $submission->update(['ranking_position' => $rankingPosition]);
            $rankingCount++;
        }

        $this->line("    Updated {$rankingCount} rankings");

        // Clear any existing caches
        $this->clearSessionCaches($session->id);

        $this->line("    ✅ Session {$session->id} rankings recalculated successfully");
    }

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
        }

        // Normalize by total weight if available
        if ($totalWeight > 0) {
            $score = $score / $totalWeight;
        }

        return round($score, 6);
    }

    private function normalizeHarga($submission): float
    {
        // Lower price = higher score (inverse relationship)
        $harga = max($submission->harga_satuan, 1);
        return 1 / $harga;
    }

    private function normalizeJumlah($submission): float
    {
        // Higher quantity = higher score
        return min($submission->jumlah / 100, 1); // Normalize to 0-1 range
    }

    private function normalizeStok($submission): float
    {
        // Lower stock = higher score (inverse relationship)
        $stok = max($submission->stok ?? 0, 1);
        return 1 / $stok;
    }

    private function normalizeUrgensi($submission): float
    {
        // Convert urgency to numeric value
        $urgency = $submission->urgensi_institusi ?? $submission->urgensi_prodi ?? 'rendah';

        return match ($urgency) {
            'tinggi' => 1.0,
            'sedang' => 0.6,
            'rendah' => 0.3,
            default => 0.3
        };
    }

    private function clearSessionCaches($sessionId)
    {
        // Clear any cached data for this session
        $cacheKeys = [
            "ahp_results_{$sessionId}",
            "ahp_rankings_{$sessionId}",
            "ahp_matrix_{$sessionId}"
        ];

        foreach ($cacheKeys as $key) {
            \Illuminate\Support\Facades\Cache::forget($key);
        }
    }
}
