<?php

namespace App\Notifications;

use App\Models\PengajuanBahanAjar;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PengajuanSubmittedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $pengajuan;

    /**
     * Create a new notification instance.
     */
    public function __construct(PengajuanBahanAjar $pengajuan)
    {
        $this->pengajuan = $pengajuan;
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
            ->subject('Pengajuan Bahan Ajar Baru - ' . $this->pengajuan->nama_barang)
            ->greeting('Halo ' . $notifiable->name . '!')
            ->line('Pengajuan bahan ajar baru telah diajukan:')
            ->line('**Nama Barang:** ' . $this->pengajuan->nama_barang)
            ->line('**Program Studi:** ' . strtoupper($this->pengajuan->user->prodi))
            ->line('**Pengaju:** ' . $this->pengajuan->user->name)
            ->line('**Total Harga:** Rp ' . number_format($this->pengajuan->total_harga, 0, ',', '.'))
            ->line('**Urgensi Prodi:** ' . ucfirst($this->pengajuan->urgensi_prodi))
            ->action('Lihat Detail', route('filament.admin.resources.pengajuan-bahan-ajars.edit', $this->pengajuan))
            ->line('Silakan review pengajuan ini sesuai dengan kebijakan institusi.')
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
            'type' => 'pengajuan_submitted',
            'title' => 'Pengajuan Bahan Ajar Baru',
            'message' => 'Pengajuan ' . $this->pengajuan->nama_barang . ' dari ' . strtoupper($this->pengajuan->user->prodi) . ' telah diajukan',
            'pengajuan_id' => $this->pengajuan->id,
            'prodi' => $this->pengajuan->user->prodi,
            'pengaju' => $this->pengajuan->user->name,
            'created_at' => now(),
        ];
    }
}
