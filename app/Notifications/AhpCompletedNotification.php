<?php

namespace App\Notifications;

use App\Models\AhpSession;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AhpCompletedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $session;

    /**
     * Create a new notification instance.
     */
    public function __construct(AhpSession $session)
    {
        $this->session = $session;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Perhitungan AHP Selesai - ' . $this->session->tahun_ajaran . ' ' . $this->session->semester)
            ->greeting('Halo ' . $notifiable->name . '!')
            ->line('Perhitungan AHP untuk periode berikut telah selesai:')
            ->line('**Tahun Ajaran:** ' . $this->session->tahun_ajaran)
            ->line('**Semester:** ' . $this->session->semester)
            ->line('**Tanggal Selesai:** ' . $this->session->updated_at->format('d/m/Y H:i:s'))
            ->line('**Status:** Selesai dan siap untuk review')
            ->action('Lihat Hasil AHP', route('filament.admin.resources.ahp-sessions.edit', $this->session))
            ->line('Silakan review hasil perangkingan dan lakukan export sesuai kebutuhan.')
            ->salutation('Terima kasih, Tim SPK Bahan Ajar');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'ahp_completed',
            'title' => 'Perhitungan AHP Selesai',
            'message' => 'AHP untuk ' . $this->session->tahun_ajaran . ' ' . $this->session->semester . ' telah selesai',
            'session_id' => $this->session->id,
            'tahun_ajaran' => $this->session->tahun_ajaran,
            'semester' => $this->session->semester,
            'created_at' => now(),
        ];
    }
}