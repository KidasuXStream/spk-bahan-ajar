<?php

namespace App\Filament\Widgets;

use App\Models\PengajuanBahanAjar;
use App\Models\Kriteria;
use App\Models\AhpSession;
use App\Models\AhpResult;
use App\Filament\Widgets\Traits\HasRoleVisibility;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TimPengadaanOverviewWidget extends BaseWidget
{
    use HasRoleVisibility;

    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        // Get kriteria data
        $kriteriaCount = Kriteria::count();

        // Get AHP sessions
        $ahpSessionCount = AhpSession::count();
        $activeAhpSessionCount = AhpSession::active()->count();
        $activeAhpSession = AhpSession::active()->latest()->first();

        // Get pengajuan data
        $pengajuanCount = PengajuanBahanAjar::count();
        $pengajuanPending = PengajuanBahanAjar::whereNull('urgensi_tim_pengadaan')->count();
        $pengajuanProcessed = PengajuanBahanAjar::whereNotNull('urgensi_tim_pengadaan')->count();

        // Get AHP results
        $ahpResultCount = AhpResult::count();

        return [
            Stat::make('Kriteria Aktif', $kriteriaCount)
                ->description('Kriteria yang digunakan untuk AHP')
                ->descriptionIcon('heroicon-m-list-bullet')
                ->color('primary'),

            Stat::make('Sesi AHP', $ahpSessionCount)
                ->description('Total sesi AHP yang dibuat')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('info'),

            Stat::make('Session Aktif', $activeAhpSessionCount)
                ->description('Session untuk semester berjalan')
                ->descriptionIcon('heroicon-m-play-circle')
                ->color('success'),

            Stat::make('Total Pengajuan', $pengajuanCount)
                ->description('Semua pengajuan dari semua prodi')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('success'),

            Stat::make('Menunggu Urgensi', $pengajuanPending)
                ->description('Belum diberi urgensi tim pengadaan')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('Sudah Diproses', $pengajuanProcessed)
                ->description('Sudah diberi urgensi tim pengadaan')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Hasil AHP', $ahpResultCount)
                ->description('Data hasil perhitungan AHP')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('info'),
        ];
    }

    protected function getColumns(): int
    {
        return 3;
    }

    protected static function getRequiredRoles(): array
    {
        return ['Tim Pengadaan'];
    }
}
