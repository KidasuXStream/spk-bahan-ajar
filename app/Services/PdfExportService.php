<?php

namespace App\Services;

use App\Models\AhpSession;
use App\Models\PengajuanBahanAjar;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Cache;

class PdfExportService
{
    /**
     * Export AHP results to PDF
     */
    public function exportAhpResults(int $sessionId): string
    {
        $session = AhpSession::with('ahpResults.kriteria')->findOrFail($sessionId);
        $rankings = $this->getRankings($sessionId);

        $data = [
            'session' => $session,
            'rankings' => $rankings,
            'exported_at' => now()->format('d/m/Y H:i:s'),
        ];

        $pdf = Pdf::loadView('exports.ahp-results-pdf', $data);
        $pdf->setPaper('A4', 'portrait');

        return $pdf->output();
    }

    /**
     * Export ranking per prodi to PDF
     */
    public function exportRankingPerProdi(int $sessionId, string $prodi = null): string
    {
        $session = AhpSession::findOrFail($sessionId);
        $rankings = $this->getRankings($sessionId);

        if ($prodi) {
            $rankings = collect($rankings)->filter(function ($item) use ($prodi) {
                return $item['prodi'] === $prodi;
            })->values()->all();
        }

        $data = [
            'session' => $session,
            'rankings' => $rankings,
            'prodi' => $prodi,
            'exported_at' => now()->format('d/m/Y H:i:s'),
        ];

        $pdf = Pdf::loadView('exports.ranking-per-prodi-pdf', $data);
        $pdf->setPaper('A4', 'portrait');

        return $pdf->output();
    }

    /**
     * Export procurement list to PDF
     */
    public function exportProcurementList(int $sessionId): string
    {
        $session = AhpSession::findOrFail($sessionId);
        $rankings = $this->getRankings($sessionId);

        // Filter only approved/processed items
        $procurementItems = collect($rankings)->filter(function ($item) {
            return $item['status_pengajuan'] === 'diproses';
        })->values()->all();

        $data = [
            'session' => $session,
            'items' => $procurementItems,
            'exported_at' => now()->format('d/m/Y H:i:s'),
        ];

        $pdf = Pdf::loadView('exports.procurement-list-pdf', $data);
        $pdf->setPaper('A4', 'portrait');

        return $pdf->output();
    }

    /**
     * Export summary per prodi to PDF
     */
    public function exportSummaryPerProdi(int $sessionId): string
    {
        $session = AhpSession::findOrFail($sessionId);
        $rankings = $this->getRankings($sessionId);

        // Group by prodi
        $summaryByProdi = collect($rankings)->groupBy('prodi')->map(function ($items, $prodi) {
            return [
                'prodi' => $prodi,
                'total_items' => count($items),
                'total_budget' => collect($items)->sum('total_harga'),
                'avg_score' => collect($items)->avg('score'),
                'top_items' => collect($items)->take(5)->values()->all(),
            ];
        })->values()->all();

        $data = [
            'session' => $session,
            'summary' => $summaryByProdi,
            'exported_at' => now()->format('d/m/Y H:i:s'),
        ];

        $pdf = Pdf::loadView('exports.summary-per-prodi-pdf', $data);
        $pdf->setPaper('A4', 'portrait');

        return $pdf->output();
    }

    /**
     * Get rankings from cache or calculate
     */
    private function getRankings(int $sessionId): array
    {
        return Cache::remember("ahp_rankings_{$sessionId}", 3600, function () use ($sessionId) {
            $ahpService = new AhpService();
            return $ahpService->getSessionRankings($sessionId);
        });
    }

    /**
     * Get filename for export
     */
    public function getFilename(string $type, int $sessionId, string $prodi = null): string
    {
        $session = AhpSession::findOrFail($sessionId);
        $timestamp = now()->format('Y-m-d_H-i-s');

        switch ($type) {
            case 'ahp-results':
                return "AHP_Results_{$session->tahun_ajaran}_{$session->semester}_{$timestamp}.pdf";
            case 'ranking-per-prodi':
                $prodiLabel = $prodi ? strtoupper($prodi) : 'ALL';
                return "Ranking_{$prodiLabel}_{$session->tahun_ajaran}_{$session->semester}_{$timestamp}.pdf";
            case 'procurement-list':
                return "Procurement_List_{$session->tahun_ajaran}_{$session->semester}_{$timestamp}.pdf";
            case 'summary-per-prodi':
                return "Summary_Per_Prodi_{$session->tahun_ajaran}_{$session->semester}_{$timestamp}.pdf";
            default:
                return "Export_{$session->tahun_ajaran}_{$session->semester}_{$timestamp}.pdf";
        }
    }
}
