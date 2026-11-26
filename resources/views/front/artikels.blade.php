@extends('layouts.main')

@php
    $pageTitle = __('Artikel & Berita Lingkungan') . ' — greendefender';
    $pageDescription = __('Baca artikel dan berita terbaru seputar penegakan hukum lingkungan hidup di Indonesia.');
@endphp

@section('content')
    <div id="artikels-page" class="mt-16">
        <x-front.page-hero :label="__('Artikel')" :title="__('Artikel & Berita Lingkungan')"
            :description="__('Halaman artikel dan berita pada platform ini dapat memuat konten yang disematkan dari sumber lain berupa artikel berita, video, gambar atau konten lain dari situs website pihak ketiga (eksternal).')" />

        <section class="bg-[#F5F7F1] py-12 md:py-16">
        <div class="max-w-7xl mx-auto px-4">

            {{-- Filter jenis (analisis & berita tampil dalam satu daftar) dan kotak cari. Keduanya mengganti
                 #artikel-results tanpa reload (skrip di bawah); tanpa JavaScript tetap jalan sebagai tautan & form biasa. --}}
            @php
                $filters = [null => __('Semua'), 'analisis' => __('Analisis Auriga'), 'berita' => __('Berita')];
                $chipOn = 'bg-[#0B1E07] text-white';
                $chipOff = 'bg-white border-[#E2E6DA] text-[#0B1E07] hover:border-[#0B1E07]';
            @endphp
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
                <nav aria-label="{{ __('Jenis artikel') }}" class="flex flex-wrap gap-2">
                    @foreach ($filters as $key => $label)
                        <a href="{{ route('public.artikel.list', array_filter(['locale' => $locale, 'jenis' => $key, 'q' => $q])) }}"
                            data-artikel-jenis="{{ $key }}" data-on="{{ $chipOn }}" data-off="{{ $chipOff }}"
                            @if ($jenis === ($key ?: null)) aria-current="page" style="border-color:#0B1E07" @endif
                            class="px-4 py-2 rounded-full border font-data uppercase tracking-widest text-[10px] transition-colors
                                {{ $jenis === ($key ?: null) ? $chipOn : $chipOff }}">
                            {{ $label }}
                        </a>
                    @endforeach
                </nav>

                <form method="GET" action="{{ route('public.artikel.list', ['locale' => $locale]) }}" role="search" id="artikel-search" class="gd-search">
                    @if ($jenis)
                        <input type="hidden" name="jenis" value="{{ $jenis }}">
                    @endif
                    <svg class="gd-search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                    <input type="search" name="q" value="{{ $q }}" maxlength="100" autocomplete="off"
                        placeholder="{{ __('Cari judul, isu, lokasi, atau media…') }}" aria-label="{{ __('Cari artikel dan berita') }}">
                    <button type="submit">{{ __('Cari') }}</button>
                </form>
            </div>

            <section id="artikel-results" aria-label="{{ __('Daftar artikel') }}">
                @if ($q !== '')
                    <p class="mb-5 text-sm text-[#6b7268]" role="status">
                        {!! __(':count hasil untuk :query', ['count' => '<strong class="text-[#0B1E07]">'.number_format($artikels->total()).'</strong>', 'query' => '<strong class="text-[#0B1E07]">“'.e($q).'”</strong>']) !!}
                    </p>
                @endif

                <div id="artikel-list">
                @if ($artikels->isEmpty())
                    <div class="text-sm text-[#6b7268] bg-white border border-[#E2E6DA] rounded-sm p-6">
                        @if ($q !== '')
                            <p>{{ __('Tidak ada artikel atau berita yang cocok. Coba kata lain yang lebih umum, misalnya nama daerah atau isu (tambang, karhutla, satwa).') }}</p>
                            <a href="{{ route('public.artikel.list', array_filter(['locale' => $locale, 'jenis' => $jenis])) }}" data-artikel-clear
                                class="inline-block mt-3 font-data text-[11px] uppercase tracking-widest text-[#2F6C14] hover:underline">{{ __('Hapus pencarian') }}</a>
                        @else
                            <p>{{ ['analisis' => __('Belum ada analisis yang diterbitkan.'), 'berita' => __('Belum ada berita.')][$jenis] ?? __('Belum ada artikel.') }}</p>
                        @endif
                    </div>
                @else
                    <div id="artikel-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                        @include('front.components.artikel-cards')
                    </div>

                    {{-- Infinite scroll: 20 artikel per muatan. Saat pengguna sampai di bawah, tampil indikator
                         memuat lalu artikel berikutnya muncul. Tanpa JavaScript, tautan membuka halaman berikutnya. --}}
                    @if ($artikels->hasMorePages())
                        <div id="artikel-more" data-next="{{ $artikels->nextPageUrl() }}" class="mt-8 flex justify-center py-6">
                            <a href="{{ $artikels->nextPageUrl() }}#artikel-list" data-more-button
                                class="inline-flex items-center gap-2 px-6 py-3 rounded-sm bg-[#0B1E07] hover:bg-[#264c16] text-white font-data uppercase tracking-[0.2em] text-xs transition-colors">
                                {{ __('Muat lebih banyak') }}
                            </a>
                            <div data-more-loader role="status" class="hidden items-center gap-3 text-sm text-[#264c16]">
                                <svg class="animate-spin w-6 h-6" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4Z"></path>
                                </svg>
                                <span class="font-data uppercase tracking-[0.2em] text-xs">{{ __('Memuat artikel…') }}</span>
                            </div>
                        </div>
                    @endif
                @endif
                </div>
            </section>
        </div>
        </section>
    </div>
@endsection

@push('scripts')
    @include('front.components.page-swap-script')
    @php
        // @json memecah argumennya pada koma, jadi array teks disusun di sini dulu.
        $scrollI18n = ['done' => __('Semua artikel sudah ditampilkan.'), 'failed' => __('Gagal memuat. Coba lagi'), 'more' => __('Muat lebih banyak')];
    @endphp
    <script>
        // Infinite scroll di dalam #artikel-results. Dipasang ulang setiap kali hasil diganti (cari/filter);
        // mengembalikan fungsi pembersih.
        function initArtikelMore(root) {
            const I18N = @json($scrollI18n);
            const more = root && root.querySelector('#artikel-more');
            const grid = root && root.querySelector('#artikel-grid');
            if (!more || !grid || !('IntersectionObserver' in window)) return () => {};

            const button = more.querySelector('[data-more-button]');
            const loader = more.querySelector('[data-more-loader]');
            const wait = (ms) => new Promise((resolve) => setTimeout(resolve, ms));
            let next = more.dataset.next;
            let loading = false;
            let stopped = false;

            const show = (element, visible) => {
                element.classList.toggle('hidden', !visible);
                element.classList.toggle(element === loader ? 'flex' : 'inline-flex', visible);
            };

            const load = async () => {
                if (loading || !next || stopped) return;
                loading = true;
                show(button, false);
                show(loader, true);

                try {
                    const url = new URL(next, window.location.href);
                    url.searchParams.set('partial', '1');

                    // Indikator memuat tampil sebentar agar pergantian muatan terlihat jelas.
                    const [response] = await Promise.all([fetch(url, { headers: { Accept: 'application/json' } }), wait(700)]);
                    if (!response.ok) throw new Error(response.status);

                    const data = await response.json();
                    if (stopped) return;
                    grid.insertAdjacentHTML('beforeend', data.html);
                    next = data.next;
                } catch (error) {
                    // Gagal memuat: tidak diulang otomatis, tombol bisa diklik untuk mencoba lagi.
                    observer.disconnect();
                    show(loader, false);
                    button.textContent = I18N.failed;
                    show(button, true);
                    loading = false;
                    return;
                }

                show(loader, false);
                loading = false;

                if (!next) {
                    observer.disconnect();
                    more.innerHTML = '<p class="text-sm text-[#6b7268]"></p>';
                    more.firstChild.textContent = I18N.done;
                    return;
                }

                // Amati ulang: bila penanda masih terlihat (layar tinggi), muatan berikutnya langsung diminta.
                observer.unobserve(more);
                observer.observe(more);
            };

            // Muatan berikutnya baru diminta saat pengguna sampai di bawah daftar.
            const observer = new IntersectionObserver((entries) => entries.some((entry) => entry.isIntersecting) && load());

            show(button, false);
            observer.observe(more);

            button.addEventListener('click', (event) => {
                event.preventDefault();
                button.textContent = I18N.more;
                observer.observe(more);
                load();
            });

            return () => {
                stopped = true;
                observer.disconnect();
            };
        }

        // Cari & filter jenis tanpa reload: #artikel-results diambil ulang dari server lalu ditukar, alamat halaman
        // ikut diperbarui (bisa dibagikan, tombol Back jalan). Gagal → buka alamatnya seperti biasa.
        (() => {
            const ROOT = 'artikel-results';
            const form = document.getElementById('artikel-search');
            const input = form && form.querySelector('input[name="q"]');
            let cleanup = initArtikelMore(document.getElementById(ROOT));
            if (!form || !input || !window.gdSwap) return;

            const chips = [...document.querySelectorAll('[data-artikel-jenis]')];
            let jenis = new URL(location.href).searchParams.get('jenis') || '';
            let shown = location.search;
            let typing = false; // pencarian sambil mengetik: hanya entri riwayat pertama yang ditambah
            let timer = null;

            const urlFor = (q) => {
                const url = new URL(form.action, location.href);
                if (jenis) url.searchParams.set('jenis', jenis);
                if (q) url.searchParams.set('q', q);
                return url.toString();
            };

            // Tandai jenis yang aktif & bawa kata pencarian di tautan jenis.
            function syncChips(q) {
                chips.forEach((chip) => {
                    const on = (chip.dataset.artikelJenis || '') === jenis;
                    chip.classList.remove(...chip.dataset.on.split(/\s+/), ...chip.dataset.off.split(/\s+/));
                    chip.classList.add(...(on ? chip.dataset.on : chip.dataset.off).split(/\s+/));
                    chip.style.borderColor = on ? '#0B1E07' : '';
                    if (on) chip.setAttribute('aria-current', 'page');
                    else chip.removeAttribute('aria-current');
                    const url = new URL(chip.href);
                    if (q) url.searchParams.set('q', q);
                    else url.searchParams.delete('q');
                    chip.href = url.toString();
                });
                form.querySelector('input[name="jenis"]')?.remove();
            }

            async function go(url, history_ = 'push') {
                try {
                    await window.gdSwap(url, ROOT, { beforeSwap: () => cleanup() });
                    if (history_) history[history_ === 'push' ? 'pushState' : 'replaceState']({ artikel: true }, '', url);
                    shown = new URL(url).search;
                    cleanup = initArtikelMore(document.getElementById(ROOT));
                } catch (error) {
                    if (error.name !== 'AbortError') location.assign(url);
                }
            }

            function search(fromTyping) {
                const q = input.value.trim();
                const url = urlFor(q);
                if (new URL(url).search === shown) return;
                syncChips(q);
                go(url, fromTyping && typing ? 'replace' : 'push');
                typing = fromTyping;
            }

            input.addEventListener('input', () => {
                clearTimeout(timer);
                // Tunggu minimal 2 huruf; kosong = kembali ke semua artikel.
                if (input.value.trim().length === 1) return;
                timer = setTimeout(() => search(true), 400);
            });

            form.addEventListener('submit', (event) => {
                event.preventDefault();
                clearTimeout(timer);
                typing = false;
                search(false);
            });

            document.addEventListener('click', (event) => {
                const chip = event.target.closest('[data-artikel-jenis]');
                const clear = event.target.closest('[data-artikel-clear]');
                if ((!chip && !clear) || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
                event.preventDefault();
                clearTimeout(timer);
                if (chip) jenis = chip.dataset.artikelJenis || '';
                if (clear) input.value = '';
                typing = false;
                search(false);
            });

            window.addEventListener('popstate', (event) => {
                if (!event.state || !event.state.artikel) return;
                const url = new URL(location.href);
                jenis = url.searchParams.get('jenis') || '';
                input.value = url.searchParams.get('q') || '';
                typing = false;
                syncChips(input.value.trim());
                if (url.search !== shown) go(location.href, null);
            });

            history.replaceState({ artikel: true }, '', location.href);
        })();
    </script>
@endpush
