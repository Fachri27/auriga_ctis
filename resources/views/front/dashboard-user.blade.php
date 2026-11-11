@extends('layouts.main')

@section('structured-data')
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "WebSite",
    "name": "greendefender",
    "url": "{{ url('/') }}",
    "description": "{{ __('Platform transparansi kasus hukum lingkungan hidup di Indonesia.') }}",
    "potentialAction": {
        "@@type": "SearchAction",
        "target": "{{ url('/') }}/id/verified-cases?q={search_term_string}",
        "query-input": "required name=search_term_string"
    }
}
</script>
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "Organization",
    "name": "greendefender",
    "url": "{{ url('/') }}",
    "description": "{{ __('Platform transparansi kasus hukum lingkungan hidup di Indonesia — pelacakan kasus dari penyelidikan hingga putusan pengadilan.') }}",
    "logo": "{{ asset('img/image.png') }}",
    "sameAs": [
        "https://twitter.com/auriga_id"
    ]
}
</script>
@endsection

@section('content')
<style>
    :root {
        --ink: #0B1E07;
        --ink-2: #143009;
        --brand: #264c16;
        --leaf: #9BDB4D;
        --leaf-deep: #2F6C14;
        --paper: #F5F7F1;
        --hairline: #E2E6DA;
        --hairline-dark: rgba(255, 255, 255, .12);
    }

    .console-grid {
        background-image:
            repeating-linear-gradient(0deg, rgba(255,255,255,.035) 0 1px, transparent 1px 56px),
            repeating-linear-gradient(90deg, rgba(255,255,255,.035) 0 1px, transparent 1px 56px);
    }

    #home-page a:focus-visible,
    #home-page button:focus-visible,
    #home-page select:focus-visible,
    #home-page input:focus-visible {
        outline: 2px solid var(--leaf-deep);
        outline-offset: 2px;
    }
    .hero-dark a:focus-visible, .hero-dark button:focus-visible { outline-color: var(--leaf); }
</style>

<div id="home-page">

    {{-- ===================== HERO: KONSOL PEMANTAUAN ===================== --}}
    <header class="hero-dark console-grid mt-16 text-white" style="background-color: var(--ink-2); background-blend-mode: normal;">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-14 lg:py-20 grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-8 items-center">

            {{-- Copy --}}
            <div class="lg:col-span-6">
                <p class="font-data text-[11px] tracking-[0.28em] uppercase text-[#9BDB4D] mb-5">
                    {{ __('messages.public_transparency') }} — {{ __('Hukum Lingkungan') }}
                </p>
                <h1 class="font-display font-bold leading-[1.05] tracking-tight text-[clamp(2.1rem,4.5vw,3.5rem)]">
                    {{ __('Pantau perkara lingkungan Indonesia,') }}<br class="hidden sm:block">
                    {{ __('dari laporan hingga') }} <span class="text-[#9BDB4D]">{{ __('putusan.') }}</span>
                </h1>
                <p class="mt-6 max-w-xl text-sm sm:text-base text-white/70 leading-relaxed">
                    {{ __('messages.explore') }}
                </p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="{{ route('front.verified-cases', ['locale' => app()->getLocale()]) }}"
                        class="inline-flex items-center gap-2 px-6 py-3 bg-[#9BDB4D] text-[#0B1E07] text-xs font-bold uppercase tracking-widest hover:bg-[#b3ec6b] transition-colors">
                        {{ __('messages.verified_cases') }}
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                    </a>
                    <a href="{{ route('public.dashboard', ['locale' => app()->getLocale()]) }}"
                        class="inline-flex items-center px-6 py-3 border border-white/25 text-white text-xs font-bold uppercase tracking-widest hover:border-white/60 hover:bg-white/5 transition-colors">
                        {{ __('Dashboard Data') }}
                    </a>
                </div>
            </div>

            {{-- KPI ledger --}}
            <div class="lg:col-span-6">
                <div class="border border-white/15 bg-[#0B1E07]/60 backdrop-blur-sm">
                    <div class="px-5 py-3 border-b border-white/15 flex items-center justify-between font-data text-[10px] tracking-[0.22em] uppercase text-white/50">
                        <span>{{ __('Kasus dalam angka') }}</span>
                        <span>Real-time</span>
                    </div>
                    <dl class="grid grid-cols-2 divide-x divide-y divide-white/10">
                        <div class="p-5 sm:p-6">
                            <dd class="font-data text-3xl sm:text-4xl font-semibold text-white">{{ $totalCases ?? '—' }}</dd>
                            <dt class="mt-2 text-xs font-semibold text-white/80">{{ __('Total Kasus Terdaftar') }}</dt>
                            <p class="font-data text-[10px] tracking-[0.14em] uppercase text-white/40 mt-1">{{ __('Sejak sistem diluncurkan') }}</p>
                        </div>
                        <div class="p-5 sm:p-6">
                            <dd class="font-data text-3xl sm:text-4xl font-semibold text-[#9BDB4D]">{{ $activeCases ?? '—' }}</dd>
                            <dt class="mt-2 text-xs font-semibold text-white/80">{{ __('Kasus Aktif') }}</dt>
                            <p class="font-data text-[10px] tracking-[0.14em] uppercase text-white/40 mt-1">{{ __('Dalam proses hukum') }}</p>
                        </div>
                        <div class="p-5 sm:p-6">
                            <dd class="font-data text-3xl sm:text-4xl font-semibold text-white">{{ $completedCases ?? '—' }}</dd>
                            <dt class="mt-2 text-xs font-semibold text-white/80">{{ __('Kasus Selesai / Divonis') }}</dt>
                            <p class="font-data text-[10px] tracking-[0.14em] uppercase text-white/40 mt-1">{{ __('Proses hukum final') }}</p>
                        </div>
                        <div class="p-5 sm:p-6">
                            <dd class="font-data text-3xl sm:text-4xl font-semibold text-white">{{ $regencyCovered ?? '—' }}</dd>
                            <dt class="mt-2 text-xs font-semibold text-white/80">{{ __('Kabupaten Terdampak') }}</dt>
                            <p class="font-data text-[10px] tracking-[0.14em] uppercase text-white/40 mt-1">{{ __('Di seluruh Indonesia') }}</p>
                        </div>
                    </dl>
                </div>
            </div>
        </div>
    </header>

    {{-- ===================== KASUS TERVERIFIKASI ===================== --}}
    <section class="bg-[#F5F7F1] border-b border-[#E2E6DA]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-16">
            <div class="flex flex-wrap items-end justify-between gap-4 mb-8">
                <div>
                    <p class="font-data text-[11px] tracking-[0.28em] uppercase text-[#2F6C14] mb-2">{{ __('messages.kasus') }}</p>
                    <h2 class="font-display text-2xl sm:text-3xl font-bold text-gray-900 tracking-tight">{{ __('messages.verified_cases') }}</h2>
                </div>
                <a href="{{ route('front.verified-cases', ['locale' => app()->getLocale()]) }}"
                    class="font-data text-[11px] font-semibold uppercase tracking-[0.18em] text-[#2F6C14] hover:text-[#0B1E07] transition-colors after:content-['_→']">
                    {{ __('Lihat semua kasus') }}
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                @forelse ($kasus->take(3) as $c)
                    @php
                        $trans = $c->translations->where('locale', app()->getLocale())->first()
                              ?? $c->translations->first();
                        $catNames = $categories->whereIn('id', $c->category_ids ?? [])->map(function ($cat) {
                            $t = $cat->translations->firstWhere('locale', app()->getLocale()) ?? $cat->translations->first();
                            return $t?->name;
                        })->filter()->implode(', ');
                    @endphp
                    <article class="group bg-white border border-[#E2E6DA] flex flex-col hover:border-[#0B1E07] hover:-translate-y-0.5 transition-all duration-200">
                        <div class="px-5 py-3 border-b border-[#E2E6DA] flex items-center justify-between gap-2">
                            <span class="font-data text-[11px] font-semibold text-gray-900 truncate">{{ $c->case_number ?? '—' }}</span>
                            <span class="shrink-0 font-data text-[9px] tracking-[0.16em] uppercase px-2 py-1 bg-[#0B1E07] text-[#9BDB4D]">✓ Terverifikasi</span>
                        </div>
                        <div class="p-5 flex flex-col flex-1">
                            @if ($trans?->title)
                                <h3 class="font-display text-base font-bold text-gray-900 leading-snug mb-3">
                                    {{ Str::limit(strip_tags($trans->title), 110) }}
                                </h3>
                            @endif
                            <p class="text-sm text-gray-500 leading-relaxed flex-1">
                                {{ Str::limit(strip_tags($trans?->description ?? ''), 150) ?: '—' }}
                            </p>
                            <dl class="mt-4 pt-4 border-t border-[#E2E6DA] grid grid-cols-2 gap-x-4 gap-y-3">
                                <div>
                                    <dt class="font-data text-[9px] tracking-[0.16em] uppercase text-gray-400 mb-1">Kategori</dt>
                                    <dd class="text-xs text-gray-800">{{ $catNames ?: '—' }}</dd>
                                </div>
                                <div>
                                    <dt class="font-data text-[9px] tracking-[0.16em] uppercase text-gray-400 mb-1">Status</dt>
                                    <dd class="text-xs text-gray-800">{{ $c->current_status_label ?? '—' }}</dd>
                                </div>
                                <div>
                                    <dt class="font-data text-[9px] tracking-[0.16em] uppercase text-gray-400 mb-1">Tanggal Kejadian</dt>
                                    <dd class="font-data text-xs text-gray-800">{{ $c->event_date ? date('d M Y', strtotime($c->event_date)) : '—' }}</dd>
                                </div>
                            </dl>
                        </div>
                        <a href="{{ route('public.verify.case', ['locale' => app()->getLocale(), 'caseNumber' => $c->case_number]) }}"
                            class="px-5 py-3 border-t border-[#E2E6DA] font-data text-[10px] font-semibold uppercase tracking-[0.18em] text-[#2F6C14] group-hover:bg-[#0B1E07] group-hover:text-[#9BDB4D] transition-colors">
                            Lihat Detail Kasus →
                        </a>
                    </article>
                @empty
                    <div class="md:col-span-3 border border-dashed border-[#E2E6DA] bg-white text-center text-gray-400 py-16 text-sm">
                        {{ __('Belum ada kasus terverifikasi & dipublikasikan.') }}
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    {{-- ===================== STATISTIK PERKARA ===================== --}}
    @if (!empty($publicCharts))
    <section id="statistik" class="scroll-mt-24 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-16">

            {{-- Header + toolbar --}}
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <p class="font-data text-[11px] tracking-[0.28em] uppercase text-[#2F6C14] mb-2">{{ __('messages.case_statistics') }}</p>
                    <h2 class="font-display text-2xl sm:text-3xl font-bold text-gray-900 tracking-tight">{{ __('Indeksasi putusan Pidana Sektor') }}<br> {{ __('Sumber Daya Alam dan Lingkungan Hidup') }}</h2>
                </div>

                <div class="gd-filter-toolbar-wrap flex flex-wrap items-center gap-3">
                    @if (!empty($filterOptions))
                    <form id="filter-form" method="GET" action="{{ url()->current() }}" class="gd-filter-toolbar">
                        <select name="tahun" onchange="applyFilters()" aria-label="{{ __('Filter tahun') }}"
                            data-filter
                            class="gd-select gd-select-sm font-data uppercase tracking-wide">
                            <option value="">{{ __('Tahun') }}</option>
                            @foreach ($filterOptions['tahun'] as $t)
                            <option value="{{ $t }}" {{ (string) $filterTahun === (string) $t ? 'selected' : '' }}>{{ $t }}</option>
                            @endforeach
                        </select>
                        <select name="klasifikasi" onchange="applyFilters()" aria-label="{{ __('Filter klasifikasi') }}"
                            data-filter
                            class="gd-select gd-select-sm font-data uppercase tracking-wide">
                            <option value="">{{ __('Klasifikasi') }}</option>
                            @foreach ($filterOptions['klasifikasi'] as $k)
                            <option value="{{ $k }}" {{ $filterKlasifikasi === $k ? 'selected' : '' }}>{{ $k }}</option>
                            @endforeach
                        </select>
                        <select name="pulau" onchange="applyFilters()" aria-label="{{ __('Filter pulau') }}"
                            data-filter
                            class="gd-select gd-select-sm font-data uppercase tracking-wide">
                            <option value="">{{ __('Pulau') }}</option>
                            @foreach ($filterOptions['pulau'] as $p)
                            <option value="{{ $p }}" {{ $filterPulau === $p ? 'selected' : '' }}>{{ $p }}</option>
                            @endforeach
                        </select>
                        @if ($filterTahun || $filterKlasifikasi || $filterPulau)
                        <a href="{{ url()->current() }}#statistik" data-statistik-nav
                            class="font-data text-[11px] uppercase tracking-wide text-[#2F6C14] hover:underline whitespace-nowrap">Reset</a>
                        @endif
                    </form>
                    @endif
                </div>
            </div>

            {{-- Grafik (KPI dan grafik batang per tahun ada di Dashboard mode Perkara), lalu tabel Top 10 di bawahnya --}}
            <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                @foreach (array_filter($publicCharts, fn ($ch) => $ch['type'] !== 'bar') as $ch)
                <div class="border border-[#E2E6DA]">
                    <h3 class="px-5 py-3 border-b border-[#E2E6DA] font-data text-[11px] font-semibold tracking-[0.18em] uppercase text-gray-700">{{ __($ch['title']) }}</h3>
                    <div class="pub-echart" id="{{ $ch['id'] }}"
                        data-chart='{{ json_encode($ch['data']) }}'
                        data-type="{{ $ch['type'] }}"
                        style="height:380px;width:100%"></div>
                </div>
                @endforeach
            </div>

            <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                @forelse ($tableData ?? [] as $td)
                <div class="border border-[#E2E6DA]">
                    <div class="px-5 py-3 border-b border-[#E2E6DA] flex items-center justify-between">
                        <h3 class="font-data text-[11px] font-semibold tracking-[0.18em] uppercase text-gray-700">{{ __($td['title']) }}</h3>
                        <span class="font-data text-[10px] text-gray-400">{{ __(':count baris', ['count' => count($td['rows'])]) }}</span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="border-b border-[#E2E6DA]">
                                    <th class="px-4 py-2.5 text-left font-data text-[10px] tracking-[0.14em] font-semibold text-gray-400 uppercase w-8">#</th>
                                    <th class="px-4 py-2.5 text-left font-data text-[10px] tracking-[0.14em] font-semibold text-gray-400 uppercase">{{ __('Nama') }}</th>
                                    <th class="px-4 py-2.5 text-right font-data text-[10px] tracking-[0.14em] font-semibold text-gray-400 uppercase">{{ __('Jumlah') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#F0F2EA]">
                                @foreach ($td['rows'] as $i => $r)
                                <tr class="hover:bg-[#F5F7F1] transition-colors">
                                    <td class="px-4 py-2 font-data text-xs text-gray-400">{{ $i + 1 }}</td>
                                    <td class="px-4 py-2 text-gray-700 max-w-md truncate">{{ $r['label'] }}</td>
                                    <td class="px-4 py-2 text-right font-data font-medium text-gray-900">{{ number_format($r['value']) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @empty
                <div class="md:col-span-2 text-center py-10 text-gray-400">{{ __('Belum ada data.') }}</div>
                @endforelse
            </div>

        </div>
    </section>
    @endif

    {{-- ===================== BERLANGGANAN ===================== --}}
    <section class="bg-[#F5F7F1] border-t border-[#E2E6DA]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-16">
            <div class="bg-[#0B1E07] console-grid px-6 py-10 sm:px-12 sm:py-12 grid grid-cols-1 lg:grid-cols-2 gap-8 items-center">
                <div>
                    <p class="font-data text-[11px] tracking-[0.28em] uppercase text-[#9BDB4D] mb-3">{{ __('Ikut Mengawal') }}</p>
                    <h2 class="font-display text-2xl sm:text-3xl font-bold text-white tracking-tight leading-tight">{{ __('Kejahatan lingkungan tumbuh subur saat tak ada yang mengawasi.') }}</h2>
                    <p class="mt-3 text-sm text-white/60 leading-relaxed max-w-md">
                        {{ __('Jadilah salah satu yang mengawasi. Kami kabari lewat email setiap ada kasus baru yang terverifikasi dan setiap kali status sebuah kasus berubah.') }}
                    </p>
                    <ul class="mt-5 space-y-2 text-sm text-white/80">
                        @foreach ([__('Kasus baru yang sudah diverifikasi tim Auriga'), __('Perkembangan status, dari penyidikan hingga putusan'), __('Gratis, langsung ke email Anda')] as $point)
                            <li class="flex items-center gap-2">
                                <svg class="flex-shrink-0 text-[#9BDB4D]" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg>
                                {{ $point }}
                            </li>
                        @endforeach
                    </ul>
                </div>
                <div class="lg:justify-self-end w-full max-w-md">
                    @livewire('case-subscribe-form')
                </div>
            </div>
        </div>
    </section>

</div>
@endsection

@push('scripts')
@include('front.components.perkara-charts-script')
@include('front.components.page-swap-script')
<script>
    (function () {
        // Filter statistik tanpa reload: bagian #statistik diambil ulang dari server lalu ditukar, grafiknya
        // dipasang lagi. Gagal → reload biasa.
        var shown = location.search; // query statistik yang sedang tampil

        function go(url, push, focusName) {
            window.gdSwap(url, 'statistik', { beforeSwap: function (el) { window.pubDisposeCharts(el); } })
                .then(function (section) {
                    if (push) history.pushState({ statistik: true }, '', url);
                    shown = new URL(url, location.href).search;
                    window.pubInitCharts(section);
                    var field = focusName && section.querySelector('#filter-form [name="' + focusName + '"]');
                    if (field) field.focus({ preventScroll: true });
                })
                .catch(function (e) { if (e.name !== 'AbortError') location.assign(url); });
        }

        window.applyFilters = function () {
            var p = new URLSearchParams(new FormData(document.getElementById('filter-form')));
            Array.from(p.keys()).forEach(function (k) { if (!p.get(k)) p.delete(k); });
            var qs = p.toString();
            var active = document.activeElement;
            go(location.pathname + (qs ? '?' + qs : ''), true, active && active.form && active.form.id === 'filter-form' ? active.name : null);
        };

        document.addEventListener('click', function (e) {
            var link = e.target.closest('#statistik a[data-statistik-nav]');
            if (!link || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
            e.preventDefault();
            go(link.pathname + link.search, true);
        });

        window.addEventListener('popstate', function (e) {
            // Kembali/maju ke entri dengan filter berbeda (bukan sekadar pindah #anchor).
            if (e.state && e.state.statistik && location.search !== shown) go(location.href, false);
        });
        history.replaceState({ statistik: true }, '', location.href);
    })();
</script>
@endpush
