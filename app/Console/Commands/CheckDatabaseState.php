<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Kriteria;
use App\Models\AhpSession;
use App\Models\AhpResult;
use App\Models\PengajuanBahanAjar;

class CheckDatabaseState extends Command
{
    protected $signature = 'db:check-state';
    protected $description = 'Check the current state of the database for debugging';

    public function handle()
    {
        $this->info('🔍 Checking Database State...');

        // Check Criteria
        $this->info('📋 Criteria:');
        $criteria = Kriteria::all();
        if ($criteria->isEmpty()) {
            $this->error('  No criteria found!');
        } else {
            foreach ($criteria as $c) {
                $this->line("  - {$c->id}: {$c->nama_kriteria} ({$c->kode_kriteria})");
            }
        }

        // Check AHP Sessions
        $this->info('📊 AHP Sessions:');
        $sessions = AhpSession::all();
        if ($sessions->isEmpty()) {
            $this->error('  No AHP sessions found!');
        } else {
            foreach ($sessions as $s) {
                $this->line("  - {$s->id}: {$s->tahun_ajaran} {$s->semester}");
            }
        }

        // Check AHP Results
        $this->info('⚖️ AHP Results:');
        $results = AhpResult::with('kriteria')->get();
        if ($results->isEmpty()) {
            $this->error('  No AHP results found!');
        } else {
            foreach ($results as $r) {
                $this->line("  - Session {$r->ahp_session_id}: {$r->kriteria->nama_kriteria} = {$r->bobot}");
            }
        }

        // Check Pengajuan
        $this->info('📦 Pengajuan:');
        $pengajuan = PengajuanBahanAjar::all();
        if ($pengajuan->isEmpty()) {
            $this->error('  No pengajuan found!');
        } else {
            $withScores = $pengajuan->whereNotNull('ahp_score')->count();
            $withRankings = $pengajuan->whereNotNull('ranking_position')->count();
            $this->line("  - Total: {$pengajuan->count()}");
            $this->line("  - With AHP scores: {$withScores}");
            $this->line("  - With rankings: {$withRankings}");
        }

        $this->info('✅ Database state check completed!');
    }
}
