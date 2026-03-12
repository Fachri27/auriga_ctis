<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Contracts\Queue\ShouldQueue; // ← yang benar ada di sini
use App\Mail\NewCaseMail;


class NewCaseNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $case;

    public function __construct($case)
    {
        $this->case = $case;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return new NewCaseMail($this->case);
    }
}
