<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CaseStatusUpdatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public object $case,
        public string $oldStatus,
        public string $newStatus,
    ) {}

    public function build(): self
    {
        $label = strip_tags($this->case->title ?? $this->case->case_number ?? 'Kasus');

        return $this
            ->subject('[CTIS] Pembaruan Status — ' . $label)
            ->view('emails.case-status-updated');
    }
}