<?php

namespace App\Observers;

use App\Events\AhpCalculationCompleted;
use App\Models\AhpSession;
use Illuminate\Support\Facades\Log;

class AhpSessionObserver
{
    /**
     * Handle the AhpSession "created" event.
     */
    public function created(AhpSession $ahpSession): void
    {
        Log::info('AhpSession created', [
            'session_id' => $ahpSession->id,
            'tahun_ajaran' => $ahpSession->tahun_ajaran,
            'semester' => $ahpSession->semester
        ]);
    }

    /**
     * Handle the AhpSession "updated" event.
     */
    public function updated(AhpSession $ahpSession): void
    {
        // Check if AHP calculation was completed
        if ($ahpSession->wasChanged('status') && $ahpSession->status === 'completed') {
            try {
                // Fire event for completed AHP calculation
                event(new AhpCalculationCompleted($ahpSession));
                
                Log::info('AHP calculation completed event fired', [
                    'session_id' => $ahpSession->id,
                    'tahun_ajaran' => $ahpSession->tahun_ajaran,
                    'semester' => $ahpSession->semester
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to fire AHP calculation completed event', [
                    'session_id' => $ahpSession->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Log other updates for debugging
        Log::info('AhpSession updated', [
            'session_id' => $ahpSession->id,
            'changes' => $ahpSession->getChanges()
        ]);
    }

    /**
     * Handle the AhpSession "deleted" event.
     */
    public function deleted(AhpSession $ahpSession): void
    {
        Log::info('AhpSession deleted', [
            'session_id' => $ahpSession->id
        ]);
    }

    /**
     * Handle the AhpSession "restored" event.
     */
    public function restored(AhpSession $ahpSession): void
    {
        Log::info('AhpSession restored', [
            'session_id' => $ahpSession->id
        ]);
    }

    /**
     * Handle the AhpSession "force deleted" event.
     */
    public function forceDeleted(AhpSession $ahpSession): void
    {
        Log::info('AhpSession force deleted', [
            'session_id' => $ahpSession->id
        ]);
    }
}
