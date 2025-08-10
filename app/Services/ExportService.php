<?php

namespace App\Services;

use App\Models\PengajuanBahanAjar;
use App\Models\AhpSession;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ExportService
{
    /**
     * Get data for ranking export with filters
     */
    public function getRankingData($prodiId = null, $sessionId = null, $filters = [])
    {
        $query = PengajuanBahanAjar::with([
            'user',
            'ahpSession'
        ]);

        // Filter by prodi if specified
        if ($prodiId) {
            $query->whereHas('user', function ($q) use ($prodiId) {
                $q->where('prodi', $prodiId);
            });
        }

        // Filter by AHP session if specified, otherwise prioritize active sessions
        if ($sessionId) {
            $query->where('ahp_session_id', $sessionId);
        } else {
            $query->whereHas('ahpSession', function ($q) {
                $q->where('is_active', true);
            });
        }

        // Apply additional filters
        if (!empty($filters['status'])) {
            $query->where('status_pengajuan', $filters['status']);
        }

        if (!empty($filters['urgensi'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('urgensi_prodi', $filters['urgensi'])
                    ->orWhere('urgensi_tim_pengadaan', $filters['urgensi'])
                    ->orWhere('urgensi_institusi', $filters['urgensi']);
            });
        }

        // Get data
        $pengajuan = $query->get();

        // Calculate AHP scores if not available
        $this->calculateMissingAhpScores($pengajuan);

        // Sort data
        return $this->sortRankingData($pengajuan);
    }

    /**
     * Get summary data for a specific session
     */
    public function getSummaryData($sessionId)
    {
        $session = AhpSession::findOrFail($sessionId);

        $prodis = User::whereNotNull('prodi')
            ->distinct()
            ->pluck('prodi')
            ->filter()
            ->toArray();

        $summary = [
            'session' => $session,
            'prodis' => [],
            'total_budget' => 0,
            'total_items' => 0,
            'priority_distribution' => [
                'diprioritaskan' => 0,
                'sedang' => 0,
                'dapat_ditunda' => 0
            ]
        ];

        foreach ($prodis as $prodi) {
            $prodiData = $this->getProdiSummary($sessionId, $prodi);
            $summary['prodis'][$prodi] = $prodiData;
            $summary['total_budget'] += $prodiData['total_budget'];
            $summary['total_items'] += $prodiData['total_items'];

            // Update priority distribution
            $summary['priority_distribution']['diprioritaskan'] += $prodiData['priority_distribution']['diprioritaskan'];
            $summary['priority_distribution']['sedang'] += $prodiData['priority_distribution']['sedang'];
            $summary['priority_distribution']['dapat_ditunda'] += $prodiData['priority_distribution']['dapat_ditunda'];
        }

        return $summary;
    }

    /**
     * Get summary data for a specific prodi in a session
     */
    protected function getProdiSummary($sessionId, $prodi)
    {
        $items = PengajuanBahanAjar::where('ahp_session_id', $sessionId)
            ->whereHas('user', function ($q) use ($prodi) {
                $q->where('prodi', $prodi);
            })
            ->with(['user'])
            ->get();

        $this->calculateMissingAhpScores($items);

        $totalBudget = $items->sum(function ($item) {
            return $item->jumlah * $item->harga_satuan;
        });

        $priorityDistribution = [
            'diprioritaskan' => 0,
            'sedang' => 0,
            'dapat_ditunda' => 0
        ];

        foreach ($items as $item) {
            $priority = $this->getPriorityStatus($item->ahp_score);
            $priorityDistribution[$priority]++;
        }

        return [
            'total_items' => $items->count(),
            'total_budget' => $totalBudget,
            'priority_distribution' => $priorityDistribution,
            'items' => $items->sortBy('ranking_position')->values()
        ];
    }

    /**
     * Calculate missing AHP scores
     */
    protected function calculateMissingAhpScores(Collection $pengajuan)
    {
        foreach ($pengajuan as $item) {
            if ($item->ahp_session_id && !$item->ahp_score) {
                // Try to get AHP score from AhpResult
                $ahpResult = \App\Models\AhpResult::where('ahp_session_id', $item->ahp_session_id)
                    ->where('kriteria_id', function ($query) {
                        $query->select('id')
                            ->from('kriterias')
                            ->where('nama_kriteria', 'Harga')
                            ->first();
                    })
                    ->first();

                if ($ahpResult) {
                    $item->ahp_score = $ahpResult->bobot;
                }
            }
        }
    }

    /**
     * Sort ranking data
     */
    protected function sortRankingData(Collection $pengajuan)
    {
        return $pengajuan->sortBy([
            ['ranking_position', 'asc'],
            ['ahp_score', 'desc'],
            ['urgensi_prodi', 'desc'],
            ['urgensi_tim_pengadaan', 'desc']
        ])->values();
    }

    /**
     * Get priority status based on AHP score
     */
    protected function getPriorityStatus($score)
    {
        if (empty($score) || $score === 0) {
            return 'dapat_ditunda';
        }

        if ($score >= 0.25) {
            return 'diprioritaskan';
        } elseif ($score >= 0.15) {
            return 'sedang';
        } else {
            return 'dapat_ditunda';
        }
    }

    /**
     * Get export statistics
     */
    public function getExportStatistics()
    {
        $stats = [
            'total_pengajuan' => PengajuanBahanAjar::count(),
            'pengajuan_per_prodi' => [],
            'status_distribution' => [],
            'urgensi_distribution' => [],
            'session_info' => []
        ];

        // Get pengajuan per prodi
        $prodis = User::whereNotNull('prodi')
            ->distinct()
            ->pluck('prodi');

        foreach ($prodis as $prodi) {
            $count = PengajuanBahanAjar::whereHas('user', function ($query) use ($prodi) {
                $query->where('prodi', $prodi);
            })->count();

            $stats['pengajuan_per_prodi'][$prodi] = $count;
        }

        // Get status distribution
        $stats['status_distribution'] = PengajuanBahanAjar::selectRaw('status_pengajuan, COUNT(*) as count')
            ->groupBy('status_pengajuan')
            ->pluck('count', 'status_pengajuan')
            ->toArray();

        // Get urgensi distribution
        $stats['urgensi_distribution'] = [
            'prodi' => PengajuanBahanAjar::selectRaw('urgensi_prodi, COUNT(*) as count')
                ->groupBy('urgensi_prodi')
                ->pluck('count', 'urgensi_prodi')
                ->toArray(),
            'tim_pengadaan' => PengajuanBahanAjar::selectRaw('urgensi_tim_pengadaan, COUNT(*) as count')
                ->groupBy('urgensi_tim_pengadaan')
                ->pluck('count', 'urgensi_tim_pengadaan')
                ->toArray(),
            'institusi' => PengajuanBahanAjar::selectRaw('urgensi_institusi, COUNT(*) as count')
                ->groupBy('urgensi_institusi')
                ->pluck('count', 'urgensi_institusi')
                ->toArray(),
        ];

        // Get session info
        $stats['session_info'] = [
            'total_sessions' => AhpSession::count(),
            'active_sessions' => AhpSession::active()->count(),
            'recent_sessions' => AhpSession::orderBy('created_at', 'desc')
                ->take(5)
                ->get()
                ->map(function ($session) {
                    return [
                        'id' => $session->id,
                        'tahun_ajaran' => $session->tahun_ajaran,
                        'semester' => $session->semester,
                        'is_active' => $session->is_active
                    ];
                })
        ];

        return $stats;
    }

    /**
     * Validate export parameters
     */
    public function validateExportParameters($prodiId = null, $sessionId = null, $filters = [])
    {
        $errors = [];

        // Validate prodi if specified
        if ($prodiId && !in_array($prodiId, ['trpl', 'mesin', 'elektro', 'sipil', 'kimia'])) {
            $errors[] = 'Program studi tidak valid';
        }

        // Validate session if specified
        if ($sessionId) {
            $session = AhpSession::find($sessionId);
            if (!$session) {
                $errors[] = 'Session AHP tidak ditemukan';
            }
        }

        // Validate filters
        if (!empty($filters['status']) && !in_array($filters['status'], ['pending', 'approved', 'rejected', 'in_progress'])) {
            $errors[] = 'Status filter tidak valid';
        }

        if (!empty($filters['urgensi']) && !in_array($filters['urgensi'], ['sangat_rendah', 'rendah', 'sedang', 'tinggi', 'sangat_tinggi'])) {
            $errors[] = 'Urgensi filter tidak valid';
        }

        return $errors;
    }
}
