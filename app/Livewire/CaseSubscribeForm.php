<?php

namespace App\Livewire;

use App\Models\CaseSubscription;
use Illuminate\Support\Facades\Cookie;
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

    public function mount(): void
    {
        // Cookie menyimpan daftar email yang pernah dipakai dari browser ini.
        // Status "sudah mengikuti" dicek ke DB — bukan sekadar cookie — supaya
        // baris DB yang dihapus (admin/testing) membuat form muncul kembali, dan
        // pesan "Anda kini mengikuti kasus ini" hanya muncul pada case yang
        // benar-benar memiliki baris langganan untuk email browser ini.
        $emails = $this->subscribedEmails();

        if ($this->caseId === null) {
            // form "semua kasus": sembunyi hanya jika email ini sudah berlangganan
            // semua kasus (baris case_id NULL).
            $this->subscribed = !empty($emails)
                && CaseSubscription::whereIn('email', $emails)
                    ->whereNull('case_id')->exists();
        } else {
            // form per-case: sembunyi hanya jika ada baris per-case untuk case ini.
            // Catatan: baris "semua kasus" (case_id NULL) TIDAK menyembunyikan form
            // per-case — visitor yang sudah subscribe all-case tetap boleh mengikuti
            // kasus tertentu secara terpisah.
            $this->subscribed = !empty($emails)
                && CaseSubscription::whereIn('email', $emails)
                    ->where('case_id', $this->caseId)->exists();
        }
    }

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

        // Catat email ke cookie agar kunjungan berikutnya tahu visitor ini siapa.
        // Status tetap dicek ke DB di mount(), jadi cookie hanya penanda email.
        $emails = $this->subscribedEmails();
        if (!in_array($this->email, $emails, true)) {
            $emails[] = $this->email;
        }
        Cookie::queue('subscribed_emails', json_encode(array_values($emails)), 60 * 24 * 365); // 1 tahun

        $this->subscribed = true;
    }

    public function render()
    {
        return view('livewire.case-subscribe-form');
    }

    /**
     * Daftar email yang pernah dipakai untuk berlangganan dari browser ini.
     */
    private function subscribedEmails(): array
    {
        $emails = json_decode(Cookie::get('subscribed_emails', '[]'), true) ?? [];

        return array_values(array_filter(array_map('strval', $emails)));
    }
}