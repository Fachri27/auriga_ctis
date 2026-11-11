@extends('layouts.main')

@php
    $id = app()->getLocale() === 'id';

    $pageTitle = $id ? 'Protokol Konten & Keamanan — greendefender' : 'Content & Security Protocol — greendefender';
    $pageDescription = $id
        ? 'Bagaimana greendefender menjaga konten tetap mutakhir, informasi tetap aman, dan setiap kasus terverifikasi serta dapat dicek keasliannya.'
        : 'How greendefender keeps content up to date, information secure, and every case verified and verifiable.';

    // Ringkasan publik dari dokumen protokol internal; hanya memuat yang sudah diterapkan di sistem.
    $pillars = $id
        ? [
            [
                'label' => 'Integritas konten',
                'title' => 'Benar, terverifikasi, dapat ditelusuri',
                'points' => [
                    'Kasus disusun dan diterbitkan oleh tim Auriga melalui akun CMS internal. Laporan dari publik tidak langsung tampil sebagai kasus.',
                    'Setiap kasus memiliki nomor kasus unik yang dapat dicek keasliannya di halaman ini.',
                    'Perkembangan proses hukum dicatat di linimasa kasus. Kesalahan yang dilaporkan diperiksa dan dikoreksi disertai catatan ralat.',
                    'Berita dari media lain hanya ditampilkan sebagai judul, ringkasan singkat, dan tautan ke berita aslinya.',
                ],
            ],
            [
                'label' => 'Keamanan informasi',
                'title' => 'Akses terbatas, data dilindungi',
                'points' => [
                    'Konten hanya dapat diubah melalui akun CMS tim, yang wajib masuk dengan email terverifikasi; percobaan masuk berulang dibatasi.',
                    'Email pelanggan hanya dipakai untuk mengirim kabar kasus.',
                    'Formulir berlangganan dilindungi dari bot otomatis dengan Cloudflare Turnstile.',
                ],
            ],
            [
                'label' => 'Modernisasi konten',
                'title' => 'Mutakhir dan mudah diakses',
                'points' => [
                    'Konten tersedia dalam bahasa Indonesia dan Inggris, dengan bantuan penerjemah mesin (DeepL).',
                    'Berita penegakan hukum lingkungan dihimpun tiap jam dan disaring agar relevan serta berbahasa Indonesia.',
                    'Tahapan hukum setiap kasus ditampilkan di peta sebagai Aktif atau Selesai, dan perkembangannya dicatat di linimasa.',
                ],
            ],
        ]
        : [
            [
                'label' => 'Content integrity',
                'title' => 'Accurate, verified, traceable',
                'points' => [
                    'Cases are compiled and published by the Auriga team through internal CMS accounts. Public reports do not appear as cases directly.',
                    'Every case has a unique case number that can be authenticated on this page.',
                    'Legal developments are recorded on each case timeline. Reported errors are checked and corrected with a correction note.',
                    'News from other media appears only as a headline, a short summary, and a link to the original story.',
                ],
            ],
            [
                'label' => 'Information security',
                'title' => 'Restricted access, protected data',
                'points' => [
                    'Content can only be changed through team CMS accounts, which require a verified email to sign in; repeated sign-in attempts are limited.',
                    'Subscriber emails are used only to send case updates.',
                    'The subscription form is protected from automated bots by Cloudflare Turnstile.',
                ],
            ],
            [
                'label' => 'Content modernization',
                'title' => 'Up to date and accessible',
                'points' => [
                    'Content is available in Indonesian and English, with the help of machine translation (DeepL).',
                    'Environmental law enforcement news is gathered hourly and filtered for relevance and Indonesian language.',
                    'Each case\'s legal stage is shown on the map as Active or Finished, and its progress is recorded on the timeline.',
                ],
            ],
        ];
@endphp

@section('content')
<section id="protocol-page" class="mt-16 bg-[#F5F7F1]">
    <x-front.page-hero :label="$id ? 'Protokol' : 'Protocol'" :title="__('messages.protocol')"
        :description="$id ? 'Cara kami menjaga setiap informasi di greendefender tetap benar, mutakhir, dan aman.' : 'How we keep every piece of information on greendefender accurate, up to date, and secure.'" />

    {{-- Tiga pilar --}}
    <div class="max-w-7xl mx-auto px-4 py-12 md:py-20">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
            @foreach ($pillars as $pillar)
                <article class="bg-white border border-[#E2E6DA] rounded-sm p-6 md:p-8 flex flex-col">
                    <p class="font-data text-[10px] uppercase tracking-widest text-[#264c16] mb-3">{{ $pillar['label'] }}</p>
                    <h2 class="font-display text-xl md:text-2xl font-semibold text-[#0B1E07] mb-5">{{ $pillar['title'] }}</h2>
                    <ul class="space-y-3">
                        @foreach ($pillar['points'] as $point)
                            <li class="flex items-start gap-3 text-sm text-[#374151] leading-relaxed">
                                <span class="mt-1.5 w-2 h-2 rounded-full bg-[#9BDB4D] flex-shrink-0"></span>
                                <span>{{ $point }}</span>
                            </li>
                        @endforeach
                    </ul>
                </article>
            @endforeach
        </div>

        {{-- Cek keaslian kasus & koreksi --}}
        <div class="mt-10 grid grid-cols-1 lg:grid-cols-2 gap-5">
            <div class="bg-[#0B1E07] console-grid text-white rounded-sm p-6 md:p-8">
                <p class="font-data text-[10px] uppercase tracking-widest text-[#9BDB4D] mb-3">{{ $id ? 'Cek keaslian kasus' : 'Verify a case' }}</p>
                <h2 class="font-display text-xl md:text-2xl font-semibold mb-3">{{ $id ? 'Pastikan kasus benar diterbitkan greendefender' : 'Make sure a case was published by greendefender' }}</h2>
                <p class="text-sm text-white/70 leading-relaxed mb-5">
                    {{ $id ? 'Masukkan nomor kasus (format CASE-XXXXX). Bila tidak ditemukan, kasus tersebut bukan terbitan greendefender.' : 'Enter the case number (format CASE-XXXXX). If it is not found, the case was not published by greendefender.' }}
                </p>
                <form id="verify-case-form" data-base="{{ url(app()->getLocale() . '/verify-case') }}" class="flex flex-col sm:flex-row gap-3">
                    <label for="verify-case-number" class="sr-only">{{ $id ? 'Nomor kasus' : 'Case number' }}</label>
                    <input id="verify-case-number" name="case_number" type="text" required maxlength="20" placeholder="CASE-XXXXX" autocomplete="off"
                        class="flex-1 px-4 py-3 rounded-sm bg-white text-[#0B1E07] text-sm" style="text-transform:uppercase">
                    <button type="submit"
                        class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-sm bg-[#9BDB4D] hover:bg-[#9BDB4D]/90 text-[#0B1E07] font-data uppercase tracking-[0.2em] text-xs transition-colors">
                        {{ $id ? 'Cek kasus' : 'Check case' }}
                    </button>
                </form>
            </div>

            <div class="bg-white border border-[#E2E6DA] rounded-sm p-6 md:p-8">
                <p class="font-data text-[10px] uppercase tracking-widest text-[#264c16] mb-3">{{ $id ? 'Koreksi & pelaporan' : 'Corrections & reports' }}</p>
                <h2 class="font-display text-xl md:text-2xl font-semibold text-[#0B1E07] mb-3">{{ $id ? 'Menemukan informasi yang keliru?' : 'Found incorrect information?' }}</h2>
                <p class="text-sm text-[#374151] leading-relaxed">
                    {{ $id ? 'Kirimkan nomor kasus atau judul artikel beserta penjelasannya. Laporan diperiksa terhadap sumbernya, dan bila terbukti keliru, kami memperbaikinya disertai catatan ralat.' : 'Send us the case number or article title with an explanation. Reports are checked against the sources, and if confirmed, we correct the content with a correction note.' }}
                </p>
                @if (config('social.email'))
                    <a href="mailto:{{ config('social.email') }}"
                        class="mt-5 inline-flex items-center gap-2 px-6 py-3 rounded-sm bg-[#0B1E07] hover:bg-[#264c16] text-white font-data uppercase tracking-[0.2em] text-xs transition-colors">
                        {{ config('social.email') }}
                    </a>
                @endif
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
    <script>
        // Cek keaslian kasus: arahkan ke /{locale}/verify-case/{nomor kasus}.
        document.getElementById('verify-case-form')?.addEventListener('submit', (event) => {
            event.preventDefault();
            const form = event.currentTarget;
            const number = form.querySelector('input').value.trim().toUpperCase();
            if (number) window.location.href = `${form.dataset.base}/${encodeURIComponent(number)}`;
        });
    </script>
@endpush
