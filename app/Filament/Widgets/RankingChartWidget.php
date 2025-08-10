<?php

namespace App\Filament\Widgets;

use App\Models\AhpResult;
use App\Models\PengajuanBahanAjar;
use App\Filament\Widgets\Traits\HasRoleVisibility;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class RankingChartWidget extends ChartWidget
{
    use HasRoleVisibility;
    
    protected static ?string $heading = 'Ranking Bahan Ajar per Prodi';

    protected static ?int $sort = 2;

    protected function getData(): array
    {
        // Get ranking data grouped by prodi
        $rankingData = AhpResult::select(
            'pengajuan_bahan_ajars.prodi',
            DB::raw('COUNT(*) as total_items'),
            DB::raw('AVG(ahp_results.nilai_akhir) as avg_score')
        )
            ->join('pengajuan_bahan_ajars', 'ahp_results.pengajuan_bahan_ajar_id', '=', 'pengajuan_bahan_ajars.id')
            ->groupBy('pengajuan_bahan_ajars.prodi')
            ->orderBy('avg_score', 'desc')
            ->get();

        $labels = $rankingData->pluck('prodi')->toArray();
        $data = $rankingData->pluck('avg_score')->toArray();
        $totalItems = $rankingData->pluck('total_items')->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Nilai Rata-rata AHP',
                    'data' => $data,
                    'backgroundColor' => [
                        'rgba(59, 130, 246, 0.8)',   // Blue
                        'rgba(16, 185, 129, 0.8)',   // Green
                        'rgba(245, 158, 11, 0.8)',   // Yellow
                        'rgba(239, 68, 68, 0.8)',    // Red
                        'rgba(139, 92, 246, 0.8)',   // Purple
                    ],
                    'borderColor' => [
                        'rgb(59, 130, 246)',
                        'rgb(16, 185, 129)',
                        'rgb(245, 158, 11)',
                        'rgb(239, 68, 68)',
                        'rgb(139, 92, 246)',
                    ],
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'Nilai AHP',
                    ],
                ],
                'x' => [
                    'title' => [
                        'display' => true,
                        'text' => 'Program Studi',
                    ],
                ],
            ],
            'plugins' => [
                'tooltip' => [
                    'callbacks' => [
                        'afterLabel' => function ($context) {
                            $index = $context->dataIndex;
                            $totalItems = $this->getTotalItems()[$index] ?? 0;
                            return "Total Item: {$totalItems}";
                        },
                    ],
                ],
            ],
        ];
    }

    private function getTotalItems(): array
    {
        $rankingData = AhpResult::select(
            'pengajuan_bahan_ajars.prodi',
            DB::raw('COUNT(*) as total_items')
        )
            ->join('pengajuan_bahan_ajars', 'ahp_results.pengajuan_bahan_ajar_id', '=', 'pengajuan_bahan_ajars.id')
            ->groupBy('pengajuan_bahan_ajars.prodi')
            ->orderBy('avg_score', 'desc')
            ->pluck('total_items')
            ->toArray();

        return $rankingData;
    }
    
    protected static function getRequiredRoles(): array
    {
        return ['Super Admin', 'Tim Pengadaan'];
    }
}
