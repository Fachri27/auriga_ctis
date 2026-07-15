@extends('layouts.main')

@section('structured-data')
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "WebSite",
    "name": "greendefender",
    "url": "{{ url('/') }}",
    "description": "Platform transparansi kasus hukum lingkungan hidup di Indonesia.",
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
    "description": "Platform transparansi kasus hukum lingkungan hidup di Indonesia — pelacakan kasus dari penyelidikan hingga putusan pengadilan.",
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
                    {{ __('messages.public_transparency') }} — Hukum Lingkungan
                </p>
                <h1 class="font-display font-bold leading-[1.05] tracking-tight text-[clamp(2.1rem,4.5vw,3.5rem)]">
                    Pantau perkara lingkungan Indonesia,<br class="hidden sm:block">
                    dari laporan hingga <span class="text-[#9BDB4D]">putusan.</span>
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
                        Dashboard Data
                    </a>
                </div>
            </div>

            {{-- KPI ledger --}}
            <div class="lg:col-span-6">
                <div class="border border-white/15 bg-[#0B1E07]/60 backdrop-blur-sm">
                    <div class="px-5 py-3 border-b border-white/15 flex items-center justify-between font-data text-[10px] tracking-[0.22em] uppercase text-white/50">
                        <span>Kasus dalam angka</span>
                        <span>Real-time</span>
                    </div>
                    <dl class="grid grid-cols-2 divide-x divide-y divide-white/10">
                        <div class="p-5 sm:p-6">
                            <dd class="font-data text-3xl sm:text-4xl font-semibold text-white">{{ $totalCases ?? '—' }}</dd>
                            <dt class="mt-2 text-xs font-semibold text-white/80">Total Kasus Terdaftar</dt>
                            <p class="font-data text-[10px] tracking-[0.14em] uppercase text-white/40 mt-1">Sejak sistem diluncurkan</p>
                        </div>
                        <div class="p-5 sm:p-6">
                            <dd class="font-data text-3xl sm:text-4xl font-semibold text-[#9BDB4D]">{{ $activeCases ?? '—' }}</dd>
                            <dt class="mt-2 text-xs font-semibold text-white/80">Kasus Aktif</dt>
                            <p class="font-data text-[10px] tracking-[0.14em] uppercase text-white/40 mt-1">Dalam proses hukum</p>
                        </div>
                        <div class="p-5 sm:p-6">
                            <dd class="font-data text-3xl sm:text-4xl font-semibold text-white">{{ $completedCases ?? '—' }}</dd>
                            <dt class="mt-2 text-xs font-semibold text-white/80">Kasus Selesai / Divonis</dt>
                            <p class="font-data text-[10px] tracking-[0.14em] uppercase text-white/40 mt-1">Proses hukum final</p>
                        </div>
                        <div class="p-5 sm:p-6">
                            <dd class="font-data text-3xl sm:text-4xl font-semibold text-white">{{ $regencyCovered ?? '—' }}</dd>
                            <dt class="mt-2 text-xs font-semibold text-white/80">Kabupaten Terdampak</dt>
                            <p class="font-data text-[10px] tracking-[0.14em] uppercase text-white/40 mt-1">Di seluruh Indonesia</p>
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
                    Lihat semua kasus
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
                        Belum ada kasus terverifikasi &amp; dipublikasikan.
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    {{-- ===================== STATISTIK PERKARA ===================== --}}
    @if (!empty($publicCharts))
    <section id="statistik" class="scroll-mt-24 bg-white"
        x-data="{ view: 'chart', switchView(v) { this.view = v; if (v === 'chart') setTimeout(() => pubResizeAll(), 100); } }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-16">

            {{-- Header + toolbar --}}
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <p class="font-data text-[11px] tracking-[0.28em] uppercase text-[#2F6C14] mb-2">{{ __('messages.case_statistics') }}</p>
                    <h2 class="font-display text-2xl sm:text-3xl font-bold text-gray-900 tracking-tight">Indeksasi Putusan Perkara</h2>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    @if (!empty($filterOptions))
                    <form id="filter-form" method="GET" action="{{ url()->current() }}" class="flex flex-wrap items-center gap-2">
                        <select name="tahun" onchange="applyFilters()" aria-label="Filter tahun"
                            class="font-data text-[11px] uppercase tracking-wide border border-[#E2E6DA] px-2 py-2 bg-white text-gray-700">
                            <option value="">Tahun</option>
                            @foreach ($filterOptions['tahun'] as $t)
                            <option value="{{ $t }}" {{ (string) $filterTahun === (string) $t ? 'selected' : '' }}>{{ $t }}</option>
                            @endforeach
                        </select>
                        <select name="klasifikasi" onchange="applyFilters()" aria-label="Filter klasifikasi"
                            class="font-data text-[11px] uppercase tracking-wide border border-[#E2E6DA] px-2 py-2 bg-white text-gray-700 max-w-[150px]">
                            <option value="">Klasifikasi</option>
                            @foreach ($filterOptions['klasifikasi'] as $k)
                            <option value="{{ $k }}" {{ $filterKlasifikasi === $k ? 'selected' : '' }}>{{ $k }}</option>
                            @endforeach
                        </select>
                        <select name="pulau" onchange="applyFilters()" aria-label="Filter pulau"
                            class="font-data text-[11px] uppercase tracking-wide border border-[#E2E6DA] px-2 py-2 bg-white text-gray-700">
                            <option value="">Pulau</option>
                            @foreach ($filterOptions['pulau'] as $p)
                            <option value="{{ $p }}" {{ $filterPulau === $p ? 'selected' : '' }}>{{ $p }}</option>
                            @endforeach
                        </select>
                        @if ($filterTahun || $filterKlasifikasi || $filterPulau)
                        <a href="{{ url()->current() }}#statistik"
                            class="font-data text-[11px] uppercase tracking-wide text-[#2F6C14] hover:underline whitespace-nowrap">Reset</a>
                        @endif
                    </form>
                    @endif

                    <div class="inline-flex border border-[#E2E6DA] p-0.5">
                        <button @click="switchView('chart')"
                            class="px-4 py-1.5 font-data text-[11px] uppercase tracking-wide transition-colors"
                            :class="view === 'chart' ? 'bg-[#0B1E07] text-[#9BDB4D]' : 'text-gray-500 hover:text-gray-800'">Grafik</button>
                        <button @click="switchView('table')"
                            class="px-4 py-1.5 font-data text-[11px] uppercase tracking-wide transition-colors"
                            :class="view === 'table' ? 'bg-[#0B1E07] text-[#9BDB4D]' : 'text-gray-500 hover:text-gray-800'">Tabel</button>
                    </div>
                </div>
            </div>

            {{-- KPI ledger --}}
            @php $byTitle = fn($t) => collect($kpiData ?? [])->firstWhere('title', $t); @endphp
            <div class="mt-8 border border-[#E2E6DA]">
                <dl class="grid grid-cols-1 sm:grid-cols-3 divide-y sm:divide-y-0 sm:divide-x divide-[#E2E6DA]">
                    @foreach (['Perkara', 'Terdakwa', 'Pengadilan'] as $t)
                    @php $k = $byTitle($t); @endphp
                    @if ($k)
                    <div class="px-6 py-5">
                        <dd class="font-data text-2xl sm:text-3xl font-semibold text-gray-900">{{ $k['display'] }}</dd>
                        <dt class="font-data text-[10px] tracking-[0.18em] uppercase text-gray-500 mt-1">{{ $k['title'] }}</dt>
                    </div>
                    @endif
                    @endforeach
                </dl>
                <div class="grid grid-cols-1 lg:grid-cols-2 border-t border-[#E2E6DA] divide-y lg:divide-y-0 lg:divide-x divide-[#E2E6DA]">
                    <div>
                        <p class="px-6 pt-4 font-data text-[10px] tracking-[0.22em] uppercase text-gray-400">Subjek Hukum</p>
                        <dl class="grid grid-cols-2 divide-x divide-[#E2E6DA]">
                            @foreach (['Perorangan', 'Korporasi'] as $t)
                            @php $k = $byTitle($t); @endphp
                            @if ($k)
                            <div class="px-6 py-4">
                                <dd class="font-data text-xl font-semibold text-gray-900">{{ $k['display'] }}</dd>
                                <dt class="font-data text-[10px] tracking-[0.18em] uppercase text-gray-500 mt-1">{{ $k['title'] }}</dt>
                            </div>
                            @endif
                            @endforeach
                        </dl>
                    </div>
                    <div>
                        <p class="px-6 pt-4 font-data text-[10px] tracking-[0.22em] uppercase text-gray-400">Vonis Putusan</p>
                        <dl class="grid grid-cols-3 divide-x divide-[#E2E6DA]">
                            @foreach (['Bersalah', 'Bebas', 'Lepas'] as $t)
                            @php $k = $byTitle('Vonis ' . $t); @endphp
                            @if ($k)
                            <div class="px-6 py-4">
                                <dd class="font-data text-xl font-semibold text-gray-900">{{ $k['display'] }}</dd>
                                <dt class="font-data text-[10px] tracking-[0.18em] uppercase text-gray-500 mt-1">{{ $t }}</dt>
                            </div>
                            @endif
                            @endforeach
                        </dl>
                    </div>
                </div>
            </div>

            {{-- CHART VIEW --}}
            <div x-show="view === 'chart'" class="mt-6 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @foreach (array_slice($publicCharts, 0, 2) as $ch)
                    <div class="border border-[#E2E6DA]">
                        <h3 class="px-5 py-3 border-b border-[#E2E6DA] font-data text-[11px] font-semibold tracking-[0.18em] uppercase text-gray-700">{{ $ch['title'] }}</h3>
                        <div class="pub-echart" id="{{ $ch['id'] }}"
                            data-chart='{{ json_encode($ch['data']) }}'
                            data-type="{{ $ch['type'] }}"
                            style="height:380px;width:100%"></div>
                    </div>
                    @endforeach
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    @foreach (array_slice($publicCharts, 2) as $ch)
                    <div class="border border-[#E2E6DA]">
                        <h3 class="px-5 py-3 border-b border-[#E2E6DA] font-data text-[11px] font-semibold tracking-[0.18em] uppercase text-gray-700">{{ $ch['title'] }}</h3>
                        <div class="pub-echart" id="{{ $ch['id'] }}"
                            data-chart='{{ json_encode($ch['data']) }}'
                            data-type="{{ $ch['type'] }}"
                            style="height:320px;width:100%"></div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- TABLE VIEW --}}
            <div x-show="view === 'table'" x-cloak class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                @forelse ($tableData ?? [] as $td)
                <div class="border border-[#E2E6DA]">
                    <div class="px-5 py-3 border-b border-[#E2E6DA] flex items-center justify-between">
                        <h3 class="font-data text-[11px] font-semibold tracking-[0.18em] uppercase text-gray-700">{{ $td['title'] }}</h3>
                        <span class="font-data text-[10px] text-gray-400">{{ count($td['rows']) }} baris</span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="border-b border-[#E2E6DA]">
                                    <th class="px-4 py-2.5 text-left font-data text-[10px] tracking-[0.14em] font-semibold text-gray-400 uppercase w-8">#</th>
                                    <th class="px-4 py-2.5 text-left font-data text-[10px] tracking-[0.14em] font-semibold text-gray-400 uppercase">Nama</th>
                                    <th class="px-4 py-2.5 text-right font-data text-[10px] tracking-[0.14em] font-semibold text-gray-400 uppercase">Jumlah</th>
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
                <div class="md:col-span-2 text-center py-10 text-gray-400">Belum ada data.</div>
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
                    <p class="font-data text-[11px] tracking-[0.28em] uppercase text-[#9BDB4D] mb-3">Notifikasi Kasus</p>
                    <h2 class="font-display text-2xl sm:text-3xl font-bold text-white tracking-tight">Berlangganan Kasus Terbaru</h2>
                    <p class="mt-3 text-sm text-white/60 leading-relaxed max-w-md">
                        Masukkan email Anda untuk menerima informasi setiap ada kasus terverifikasi terbaru yang dipublikasikan.
                    </p>
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
<script src="https://cdn.jsdelivr.net/npm/echarts@6/dist/echarts.min.js"></script>
<script>
    (function () {
        // Validated categorical palette (dataviz skill, green-first order)
        var CAT = ['#008300', '#2a78d6', '#eda100', '#e87ba4', '#1baf7a', '#eb6834', '#4a3aa7', '#e34948'];
        var GREEN = '#008300';
        var INK = '#374151', MUTED = '#6b7268', GRID = '#EEF1E8', AXISLINE = '#E2E6DA';
        var FONT = "'IBM Plex Mono', monospace";
        // ponytail: console-style bar fill — deep-green base fading to bright leaf at the data end
        function barFill(vertical) {
            return {
                type: 'linear',
                x: 0, y: 0, x2: vertical ? 0 : 1, y2: vertical ? 1 : 0,
                colorStops: [
                    { offset: 0, color: vertical ? '#3FA90F' : '#1F6300' },
                    { offset: 1, color: vertical ? '#1F6300' : '#3FA90F' }
                ]
            };
        }

        window.pubChartInstances = window.pubChartInstances || {};

        function axisCommon(labels, long) {
            return {
                type: 'category',
                data: labels,
                axisLine: { lineStyle: { color: AXISLINE } },
                axisTick: { show: false },
                axisLabel: {
                    color: MUTED, fontSize: 10, fontFamily: FONT,
                    interval: long ? Math.ceil(labels.length / 20) : 0,
                    rotate: long ? 55 : 0
                }
            };
        }

        function valueAxis() {
            return {
                type: 'value', minInterval: 1,
                axisLabel: { color: MUTED, fontSize: 10, fontFamily: FONT },
                splitLine: { lineStyle: { color: GRID } }
            };
        }

        function tooltipFmt(params) {
            var p = Array.isArray(params) ? params[0] : params;
            return '<b>' + p.name + '</b><br/>' + Number(p.value).toLocaleString('id-ID');
        }

        function pubInitChart(el) {
            var raw = el.getAttribute('data-chart');
            if (!raw) return;
            var data = JSON.parse(raw);
            if (!data.length) return;

            if (window.pubChartInstances[el.id]) window.pubChartInstances[el.id].dispose();
            var chart = echarts.init(el);
            window.pubChartInstances[el.id] = chart;

            var type = el.getAttribute('data-type') || 'bar';
            var labels = data.map(function (d) { return d.label; });
            var values = data.map(function (d) { return d.value; });
            var long = labels.length > 15;

            if (type === 'pie') {
                chart.setOption({
                    textStyle: { fontFamily: FONT },
                    tooltip: {
                        trigger: 'item',
                        formatter: function (p) {
                            return '<b>' + p.name + '</b><br/>' + p.value.toLocaleString('id-ID') + ' (' + p.percent.toFixed(1) + '%)';
                        }
                    },
                    series: [{
                        type: 'pie',
                        radius: ['38%', '68%'],
                        data: data.map(function (d) { return { name: d.label, value: d.value }; }),
                        itemStyle: {
                            color: function (p) { return CAT[p.dataIndex % CAT.length]; },
                            borderColor: '#fff', borderWidth: 2, borderRadius: 3
                        },
                        label: {
                            color: INK, fontSize: 10, fontFamily: FONT,
                            formatter: function (p) { return p.name + '\n' + p.value.toLocaleString('id-ID'); }
                        },
                        labelLine: { lineStyle: { color: AXISLINE } }
                    }]
                });
            } else if (type === 'line') {
                chart.setOption({
                    textStyle: { fontFamily: FONT },
                    tooltip: { trigger: 'axis', axisPointer: { type: 'line' }, formatter: tooltipFmt },
                    grid: { left: 8, right: 16, top: 20, bottom: long ? 60 : 10, containLabel: true },
                    xAxis: axisCommon(labels, long),
                    yAxis: valueAxis(),
                    series: [{
                        type: 'line',
                        data: values,
                        lineStyle: { width: 2, color: GREEN },
                        itemStyle: { color: GREEN, borderColor: '#fff', borderWidth: 2 },
                        symbol: 'circle', symbolSize: 8,
                        areaStyle: {
                            color: {
                                type: 'linear', x: 0, y: 0, x2: 0, y2: 1,
                                colorStops: [{ offset: 0, color: 'rgba(0,131,0,0.16)' }, { offset: 1, color: 'rgba(0,131,0,0.01)' }]
                            }
                        }
                    }]
                });
            } else if (type === 'hbar') {
                chart.setOption({
                    textStyle: { fontFamily: FONT },
                    tooltip: { trigger: 'axis', axisPointer: { type: 'shadow' }, formatter: tooltipFmt },
                    grid: { left: 8, right: 40, top: 8, bottom: 8, containLabel: true },
                    xAxis: valueAxis(),
                    yAxis: {
                        type: 'category',
                        data: labels.slice().reverse(),
                        axisLine: { lineStyle: { color: AXISLINE } },
                        axisTick: { show: false },
                        axisLabel: { color: MUTED, fontSize: 9, fontFamily: FONT, width: 120, overflow: 'truncate' }
                    },
                    series: [{
                        type: 'bar',
                        data: values.slice().reverse(),
                        barMaxWidth: 16,
                        itemStyle: { color: barFill(false), borderRadius: 0 },
                        emphasis: { itemStyle: { color: '#0B1E07' } }
                    }]
                });
            } else {
                chart.setOption({
                    textStyle: { fontFamily: FONT },
                    tooltip: { trigger: 'axis', axisPointer: { type: 'shadow' }, formatter: tooltipFmt },
                    grid: { left: 8, right: 16, top: 20, bottom: long ? 60 : 10, containLabel: true },
                    xAxis: axisCommon(labels, long),
                    yAxis: valueAxis(),
                    dataZoom: long ? [{ type: 'slider', start: 0, end: 30, height: 16, bottom: 5 }] : undefined,
                    series: [{
                        type: 'bar',
                        data: values,
                        barMaxWidth: 28,
                        itemStyle: { color: barFill(true), borderRadius: 0 },
                        emphasis: { itemStyle: { color: '#0B1E07' } }
                    }]
                });
            }
        }

        document.querySelectorAll('.pub-echart').forEach(pubInitChart);

        function pubResizeAll() {
            Object.keys(window.pubChartInstances).forEach(function (id) {
                if (window.pubChartInstances[id]) window.pubChartInstances[id].resize();
            });
        }
        window.pubResizeAll = pubResizeAll;
        window.addEventListener('resize', pubResizeAll);

        // ponytail: filters navigate (server re-renders everything) — no DOM patching
        window.applyFilters = function () {
            var p = new URLSearchParams(new FormData(document.getElementById('filter-form')));
            Array.from(p.keys()).forEach(function (k) { if (!p.get(k)) p.delete(k); });
            var qs = p.toString();
            location.assign(location.pathname + (qs ? '?' + qs : '') + '#statistik');
        };
    })();
</script>
@endpush
