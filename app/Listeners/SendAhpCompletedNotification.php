<?php

namespace App\Listeners;

use App\Events\AhpCalculationCompleted;
use App\Models\User;
use App\Notifications\AhpCompletedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendAhpCompletedNotification implements ShouldQueue
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
    public function handle(AhpCalculationCompleted $event): void
    {
        try {
            $session = $event->session;
            
            // Get users who should be notified
            $usersToNotify = User::whereHas('roles', function ($query) {
                $query->whereIn('name', ['Tim Pengadaan', 'super_admin']);
            })->get();

            // Send notifications
            foreach ($usersToNotify as $user) {
                $user->notify(new AhpCompletedNotification($session));
            }

            Log::info('AHP completion notification sent', [
                'session_id' => $session->id,
                'recipients_count' => $usersToNotify->count()
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send AHP completion notification', [
                'session_id' => $event->session->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}
