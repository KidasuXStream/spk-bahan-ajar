<?php

namespace App\Events;

use App\Models\AhpSession;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AhpCalculationCompleted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $session;

    /**
     * Create a new event instance.
     */
    public function __construct(AhpSession $session)
    {
        $this->session = $session;
    }
}
