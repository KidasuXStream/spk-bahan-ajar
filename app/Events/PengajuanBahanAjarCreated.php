<?php

namespace App\Events;

use App\Models\PengajuanBahanAjar;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PengajuanBahanAjarCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $pengajuan;

    /**
     * Create a new event instance.
     */
    public function __construct(PengajuanBahanAjar $pengajuan)
    {
        $this->pengajuan = $pengajuan;
    }
}
