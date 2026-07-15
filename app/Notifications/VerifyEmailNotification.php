<?php

namespace App\Notifications;

use App\Mail\VerifyEmailMail;
use Illuminate\Auth\Notifications\VerifyEmail as BaseVerifyEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
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
     * Render email verifikasi via Mailable (view emails.verify-email) supaya
     * tampilannya konsisten dengan hero publik. URL bertanda tetap dihasilkan
     * oleh verificationUrl() bawaan Laravel — tidak berubah.
     */
    public function toMail($notifiable)
    {
        $url = $this->verificationUrl($notifiable);

        return (new VerifyEmailMail($notifiable, $url))->to($notifiable->email);
    }
}