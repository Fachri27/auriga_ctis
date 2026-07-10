<?php

namespace App\Livewire;

use App\Models\CaseSubscription;
use Illuminate\Support\Facades\Http;
use Livewire\Attributes\Locked;
use Livewire\Component;

class CaseSubscribeForm extends Component
{
    // null = berlangganan kasus terbaru, terisi = ikuti kasus tertentu
    #[Locked]
    public ?int $caseId = null;

    public string $email = '';

    public ?string $turnstileToken = null;

    public bool $subscribed = false;

    public function subscribe()
    {
        $this->validate([
            'email' => ['required', 'email', 'max:255'],
            'turnstileToken' => ['required', 'string'],
        ], [
            'turnstileToken.required' => 'Verifikasi keamanan belum selesai, silakan coba lagi.',
        ]);

        $verify = Http::asForm()->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
            'secret' => config('services.turnstile.secret_key'),
            'response' => $this->turnstileToken,
            'remoteip' => request()->ip(),
        ])->json();

        // token sekali pakai — widget harus buat token baru untuk percobaan berikutnya
        $this->turnstileToken = null;
        $this->dispatch('turnstile-reset');

        if (!($verify['success'] ?? false)) {
            $this->addError('email', 'Verifikasi keamanan gagal, silakan coba lagi.');
            return;
        }

        CaseSubscription::firstOrCreate([
            'email' => $this->email,
            'case_id' => $this->caseId,
        ]);

        $this->subscribed = true;
    }

    public function render()
    {
        return view('livewire.case-subscribe-form');
    }
}
