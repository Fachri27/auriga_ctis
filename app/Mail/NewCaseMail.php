<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewCaseMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public object $case) {}

    public function build(): self
    {
        return $this
            ->subject('🔔 Kasus Baru: ' . strip_tags($this->case->title))
            ->view('emails.new-case-notification');
    }
}