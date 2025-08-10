<?php

namespace App\Listeners;

use App\Events\PengajuanBahanAjarCreated;
use App\Models\User;
use App\Notifications\PengajuanSubmittedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendPengajuanSubmittedNotification implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(PengajuanBahanAjarCreated $event): void
    {
        try {
            $pengajuan = $event->pengajuan;
            
            // Get users who should be notified
            $usersToNotify = User::whereHas('roles', function ($query) {
                $query->whereIn('name', ['Tim Pengadaan', 'super_admin']);
            })->get();

            // Send notifications
            foreach ($usersToNotify as $user) {
                $user->notify(new PengajuanSubmittedNotification($pengajuan));
            }

            Log::info('Pengajuan notification sent', [
                'pengajuan_id' => $pengajuan->id,
                'recipients_count' => $usersToNotify->count()
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send pengajuan notification', [
                'pengajuan_id' => $event->pengajuan->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}
