<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class NewCaseMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public object $case) {}

    public function build(): self
    {
        return $this
            ->subject('[CTIS] Kasus Baru — ' . strip_tags($this->case->title))
            ->view('emails.new-case-notification');
    }

    public function failed(Throwable $exception): void
    {
        Log::error('NewCaseMail failed to send', [
            'case_id' => $this->case->id ?? null,
            'case_number' => $this->case->case_number ?? null,
            'to' => $this->to[0]['address'] ?? null,
            'error' => $exception->getMessage(),
        ]);
    }
}