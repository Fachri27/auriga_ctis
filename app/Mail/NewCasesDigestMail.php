<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Satu email ringkasan berisi beberapa kasus yang dipublikasikan sekaligus
 * (publikasi massal dari daftar kasus).
 */
class NewCasesDigestMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  list<object>  $cases  tiap item: case_number, title, description
     */
    public function __construct(public array $cases) {}

    public function build(): self
    {
        return $this
            ->subject('[CTIS] '.count($this->cases).' Kasus Baru Dipublikasikan')
            ->view('emails.new-cases-digest');
    }

    public function failed(Throwable $exception): void
    {
        Log::error('NewCasesDigestMail failed to send', [
            'cases' => array_column($this->cases, 'case_number'),
            'to' => $this->to[0]['address'] ?? null,
            'error' => $exception->getMessage(),
        ]);
    }
}
