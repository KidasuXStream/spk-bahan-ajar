<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Filament\Widgets\KaprodiOverviewWidget;
use App\Filament\Widgets\TimPengadaanOverviewWidget;
use App\Filament\Widgets\SuperAdminOverviewWidget;
use App\Filament\Widgets\LatestPengajuanWidget;
use App\Filament\Widgets\RankingChartWidget;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class TestWidgets extends Command
{
    protected $signature = 'test:widgets';
    protected $description = 'Test if all dashboard widgets can be instantiated without errors';

    public function handle()
    {
        $this->info('=== Testing Dashboard Widgets ===');

        // Test widget instantiation
        $this->info('1. Testing Widget Instantiation:');

        try {
            $kaprodiWidget = new KaprodiOverviewWidget();
            $this->line('   ✅ KaprodiOverviewWidget: OK');
        } catch (\Exception $e) {
            $this->error("   ❌ KaprodiOverviewWidget: " . $e->getMessage());
        }

        try {
            $timPengadaanWidget = new TimPengadaanOverviewWidget();
            $this->line('   ✅ TimPengadaanOverviewWidget: OK');
        } catch (\Exception $e) {
            $this->error("   ❌ TimPengadaanOverviewWidget: " . $e->getMessage());
        }

        try {
            $superAdminWidget = new SuperAdminOverviewWidget();
            $this->line('   ✅ SuperAdminOverviewWidget: OK');
        } catch (\Exception $e) {
            $this->error("   ❌ SuperAdminOverviewWidget: " . $e->getMessage());
        }

        try {
            $latestPengajuanWidget = new LatestPengajuanWidget();
            $this->line('   ✅ LatestPengajuanWidget: OK');
        } catch (\Exception $e) {
            $this->error("   ❌ LatestPengajuanWidget: " . $e->getMessage());
        }

        try {
            $rankingChartWidget = new RankingChartWidget();
            $this->line('   ✅ RankingChartWidget: OK');
        } catch (\Exception $e) {
            $this->error("   ❌ RankingChartWidget: " . $e->getMessage());
        }

        // Test dashboard page
        $this->info('3. Testing Dashboard Page:');
        try {
            $dashboard = new \App\Filament\Pages\Dashboard();
            $this->line('   ✅ Dashboard page: OK');

            // Test with authenticated user
            $this->line('   Testing with authenticated users:');

            // Test with Kaprodi user
            $kaprodiUser = User::where('email', 'kaprodi@spk.com')->first();
            if ($kaprodiUser) {
                \Illuminate\Support\Facades\Auth::login($kaprodiUser);
                $headerWidgets = $dashboard->getHeaderWidgets();
                $footerWidgets = $dashboard->getFooterWidgets();
                $this->line("   Kaprodi - Header widgets: " . count($headerWidgets));
                $this->line("   Kaprodi - Footer widgets: " . count($footerWidgets));
            }

            // Test with Tim Pengadaan user
            $timPengadaanUser = User::where('email', 'pengadaan@spk.com')->first();
            if ($timPengadaanUser) {
                \Illuminate\Support\Facades\Auth::login($timPengadaanUser);
                $headerWidgets = $dashboard->getHeaderWidgets();
                $footerWidgets = $dashboard->getFooterWidgets();
                $this->line("   Tim Pengadaan - Header widgets: " . count($headerWidgets));
                $this->line("   Tim Pengadaan - Footer widgets: " . count($footerWidgets));
            }
        } catch (\Exception $e) {
            $this->error("   ❌ Dashboard page: " . $e->getMessage());
        }

        // Test role checking more thoroughly
        $this->info('4. Testing Role Checking in Detail:');

        $kaprodiUser = User::where('email', 'kaprodi@spk.com')->first();
        if ($kaprodiUser) {
            $this->line("   Testing Kaprodi user: {$kaprodiUser->name}");
            $this->line("   Has role 'Kaprodi': " . ($kaprodiUser->hasRole('Kaprodi') ? 'Yes' : 'No'));
            $this->line("   Has role 'Tim Pengadaan': " . ($kaprodiUser->hasRole('Tim Pengadaan') ? 'Yes' : 'No'));

            // Test widget visibility with authenticated user
            \Illuminate\Support\Facades\Auth::login($kaprodiUser);
            $this->line("   KaprodiOverviewWidget::canView() (authenticated): " . (KaprodiOverviewWidget::canView() ? 'Yes' : 'No'));
        }

        $timPengadaanUser = User::where('email', 'pengadaan@spk.com')->first();
        if ($timPengadaanUser) {
            $this->line("   Testing Tim Pengadaan user: {$timPengadaanUser->name}");
            $this->line("   Has role 'Tim Pengadaan': " . ($timPengadaanUser->hasRole('Tim Pengadaan') ? 'Yes' : 'No'));
            $this->line("   Has role 'Kaprodi': " . ($timPengadaanUser->hasRole('Kaprodi') ? 'Yes' : 'No'));

            // Test widget visibility with authenticated user
            \Illuminate\Support\Facades\Auth::login($timPengadaanUser);
            $this->line("   TimPengadaanOverviewWidget::canView() (authenticated): " . (TimPengadaanOverviewWidget::canView() ? 'Yes' : 'No'));
        }

        $this->info('=== Widget Testing Complete ===');
    }
}
