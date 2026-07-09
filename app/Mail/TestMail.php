<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TestMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public string $name) {}

    public function build(): self
    {
        return $this
            ->subject('Test Email - Auriga CTIS')
            ->view('emails.test-mail');
    }
}
