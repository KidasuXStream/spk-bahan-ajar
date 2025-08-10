<?php

namespace App\Filament\Widgets;

use App\Models\User;
use App\Models\PengajuanBahanAjar;
use App\Models\Kriteria;
use App\Models\AhpSession;
use App\Models\AhpResult;
use App\Filament\Widgets\Traits\HasRoleVisibility;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SuperAdminOverviewWidget extends BaseWidget
{
    use HasRoleVisibility;

    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        // Get user statistics
        $totalUsers = User::count();
        $kaprodiUsers = User::whereHas('roles', function ($query) {
            $query->where('name', 'Kaprodi');
        })->count();
        $timPengadaanUsers = User::whereHas('roles', function ($query) {
            $query->where('name', 'Tim Pengadaan');
        })->count();

        // Get system data
        $totalPengajuan = PengajuanBahanAjar::count();
        $totalKriteria = Kriteria::count();
        $totalAhpSessions = AhpSession::count();
        $activeAhpSessions = AhpSession::active()->count();
        $totalAhpResults = AhpResult::count();

        // Get prodi statistics
        $prodiList = PengajuanBahanAjar::distinct('prodi')->pluck('prodi')->filter()->count();

        return [
            Stat::make('Total Users', $totalUsers)
                ->description('Semua user dalam sistem')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),

            Stat::make('Kaprodi', $kaprodiUsers)
                ->description('User dengan role Kaprodi')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('info'),

            Stat::make('Tim Pengadaan', $timPengadaanUsers)
                ->description('User dengan role Tim Pengadaan')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('success'),

            Stat::make('Total Pengajuan', $totalPengajuan)
                ->description('Semua pengajuan bahan ajar')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('warning'),

            Stat::make('Program Studi', $prodiList)
                ->description('Jumlah prodi yang mengajukan')
                ->descriptionIcon('heroicon-m-building-office')
                ->color('primary'),

            Stat::make('Kriteria AHP', $totalKriteria)
                ->description('Kriteria untuk perhitungan AHP')
                ->descriptionIcon('heroicon-m-list-bullet')
                ->color('info'),

            Stat::make('Sesi AHP', $totalAhpSessions)
                ->description('Total sesi AHP yang dibuat')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('success'),

            Stat::make('Session Aktif', $activeAhpSessions)
                ->description('Session untuk semester berjalan')
                ->descriptionIcon('heroicon-m-play-circle')
                ->color('success'),

            Stat::make('Hasil AHP', $totalAhpResults)
                ->description('Data hasil perhitungan AHP')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('warning'),
        ];
    }

    protected function getColumns(): int
    {
        return 4;
    }

    protected static function getRequiredRoles(): array
    {
        return ['Super Admin'];
    }
}
