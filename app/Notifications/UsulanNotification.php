<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
// Kalau mau support queue worker, bisa aktifkan:
// use Illuminate\Contracts\Queue\ShouldQueue;

class UsulanNotification extends Notification // implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $usulanId,
        public string $judul,       // Judul singkat
        public string $pesan,       // Pesan detail
        public string $statusBaru,  // menunggu_kepala_unit|menunggu_katimker|menunggu_kabid|disetujui|ditolak
        public ?int $byUserId = null,
        public ?string $byUserName = null,
        public ?string $url = null, // opsional
    ) {}

    /**
     * Tentukan channel pengiriman (database/email/dll)
     */
    public function via($notifiable): array
    {
        return ['database'];
    }

    /**
     * Payload yang disimpan ke tabel notifications
     */
    public function toDatabase($notifiable): array
    {
        return [
            'usulan_id'    => $this->usulanId,
            'title'        => $this->judul,
            'message'      => $this->pesan,
            'status_baru'  => $this->statusBaru,
            'by_user_id'   => $this->byUserId,
            'by_user_name' => $this->byUserName,
            'url'          => $this->url ?? route('persetujuan.index', ['tab' => 'waiting']),
        ];
    }
}
