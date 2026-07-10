<div>
    @if ($subscribed)
        <p class="text-sm font-semibold {{ $caseId ? 'text-green-700' : 'text-green-300 text-center' }}">
            ✓ {{ $caseId ? 'Anda kini mengikuti kasus ini. Terima kasih!' : 'Berhasil berlangganan. Terima kasih!' }}
        </p>
    @else
        {{-- submit → tampilkan captcha Cloudflare → token valid → kirim via Livewire --}}
        {{-- pakai vanilla JS (bukan Alpine x-on), karena layout ini memuat Alpine
             dua kali (CDN + bundel Livewire) sehingga $wire magic Alpine tidak
             selalu terpasang di instance yang menang --}}
        <form id="tsform-{{ $this->getId() }}" class="flex flex-col gap-2">
            <div class="flex flex-col sm:flex-row gap-2">
                <input type="email" wire:model="email" required placeholder="Alamat email Anda"
                    @if ($caseId)
                        class="flex-1 border border-gray-300 rounded-sm px-3 py-2 text-sm focus:border-[#032A36] focus:ring-[#032A36]"
                    @else
                        class="flex-1 border border-transparent rounded-sm px-3 py-2 text-sm bg-white focus:border-white focus:ring-white"
                    @endif>
                <button type="submit" wire:loading.attr="disabled"
                    @if ($caseId)
                        class="px-4 py-2 bg-[#032A36] text-white text-xs font-bold uppercase tracking-widest rounded-sm hover:bg-[#264c16] transition-colors disabled:opacity-50"
                    @else
                        class="px-5 py-2 bg-white text-[#032A36] text-xs font-bold uppercase tracking-widest rounded-sm hover:bg-gray-200 transition-colors disabled:opacity-50"
                    @endif>
                    {{ $caseId ? 'Ikuti Kasus' : 'Berlangganan' }}
                </button>
            </div>

            {{-- widget muncul saat submit (execution=execute), tersembunyi sebelumnya --}}
            <div wire:ignore id="ts-{{ $this->getId() }}" class="cf-turnstile"
                data-sitekey="{{ config('services.turnstile.site_key') }}"
                data-execution="execute" data-appearance="execute" data-size="normal"
                data-theme="{{ $caseId ? 'light' : 'dark' }}"
                data-callback="turnstileCb{{ $this->getId() }}"></div>

            @error('email')
                <p class="text-xs {{ $caseId ? 'text-red-600' : 'text-red-300' }}">{{ $message }}</p>
            @enderror
            @error('turnstileToken')
                <p class="text-xs {{ $caseId ? 'text-red-600' : 'text-red-300' }}">{{ $message }}</p>
            @enderror
        </form>
    @endif

    @once
        @push('scripts')
            {{-- tanpa SRI: Cloudflare merotasi isi script ini di URL yang sama --}}
            <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
        @endpush
    @endonce

    @script
        <script>
            const formEl = document.getElementById('tsform-{{ $this->getId() }}');

            formEl?.addEventListener('submit', (e) => {
                e.preventDefault();
                if ($wire.turnstileToken) {
                    $wire.subscribe();
                } else if (window.turnstile) {
                    turnstile.execute('#ts-{{ $this->getId() }}');
                } else {
                    $wire.subscribe();
                }
            });

            // dipanggil Turnstile setelah captcha lolos → langsung kirim form
            window['turnstileCb{{ $this->getId() }}'] = (token) => {
                $wire.set('turnstileToken', token, false);
                $wire.subscribe();
            };
            // ponytail: reset() tanpa widgetId cukup — hanya ada satu widget per halaman
            $wire.on('turnstile-reset', () => window.turnstile && turnstile.reset());
        </script>
    @endscript
</div>
