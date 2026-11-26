{{-- Mode Perkara dashboard: KPI seperti di Beranda, peta perkara per kabupaten dengan sidebar klasifikasi,
     lalu grafik batang per tahun di bawah peta. Data dari App\Services\PerkaraStatistics. --}}
@php
    // Satu warna per klasifikasi (urut jumlah terbanyak); dipakai di sidebar, peta, dan legenda.
    $palette = ['#008300', '#2a78d6', '#eda100', '#e87ba4', '#1baf7a', '#eb6834', '#4a3aa7', '#e34948', '#0f766e', '#8a6d3b'];
    $colors = collect($classifications)->values()->mapWithKeys(fn ($c, $i) => [$c['label'] => $palette[$i % count($palette)]])->all();
    $classTotal = collect($classifications)->sum('value');
    $hasFilters = (bool) array_filter($perkaraFilters);

    // URL dashboard mode Perkara dengan filter saat ini, diubah sebagian.
    $perkaraUrl = fn (array $changes = []) => route('public.dashboard', ['locale' => app()->getLocale()]
        + array_filter(array_merge(['mode' => 'perkara'], $perkaraFilters, $changes), fn ($v) => $v !== null && $v !== ''));

    // Gaya dropdown bersama ada di layouts.main (.gd-select); di layar lebar ketiganya sama lebar memenuhi baris.
    $selectClass = 'gd-select sm:flex-1';
@endphp

{{-- ===================== FILTER ===================== --}}
<section class="max-w-7xl mx-auto px-4 sm:px-6 pt-4">
    <form method="GET" action="{{ route('public.dashboard', ['locale' => app()->getLocale()]) }}" data-dash-filter
        class="bg-white rounded-sm border border-[#E2E6DA] p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center gap-3">
        <input type="hidden" name="mode" value="perkara">
        <select name="tahun" aria-label="{{ __('Filter tahun') }}" class="{{ $selectClass }}" data-filter>
            <option value="">{{ __('Semua tahun') }}</option>
            @foreach ($filterOptions['tahun'] as $t)
                <option value="{{ $t }}" @selected((string) $perkaraFilters['tahun'] === (string) $t)>{{ $t }}</option>
            @endforeach
        </select>
        <select name="klasifikasi" aria-label="{{ __('Filter klasifikasi') }}" class="{{ $selectClass }}" data-filter>
            <option value="">{{ __('Semua klasifikasi') }}</option>
            @foreach ($filterOptions['klasifikasi'] as $k)
                <option value="{{ $k }}" @selected($perkaraFilters['klasifikasi'] === $k)>{{ $k }}</option>
            @endforeach
        </select>
        <select name="pulau" aria-label="{{ __('Filter pulau') }}" class="{{ $selectClass }}" data-filter>
            <option value="">{{ __('Semua pulau') }}</option>
            @foreach ($filterOptions['pulau'] as $p)
                <option value="{{ $p }}" @selected($perkaraFilters['pulau'] === $p)>{{ $p }}</option>
            @endforeach
        </select>
        <noscript><button type="submit" class="px-4 py-2.5 text-sm bg-[#0B1E07] text-white rounded-sm">{{ __('Terapkan') }}</button></noscript>
        @if ($hasFilters)
            <a href="{{ $perkaraUrl(['tahun' => null, 'klasifikasi' => null, 'pulau' => null]) }}" data-dash-nav
                class="font-data text-[11px] uppercase tracking-widest text-[#2F6C14] hover:underline whitespace-nowrap flex-shrink-0">{{ __('Reset filter') }}</a>
        @endif
    </form>
</section>

{{-- ===================== KPI ===================== --}}
<section class="max-w-7xl mx-auto px-4 sm:px-6">
    @include('front.components.perkara-kpi')
</section>

{{-- ===================== PETA + SIDEBAR KLASIFIKASI ===================== --}}
<section class="max-w-7xl mx-auto px-4 sm:px-6 py-6">
    {{-- data-map-card: bagian yang dibuat layar penuh (lihat front/components/dashboard-map-script) --}}
    <div data-map-card class="gd-dash-card bg-white rounded-sm border border-[#E2E6DA] overflow-hidden">
        <div class="gd-dash-layout">
            {{-- Sidebar: klasifikasi sekaligus legenda warna peta --}}
            <div class="gd-dash-side">
                <div class="px-4 py-3 border-b border-[#E2E6DA] bg-[#F5F7F1] flex-shrink-0">
                    <p class="text-[10px] uppercase tracking-widest text-[#6b7268] font-data">{{ __('Klasifikasi Perkara') }}</p>
                    <p class="text-xs text-[#6b7268] mt-0.5">{{ __('Klik untuk menyaring peta dan angka') }}</p>
                </div>
                <div class="overflow-y-auto flex-1 divide-y divide-[#E2E6DA]">
                    <a href="{{ $perkaraUrl(['klasifikasi' => null]) }}" data-dash-nav
                        class="block px-4 py-3 hover:bg-[#F5F7F1] transition-colors {{ $perkaraFilters['klasifikasi'] ? '' : 'bg-[#F5F7F1]' }}">
                        <span class="flex items-center justify-between gap-2 text-xs">
                            <span class="flex items-center gap-2.5 min-w-0 font-semibold text-[#0B1E07]">
                                <span class="flex items-center justify-center flex-shrink-0 rounded-full bg-[#0B1E07] text-white" style="width:24px;height:24px">
                                    @include('front.components.klasifikasi-icon', ['label' => null, 'size' => 13])
                                </span>
                                {{ __('Semua klasifikasi') }}
                            </span>
                            <span class="font-data text-[#6b7268]">{{ number_format($classTotal) }}</span>
                        </span>
                    </a>
                    @foreach ($classifications as $c)
                        @php
                            $active = $perkaraFilters['klasifikasi'] === $c['label'];
                            $share = $classTotal ? $c['value'] / $classTotal * 100 : 0;
                        @endphp
                        <a href="{{ $perkaraUrl(['klasifikasi' => $active ? null : $c['label']]) }}" data-dash-nav @if ($active) aria-current="true" @endif
                            class="block px-4 py-3 hover:bg-[#F5F7F1] transition-colors {{ $active ? 'bg-[#F5F7F1]' : '' }}">
                            <span class="flex items-center justify-between gap-2 text-xs">
                                <span class="flex items-center gap-2.5 min-w-0 {{ $active ? 'font-semibold text-[#0B1E07]' : 'text-[#374151]' }}">
                                    {{-- Ikon klasifikasi di lingkaran berwarna (warna sama dengan cincin di peta) --}}
                                    <span class="flex items-center justify-center flex-shrink-0 rounded-full text-white" style="width:24px;height:24px;background: {{ $colors[$c['label']] }}">
                                        @include('front.components.klasifikasi-icon', ['label' => $c['label'], 'size' => 13])
                                    </span>
                                    <span class="truncate">{{ $c['label'] }}</span>
                                </span>
                                <span class="font-data text-[#6b7268] flex-shrink-0">{{ number_format($c['value']) }}</span>
                            </span>
                            <span class="mt-1.5 block h-1 bg-[#F5F7F1] rounded-full overflow-hidden" style="margin-left: 34px">
                                <span class="block h-full rounded-full" style="width: {{ max(1, round($share, 1)) }}%; background: {{ $colors[$c['label']] }}"></span>
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>

            {{-- Peta --}}
            <div class="gd-dash-canvas">
                {{-- Legenda bisa dibuka-tutup; tertutup di awal agar tidak menutupi peta. --}}
                <details class="perkara-legend gd-map-corner bg-white rounded-sm border border-[#E2E6DA] text-xs shadow-sm" style="max-width: 230px">
                    @php
                        // Contoh cincin di legenda memakai tiga warna klasifikasi teratas.
                        $sample = array_slice(array_values($colors), 0, 3) + ['#008300', '#2a78d6', '#eda100'];
                    @endphp
                    <summary class="flex items-center gap-2 cursor-pointer select-none font-data text-[10px] uppercase tracking-widest text-[#6b7268]" style="padding: 6px 10px; list-style: none">
                        <span class="flex-shrink-0" style="width:14px;height:14px;border-radius:50%;padding:3px;box-sizing:border-box;background:conic-gradient({{ $sample[0] }} 0 55%, {{ $sample[1] }} 55% 85%, {{ $sample[2] }} 85% 100%)">
                            <span style="display:block;width:100%;height:100%;border-radius:50%;background:#fff"></span>
                        </span>
                        {{ __('Legenda') }}
                        <svg class="perkara-legend-chevron" width="10" height="10" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M5.3 12.7a1 1 0 0 0 1.4 0L10 9.4l3.3 3.3a1 1 0 0 0 1.4-1.4l-4-4a1 1 0 0 0-1.4 0l-4 4a1 1 0 0 0 0 1.4z"/></svg>
                    </summary>
                    <div class="border-t border-[#E2E6DA] text-[#0B1E07]" style="padding: 8px 10px; font-size: 11px; line-height: 1.4">
                        <p>{{ __('Angka = jumlah perkara; warna cincin = komposisi klasifikasi (lihat daftar).') }}</p>
                        <p style="margin-top: 4px">{{ __('Lingkaran menggabungkan kabupaten yang berdekatan; klik untuk memperbesar.') }}</p>
                        <p class="text-[#6b7268]" style="margin-top: 4px">{{ __('Titik berada di pusat wilayah kabupaten, bukan lokasi kejadian.') }}</p>
                        @if ($regencies['unmapped'])
                            <p class="text-[#6b7268]" style="margin-top: 4px">{{ __(':count perkara tanpa lokasi kabupaten tidak tampil di peta.', ['count' => number_format($regencies['unmapped'])]) }}</p>
                        @endif
                        @if ($regencies['doubtful']['count'])
                            <p class="text-[#6b7268]" style="margin-top: 4px">{{ __(':count perkara berlabel :regencies tidak dipetakan karena dicatat oleh :courts pengadilan berbeda (diduga salah isi di sumber data).', [
                                'count' => number_format($regencies['doubtful']['count']),
                                'regencies' => implode(', ', $regencies['doubtful']['regencies']),
                                'courts' => $regencies['doubtful']['courts'],
                            ]) }}</p>
                        @endif
                    </div>
                </details>
                <div id="perkaraMap" class="w-full h-full"></div>
            </div>
        </div>
    </div>
</section>

{{-- ===================== GRAFIK BATANG ===================== --}}
@if ($yearlyCharts)
    {{-- Baris pertama: jumlah per tahun (3 kolom); baris kedua: rata-rata vonis pidana (2 kolom). --}}
    @php [$sentenceCharts, $countCharts] = collect($yearlyCharts)->partition(fn ($ch) => !empty($ch['unit'])); @endphp
    <section class="max-w-7xl mx-auto px-4 sm:px-6 pb-8">
        @foreach ([[$countCharts, 'md:grid-cols-3'], [$sentenceCharts, 'md:grid-cols-2']] as [$charts, $cols])
            @if ($charts->isNotEmpty())
                <div class="grid grid-cols-1 {{ $cols }} gap-6 {{ $loop->first ? '' : 'mt-6' }}">
                    @foreach ($charts as $ch)
                        <div class="bg-white border border-[#E2E6DA]">
                            <h3 class="px-5 py-3 border-b border-[#E2E6DA] font-data text-[11px] font-semibold tracking-[0.18em] uppercase text-gray-700">{{ __($ch['title']) }}</h3>
                            <div class="pub-echart" id="{{ $ch['id'] }}" data-chart="{{ json_encode($ch['data']) }}" data-type="{{ $ch['type'] }}"
                                @if (!empty($ch['unit'])) data-unit="{{ $ch['unit'] }}" data-unit-label="{{ __($ch['unit']) }}" @endif
                                style="height:320px;width:100%"></div>
                        </div>
                    @endforeach
                </div>
            @endif
        @endforeach
        <p class="mt-4 text-xs text-[#6b7268]">
            {{ __('Sumber: indeksasi putusan pidana sektor sumber daya alam dan lingkungan hidup :from–:to.', ['from' => \App\Services\PerkaraStatistics::YEARS[0], 'to' => \App\Services\PerkaraStatistics::YEARS[1]]) }}
            @if ($sentenceCharts->isNotEmpty())
                {{ __('Rata-rata vonis dihitung dari semua putusan yang vonisnya tercatat, termasuk putusan bebas atau lepas (dihitung 0).') }}
            @endif
        </p>
    </section>
@endif

{{-- ===================== DATA PETA ===================== --}}
@php
    // Data peta untuk window.initPerkaraDashboard (public/partials/dashboard-perkara-script).
    // @json memecah argumennya pada koma, jadi data disusun di sini dulu.
    $perkaraMap = [
        'points' => $regencies['points'],
        'colors' => $colors,
        'filtered' => (bool) ($perkaraFilters['pulau'] || $perkaraFilters['klasifikasi']),
        'locale' => app()->getLocale() === 'en' ? 'en-US' : 'id-ID',
        'i18n' => [
            'cases' => __('perkara'),
            'defendants' => __('terdakwa'),
            'cluster' => __(':regencies kabupaten, :cases perkara. Klik untuk memperbesar.'),
            'search' => __('Cari kabupaten/kota…'),
            'rank' => __('Peringkat :rank dari :count kabupaten/kota'),
        ],
    ];
@endphp
<script type="application/json" id="perkara-map-data">@json($perkaraMap)</script>
<style>
    .perkara-donut > div { transition: transform .15s ease; }
    .perkara-donut:hover > div { transform: scale(1.15); }
    .perkara-legend > summary::-webkit-details-marker { display: none; }
    .perkara-legend-chevron { margin-left: auto; transform: rotate(180deg); transition: transform .15s ease; }
    .perkara-legend[open] .perkara-legend-chevron { transform: none; }
</style>
