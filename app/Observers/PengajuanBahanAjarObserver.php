<?php

namespace App\Observers;

use App\Events\PengajuanBahanAjarCreated;
use App\Models\PengajuanBahanAjar;
use Illuminate\Support\Facades\Log;

class PengajuanBahanAjarObserver
{
    /**
     * Handle the PengajuanBahanAjar "created" event.
     */
    public function created(PengajuanBahanAjar $pengajuanBahanAjar): void
    {
        try {
            // Fire event for new pengajuan
            event(new PengajuanBahanAjarCreated($pengajuanBahanAjar));
            
            Log::info('PengajuanBahanAjar created event fired', [
                'pengajuan_id' => $pengajuanBahanAjar->id,
                'user_id' => $pengajuanBahanAjar->user_id
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fire PengajuanBahanAjar created event', [
                'pengajuan_id' => $pengajuanBahanAjar->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Handle the PengajuanBahanAjar "updated" event.
     */
    public function updated(PengajuanBahanAjar $pengajuanBahanAjar): void
    {
        // Log updates for debugging
        Log::info('PengajuanBahanAjar updated', [
            'pengajuan_id' => $pengajuanBahanAjar->id,
            'changes' => $pengajuanBahanAjar->getChanges()
        ]);
    }

    /**
     * Handle the PengajuanBahanAjar "deleted" event.
     */
    public function deleted(PengajuanBahanAjar $pengajuanBahanAjar): void
    {
        Log::info('PengajuanBahanAjar deleted', [
            'pengajuan_id' => $pengajuanBahanAjar->id
        ]);
    }

    /**
     * Handle the PengajuanBahanAjar "restored" event.
     */
    public function restored(PengajuanBahanAjar $pengajuanBahanAjar): void
    {
        Log::info('PengajuanBahanAjar restored', [
            'pengajuan_id' => $pengajuanBahanAjar->id
        ]);
    }

    /**
     * Handle the PengajuanBahanAjar "force deleted" event.
     */
    public function forceDeleted(PengajuanBahanAjar $pengajuanBahanAjar): void
    {
        Log::info('PengajuanBahanAjar force deleted', [
            'pengajuan_id' => $pengajuanBahanAjar->id
        ]);
    }
}
