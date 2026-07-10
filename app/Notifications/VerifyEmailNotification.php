<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail as BaseVerifyEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Log;
use Throwable;

class VerifyEmailNotification extends BaseVerifyEmail implements ShouldQueue
{
    use Queueable;

    public function failed(Throwable $exception): void
    {
        Log::error('VerifyEmailNotification failed to send', [
            'error' => $exception->getMessage(),
        ]);
    }

    /**
     * Build the mail message
     */
    protected function buildMailMessage($url)
    {
        return (new MailMessage)
            ->subject('Verifikasi Email Anda - Auriga CTIS')
            ->greeting('👋 Halo!')
            ->line('Terima kasih telah mendaftar di **Auriga CTIS**. Kami senang menyambut Anda!')
            ->line('')
            ->line('Untuk menyelesaikan proses pendaftaran dan mengaktifkan akun Anda, silakan verifikasi alamat email dengan mengklik tombol di bawah:')
            ->action('✓ Verifikasi Email Saya', $url)
            ->line('')
            ->line('**Atau salin dan tempel URL di bawah ke browser Anda:**')
            ->line($url)
            ->line('')
            ->line('---')
            ->line('')
            ->line('**⏰ Informasi Penting:**')
            ->line('• Link verifikasi ini akan berlaku selama 1 jam')
            ->line('• Jika tautan tidak berfungsi, hubungi tim support kami')
            ->line('')
            ->line('**⚠️ Keamanan:**')
            ->line('Jika Anda tidak mendaftar akun ini, silakan abaikan email ini. Akun Anda tidak akan aktif sampai email diverifikasi.')
            ->line('')
            ->line('Pertanyaan? Email kami di: support@example.com')
            ->salutation('Salam hangat,
Tim Auriga CTIS');
    }
}
