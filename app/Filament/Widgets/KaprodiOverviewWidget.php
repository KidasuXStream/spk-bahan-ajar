<?php

namespace App\Filament\Widgets;

use App\Models\PengajuanBahanAjar;
use App\Models\AhpResult;
use App\Filament\Widgets\Traits\HasRoleVisibility;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class KaprodiOverviewWidget extends BaseWidget
{
    use HasRoleVisibility;
    
    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $user = Auth::user();
        $prodi = $user->prodi;

        // Get pengajuan data for this prodi
        $pengajuanCount = PengajuanBahanAjar::where('prodi', $prodi)->count();
        $pengajuanPending = PengajuanBahanAjar::where('prodi', $prodi)
            ->whereNull('urgensi_tim_pengadaan')
            ->count();
        $pengajuanProcessed = PengajuanBahanAjar::where('prodi', $prodi)
            ->whereNotNull('urgensi_tim_pengadaan')
            ->count();

        // Get ranking data if available
        $hasRanking = AhpResult::whereHas('pengajuan', function ($query) use ($prodi) {
            $query->where('prodi', $prodi);
        })->exists();

        return [
            Stat::make('Total Pengajuan Prodi', $pengajuanCount)
                ->description('Jumlah pengajuan bahan ajar')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('primary'),

            Stat::make('Menunggu Tim Pengadaan', $pengajuanPending)
                ->description('Belum diproses tim pengadaan')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('Sudah Diproses', $pengajuanProcessed)
                ->description('Sudah ada urgensi tim pengadaan')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Status Ranking', $hasRanking ? 'Tersedia' : 'Belum Tersedia')
                ->description('Hasil perangkingan AHP')
                ->descriptionIcon($hasRanking ? 'heroicon-m-chart-bar' : 'heroicon-m-x-circle')
                ->color($hasRanking ? 'success' : 'danger'),
        ];
    }

    protected function getColumns(): int
    {
        return 2;
    }
    
    protected static function getRequiredRoles(): array
    {
        return ['Kaprodi'];
    }
}