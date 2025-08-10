<?php

namespace App\Providers;

use App\Models\AhpSession;
use App\Models\PengajuanBahanAjar;
use App\Observers\AhpSessionObserver;
use App\Observers\PengajuanBahanAjarObserver;
use Illuminate\Support\ServiceProvider;
use Filament\Notifications\Livewire\DatabaseNotifications;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register model observers
        PengajuanBahanAjar::observe(PengajuanBahanAjarObserver::class);
        AhpSession::observe(AhpSessionObserver::class);
    }
}
