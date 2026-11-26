@extends('layouts.main')

@php
    $mode ??= 'kasus';
    $pageTitle = $mode === 'perkara'
        ? __('Dashboard Perkara') . ' — greendefender'
        : __('Dashboard Kasus Lingkungan — greendefender');
    $pageDescription = $mode === 'perkara'
        ? __('Statistik perkara pidana sektor sumber daya alam dan lingkungan hidup di Indonesia: jumlah perkara, terdakwa, pengadilan, klasifikasi, dan sebarannya per kabupaten.')
        : __('Jelajahi semua kasus hukum lingkungan hidup di Indonesia. Filter berdasarkan status dan kategori. Data transparan dan terverifikasi.');
@endphp

@section('content')
@include('public.partials.dashboard-map-style')
{{-- #dashboard-page ditukar utuh saat ganti mode/filter tanpa reload (lihat skrip di bawah). --}}
<div id="dashboard-page" data-mode="{{ $mode }}" class="mt-16 bg-[#F5F7F1]">

    {{-- ===================== HEADER ===================== --}}
    <x-front.page-hero inner="px-4 sm:px-6"
        :label="$mode === 'perkara' ? __('Dashboard Perkara') : __('Dashboard Kasus')"
        :title="$mode === 'perkara' ? __('Dashboard Perkara Publik') : __('Dashboard Kasus Publik')"
        :description="$mode === 'perkara' ? __('Indeksasi putusan pidana sektor sumber daya alam dan lingkungan hidup di Indonesia') : __('Pantau dan telusuri informasi kasus hukum yang terbuka untuk masyarakat')">
        <x-slot:aside>
            <p class="font-data text-xs text-white/50">
                {{ __('Diperbarui:') }} <strong class="text-white">{{ now()->format('d M Y, H:i') }} WIB</strong>
            </p>
        </x-slot:aside>
    </x-front.page-hero>

    <div data-dash-body>
    {{-- ===================== MODE: KASUS / PERKARA ===================== --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 pt-6">
        <nav aria-label="{{ __('Mode dashboard') }}" class="flex flex-col sm:flex-row sm:items-center gap-3">
            <div class="inline-flex border border-[#E2E6DA] bg-white p-0.5">
                @foreach (['kasus' => __('Kasus'), 'perkara' => __('Perkara')] as $key => $label)
                    <a href="{{ route('public.dashboard', ['locale' => app()->getLocale()] + ($key === 'perkara' ? ['mode' => 'perkara'] : [])) }}"
                        data-dash-nav @if ($mode === $key) aria-current="page" @endif
                        class="px-5 py-2 font-data text-[11px] uppercase tracking-[0.18em] transition-colors {{ $mode === $key ? 'bg-[#0B1E07] text-[#9BDB4D]' : 'text-[#6b7268] hover:text-[#0B1E07]' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>
            {{-- Definisi yang sama dengan bagian Terminologi di halaman About. --}}
            @php $term = __('about.content.terms.' . $mode); @endphp
            <p class="text-xs text-[#6b7268]">
                <strong class="font-semibold text-[#0B1E07]">{{ $term['term'] }}:</strong> {{ $term['text'] }}
            </p>
        </nav>
    </section>

    @if ($mode === 'perkara')
        @include('public.partials.dashboard-perkara')
    @else
    {{-- ===================== SUMMARY CARDS ===================== --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 py-6">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">

            {{-- Total --}}
            <div class="bg-white rounded-sm border border-[#E2E6DA] p-5">
                <p class="text-[10px] uppercase tracking-widest text-[#6b7268] font-data">{{ __('Total Kasus') }}</p>
                <p class="mt-2 text-2xl sm:text-3xl font-bold text-[#0B1E07] font-data">{{ $totalCases }}</p>
                <p class="text-xs text-[#6b7268] mt-1">{{ __('Sejak sistem diluncurkan') }}</p>
            </div>

            {{-- Aktif --}}
            <div class="bg-white rounded-sm border border-[#E2E6DA] p-5">
                <p class="text-[10px] uppercase tracking-widest text-[#6b7268] font-data">{{ __('Kasus Aktif') }}</p>
                <p class="mt-2 text-2xl sm:text-3xl font-bold text-[#9BDB4D] font-data">{{ $activeCases }}</p>
                <p class="text-xs text-[#6b7268] mt-1">{{ __('Sedang dalam proses hukum') }}</p>
            </div>

            {{-- Selesai --}}
            <div class="bg-white rounded-sm border border-[#E2E6DA] p-5">
                <p class="text-[10px] uppercase tracking-widest text-[#6b7268] font-data">{{ __('Kasus Selesai') }}</p>
                <p class="mt-2 text-2xl sm:text-3xl font-bold text-[#0B1E07] font-data">{{ $closedCases }}</p>
                <p class="text-xs text-[#6b7268] mt-1">{{ __('Proses hukum sudah final') }}</p>
            </div>

            {{-- Kabupaten/Kota --}}
            <div class="bg-white rounded-sm border border-[#E2E6DA] p-5 col-span-2 lg:col-span-1">
                <p class="text-[10px] uppercase tracking-widest text-[#6b7268] font-data">{{ __('Kabupaten Terdampak') }}</p>
                <p class="mt-2 text-2xl sm:text-3xl font-bold text-[#0B1E07] font-data">
                    {{ $regencyCount }}
                </p>
                <p class="text-xs text-[#6b7268] mt-1">{{ __('Dari 514 kabupaten/kota di Indonesia') }}</p>
            </div>

        </div>
    </section>

    {{-- ===================== FILTER & SEARCH ===================== --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 pb-4">
        <div class="bg-white rounded-sm border border-[#E2E6DA] p-4 sm:p-5">
            <div class="flex flex-col sm:flex-row gap-3">

                {{-- Search --}}
                <div class="relative flex-1">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-[#6b7268]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 105 11a6 6 0 0012 0z"/>
                    </svg>
                    <input type="text" id="searchInput" placeholder="{{ __('Cari nomor kasus, judul, atau lokasi...') }}"
                        class="w-full pl-9 pr-4 py-2.5 text-sm border border-[#E2E6DA] rounded-sm focus:outline-none focus:border-[#9BDB4D] focus:ring-1 focus:ring-[#9BDB4D] bg-white text-[#0B1E07] transition-colors">
                </div>

                {{-- Filter Status --}}
                <select id="filterStatus" aria-label="{{ __('Filter status') }}" data-filter
                    class="gd-select gd-select-fixed">
                    <option value="">{{ __('Semua Status') }}</option>
                    @foreach($statusOptions as $s)
                    <option value="{{ $s['name'] ?? '' }}">{{ __($s['name'] ?? '') }}</option>
                    @endforeach
                </select>

                {{-- Filter Kategori --}}
                <select id="filterCategory" aria-label="{{ __('Filter kategori') }}" data-filter
                    class="gd-select gd-select-fixed">
                    <option value="">{{ __('Semua Kategori') }}</option>
                    @foreach($cases->pluck('category')->filter()->unique(function ($cat) { return $cat['name'] ?? ''; }) as $cat)
                    <option value="{{ $cat['name'] ?? '' }}">{{ $cat['name'] ?? '' }}</option>
                    @endforeach
                </select>

                {{-- Toggle View --}}
                <div class="gd-view-toggle flex rounded-sm border border-[#E2E6DA] overflow-hidden flex-shrink-0">
                    <button id="btnMap" onclick="setView('map')"
                        class="px-3 py-2.5 text-sm font-medium bg-[#0B1E07] text-white transition-colors flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                        </svg>
                        {{ __('Peta') }}
                    </button>
                    <button id="btnList" onclick="setView('list')"
                        class="px-3 py-2.5 text-sm font-medium bg-white text-[#0B1E07] hover:bg-[#F5F7F1] transition-colors flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                        </svg>
                        {{ __('Tabel') }}
                    </button>
                </div>

            </div>

            {{-- Active filter label --}}
            <div class="mt-3 flex items-center gap-2 text-xs text-[#6b7268]">
                <span>{{ __('Menampilkan:') }}</span>
                <span id="filterLabel" class="font-semibold text-[#0B1E07]">{{ __('Semua Kasus') }}</span>
                <span id="caseCount" class="bg-[#F5F7F1] text-[#0B1E07] px-2 py-0.5 rounded-full font-medium font-data"></span>
            </div>
        </div>
    </section>

    {{-- ===================== MAP + SIDEBAR ===================== --}}
    <section id="viewMap" class="max-w-7xl mx-auto px-4 sm:px-6 pb-8">
        {{-- data-map-card: bagian yang dibuat layar penuh (lihat front/components/dashboard-map-script) --}}
        <div data-map-card class="gd-dash-card bg-white rounded-sm border border-[#E2E6DA] overflow-hidden">
            <div class="gd-dash-layout">

                {{-- Daftar kasus yang terlihat di peta; ikut berubah saat peta digeser atau diperbesar --}}
                <div class="gd-dash-side" id="sidebarWrapper">
                    <div class="px-4 py-3 border-b border-[#E2E6DA] bg-[#F5F7F1] flex-shrink-0">
                        <p class="text-[10px] uppercase tracking-widest text-[#6b7268] font-data">{{ __('Kasus di Area Peta') }}</p>
                        <p id="caseListCount" class="text-xs text-[#6b7268] mt-0.5" aria-live="polite"></p>
                    </div>
                    <div id="caseList" class="overflow-y-auto flex-1">
                        {{-- Filled by JS --}}
                    </div>
                </div>

                {{-- Map --}}
                <div class="gd-dash-canvas">
                    <div id="map" class="w-full h-full"></div>
                    {{-- Legenda ringkas; cincin kelompok = perbandingan kasus aktif & selesai, angka = jumlah kasus --}}
                    <div class="gd-map-legend">
                        <span><i style="background:#d97706"></i>{{ __('Aktif') }}</span>
                        <span><i style="background:#2F6C14"></i>{{ __('Selesai') }}</span>
                        <span><i style="background:conic-gradient(#d97706 0 60%, #2F6C14 60% 100%);-webkit-mask:radial-gradient(circle, transparent 2px, #000 2.5px);mask:radial-gradient(circle, transparent 2px, #000 2.5px)"></i>{{ __('Kelompok (aktif : selesai)') }}</span>
                    </div>
                    {{-- Muncul bila pencarian/filter tidak menemukan kasus --}}
                    <div id="mapEmpty" class="gd-map-empty" hidden>
                        <div>
                            <p>{{ __('Tidak ada kasus yang cocok dengan pencarian atau filter.') }}</p>
                            <button type="button" data-action="clear-filters">{{ __('Hapus filter') }}</button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    {{-- ===================== TABLE VIEW ===================== --}}
    <section id="viewList" class="max-w-7xl mx-auto px-4 sm:px-6 pb-8 hidden">
        <div class="bg-white rounded-sm border border-[#E2E6DA] overflow-hidden">
            <div class="px-5 py-4 border-b border-[#E2E6DA] flex items-center justify-between">
                <h2 class="font-display font-semibold text-[#0B1E07] text-sm">{{ __('Daftar Kasus') }}</h2>
                <span id="tableCount" class="text-xs bg-[#F5F7F1] text-[#6b7268] px-2.5 py-1 rounded-full font-medium font-data"></span>
            </div>

            {{-- Mobile: card list --}}
            <div id="mobileCardList" class="divide-y divide-[#E2E6DA] sm:hidden"></div>

            {{-- Desktop: table --}}
            <div class="hidden sm:block">
                <table class="w-full text-sm table-fixed">
                    <colgroup>
                        <col class="w-[130px]">
                        <col class="w-[22%]">
                        <col class="w-[210px]">
                        <col class="w-[120px]">
                        <col class="w-[16%]">
                        <col class="w-[110px]">
                        <col class="w-[80px]">
                    </colgroup>
                    <thead class="bg-[#F5F7F1] text-[10px] uppercase tracking-widest text-[#6b7268] font-data">
                        <tr>
                            <th class="text-left px-5 py-3 font-semibold">{{ __('No. Kasus') }}</th>
                            <th class="text-left px-5 py-3 font-semibold">{{ __('Judul') }}</th>
                            <th class="text-left px-5 py-3 font-semibold">{{ __('Kategori') }}</th>
                            <th class="text-left px-5 py-3 font-semibold">{{ __('Status') }}</th>
                            <th class="text-left px-5 py-3 font-semibold">{{ __('Lokasi') }}</th>
                            <th class="text-left px-5 py-3 font-semibold">{{ __('Tanggal') }}</th>
                            <th class="px-5 py-3"></th>
                        </tr>
                    </thead>
                    <tbody id="caseTableBody" class="divide-y divide-[#E2E6DA] text-[#374151]">
                        {{-- Filled by JS --}}
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="px-5 py-4 border-t border-[#E2E6DA] flex flex-col sm:flex-row items-center justify-between gap-3">
                <p id="paginationInfo" class="text-xs text-[#6b7268] font-data"></p>
                <div class="flex gap-2">
                    <button id="btnPrev" onclick="changePage(-1)"
                        class="px-3 py-1.5 text-xs border border-[#E2E6DA] bg-white text-[#0B1E07] rounded-sm hover:border-[#9BDB4D] hover:bg-[#F5F7F1] disabled:opacity-40 disabled:cursor-not-allowed transition-colors">
                        {{ __('← Sebelumnya') }}
                    </button>
                    <button id="btnNext" onclick="changePage(1)"
                        class="px-3 py-1.5 text-xs border border-[#E2E6DA] bg-white text-[#0B1E07] rounded-sm hover:border-[#9BDB4D] hover:bg-[#F5F7F1] disabled:opacity-40 disabled:cursor-not-allowed transition-colors">
                        {{ __('Berikutnya →') }}
                    </button>
                </div>
            </div>
        </div>
    </section>

    {{-- ===================== DATA KASUS (untuk skrip peta & tabel) ===================== --}}
    @php
        // Data & teks UI untuk window.initKasusDashboard sesuai locale aktif. Disiapkan di sini karena @json memecah argumennya per koma.
        $dashboardI18n = [
            'category'     => __('Kategori:'),
            'detail'       => __('Lihat Detail →'),
            'view'         => __('Lihat →'),
            'noCases'      => __('Tidak ada kasus ditemukan'),
            'allCases'     => __('Semua Kasus'),
            'casesCount'   => __(':count kasus'),
            'showing'      => __('Menampilkan :start–:end dari :total kasus'),
            'clusterTitle' => __(':active aktif · :finished selesai'),
            'inView'       => __(':shown dari :total kasus terlihat di peta'),
            'noneInArea'   => __('Tidak ada kasus di area peta ini.'),
            'showAll'      => __('Tampilkan semua kasus'),
            'outside'      => __(':count kasus lain di luar area peta.'),
            // Nama status dari DB → label sesuai locale.
            'statuses'     => collect($cases)->pluck('status.name')->filter()->unique()
                ->mapWithKeys(fn ($n) => [$n => __($n)]),
        ];
        $kasusData = ['cases' => $cases, 'i18n' => $dashboardI18n, 'locale' => app()->getLocale() === 'en' ? 'en-US' : 'id-ID'];
    @endphp
    <script type="application/json" id="kasus-data">@json($kasusData)</script>
    @endif
    </div>
</div>
@endsection

@push('scripts')
@include('front.components.perkara-charts-script', ['autoInit' => false])
@include('front.components.page-swap-script')
@include('front.components.dashboard-map-script')
@include('public.partials.dashboard-perkara-script')
<script>
// Peta, daftar & tabel mode Kasus. Dipanggil saat halaman dimuat dan setiap kali konten mode Kasus ditukar tanpa
// reload; datanya dibaca dari #kasus-data. Mengembalikan fungsi pembersih.
window.initKasusDashboard = function (root) {
    const dataEl = root.querySelector('#kasus-data');
    if (!dataEl || !window.gdDashMap) return () => {};
    const DATA = JSON.parse(dataEl.textContent);
    const $ = (id) => root.querySelector('#' + id);

    // ========================
    // DATA
    // ========================
    const allCases = DATA.cases;
    let filteredCases = [...allCases];
    let currentPage = 1;
    const perPage = 10;
    let currentView = 'map';
    let entries = [];       // { i, c, marker } kasus yang sedang tampil di peta (sesuai filter)
    let activeEntry = null; // kasus yang popup-nya sedang terbuka
    let listKey = null;     // isi daftar samping terakhir; daftar hanya digambar ulang bila isinya berubah
    let hoveredEl = null;
    let searchTimer = null;

    // ========================
    // I18N
    // ========================
    const I18N = DATA.i18n;

    // Isi placeholder ":nama" (kunci terpanjang dulu, seperti Laravel).
    function fmt(str, params) {
        return Object.keys(params)
            .sort((a, b) => b.length - a.length)
            .reduce((s, k) => s.split(':' + k).join(params[k]), str);
    }

    // Nama status dari DB diterjemahkan; fallback ke nama asli.
    function statusLabel(name) {
        return (name != null && I18N.statuses[name]) || name;
    }

    // "2024-03-15" → "15 Mar 2024" / "Mar 15, 2024" sesuai bahasa halaman.
    function dateText(value) {
        if (!value) return '';
        const d = new Date(String(value).slice(0, 10) + 'T00:00:00');
        return isNaN(d) ? String(value) : d.toLocaleDateString(DATA.locale, { day: 'numeric', month: 'short', year: 'numeric' });
    }

    // Warna per kelompok status (lihat App\Models\Status::group): aktif = masih berjalan, selesai = sudah tuntas.
    const STATUS_GROUP_COLORS = { aktif: '#d97706', selesai: '#2F6C14' };

    function getStatusColorHex(statusGroup) {
        return STATUS_GROUP_COLORS[statusGroup] ?? STATUS_GROUP_COLORS.aktif;
    }

    function createDotIcon(color) {
        return L.divIcon({
            className: 'gd-case-dot',
            html: `<div style="
                width:16px;height:16px;border-radius:50%;
                background:${color};border:2.5px solid white;
                box-shadow:0 1px 4px rgba(0,0,0,0.35);
            "></div>`,
            iconSize: [16, 16],
            iconAnchor: [8, 8],
            popupAnchor: [0, -10]
        });
    }

    // ========================
    // MAP INIT
    // ========================
    const dash = gdDashMap.create($('map'), {
        maxZoom: 16,
        satellite: true,
        card: root.querySelector('[data-map-card]'),
        onReset: () => fitCases(true),
    });
    const map = dash.map;

    // Ikon kelompok: cincin berwarna sesuai perbandingan kasus aktif & selesai di dalamnya, angka = jumlah kasus.
    function createClusterIcon(cluster) {
        const markers = cluster.getAllChildMarkers();
        const total = markers.length;
        const selesai = markers.filter((m) => m.options.statusGroup === 'selesai').length;
        const aktif = total - selesai;
        const aktifPct = (aktif / total) * 100;
        const size = total < 10 ? 36 : (total < 100 ? 42 : 50);
        const ring = `conic-gradient(${STATUS_GROUP_COLORS.aktif} 0 ${aktifPct}%, ${STATUS_GROUP_COLORS.selesai} ${aktifPct}% 100%)`;

        return L.divIcon({
            className: 'case-cluster',
            html: `<div title="${fmt(I18N.clusterTitle, { active: aktif, finished: selesai })}" style="
                width:${size}px;height:${size}px;border-radius:50%;padding:5px;box-sizing:border-box;
                background:${ring};box-shadow:0 1px 6px rgba(0,0,0,0.35);
            "><div style="
                width:100%;height:100%;border-radius:50%;background:#fff;
                display:flex;align-items:center;justify-content:center;
                font-family:inherit;font-weight:700;font-size:12px;line-height:1;color:#0B1E07;
            ">${total}</div></div>`,
            iconSize: L.point(size, size),
        });
    }

    var clusterGroup = L.markerClusterGroup({
        // Radius kecil agar kasus di pulau berbeda tidak tergabung saat peta diperkecil (mis. di ponsel).
        maxClusterRadius: 50,
        disableClusteringAtZoom: 12,
        spiderfyOnMaxZoom: true,
        showCoverageOnHover: false,
        iconCreateFunction: createClusterIcon
    });
    map.addLayer(clusterGroup);

    // ========================
    // MARKERS (dibuat sekali; filter hanya mengganti penanda yang dimasukkan ke peta)
    // ========================
    function popupHtml(c) {
        const color = getStatusColorHex(c.status?.group);
        const cats = categoryList(c);
        const loc = locText(c);
        return `<div class="gd-pop">
            <div class="gd-pop-status" style="color:${color}"><i style="background:${color}"></i>${escapeHtml(statusLabel(c.status?.name) ?? '-')}</div>
            <div class="gd-pop-title">${escapeHtml(c.title ?? c.case_number ?? '')}</div>
            <div class="gd-pop-meta font-data">${escapeHtml(c.case_number ?? '')}${c.event_date ? ' · ' + escapeHtml(dateText(c.event_date)) : ''}</div>
            ${loc ? `<div class="gd-pop-loc"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"/><circle cx="12" cy="10" r="3"/></svg><span>${escapeHtml(loc)}</span></div>` : ''}
            ${cats.length ? `<div class="gd-pop-cats">${cats.map((name) => `<span>${escapeHtml(name)}</span>`).join('')}</div>` : ''}
            <a class="gd-pop-cta" href="${c.detail_url ?? '#'}">${I18N.detail}</a>
        </div>`;
    }

    const allEntries = [];
    allCases.forEach(function (c) {
        if (!c.latitude || !c.longitude) return;
        const lat = parseFloat(c.latitude);
        const lng = parseFloat(c.longitude);
        if (isNaN(lat) || isNaN(lng)) return;

        const marker = L.marker([lat, lng], {
            icon: createDotIcon(getStatusColorHex(c.status?.group)),
            statusGroup: c.status?.group ?? 'aktif',
            title: c.title ?? c.case_number ?? '',
        });
        const entry = { i: allEntries.length, c, marker };
        marker.bindPopup(popupHtml(c), { maxWidth: 280, autoPanPadding: [40, 40] });
        marker.on('popupopen', () => setActive(entry));
        marker.on('popupclose', () => { if (activeEntry === entry) setActive(null); });
        // Elemen penanda dibuat ulang setiap kali keluar-masuk kelompok; tandai lagi bila sedang aktif.
        marker.on('add', () => { if (activeEntry === entry) marker.getElement()?.classList.add('is-active'); });
        allEntries.push(entry);
    });
    const entryOf = new Map(allEntries.map((e) => [e.c, e]));

    function renderMarkers(data) {
        map.closePopup();
        entries = data.map((c) => entryOf.get(c)).filter(Boolean);
        clusterGroup.clearLayers();
        clusterGroup.addLayers(entries.map((e) => e.marker));
        $('mapEmpty').hidden = data.length > 0;
    }

    function filtersActive() {
        return Boolean($('searchInput').value.trim() || $('filterStatus').value || $('filterCategory').value);
    }

    // Tanpa filter: seluruh Indonesia. Dengan filter: arahkan peta ke kasus yang cocok.
    function fitCases(animate) {
        if (!entries.length || !filtersActive()) return dash.fitIndonesia(animate);
        map.fitBounds(L.latLngBounds(entries.map((e) => e.marker.getLatLng())), { padding: [48, 48], maxZoom: 10, animate });
    }

    // Tampilkan kasus di peta: perbesar, keluarkan dari kelompok, lalu buka popup-nya.
    function focusEntry(entry) {
        if (currentView !== 'map') setView('map');
        gdDashMap.reveal(map, clusterGroup, entry.marker, 12);
    }

    function setActive(entry) {
        activeEntry?.marker.getElement()?.classList.remove('is-active');
        activeEntry = entry;
        entry?.marker.getElement()?.classList.add('is-active');
        markActiveItem(true);
    }

    // Sorot penanda (atau kelompok yang memuatnya) saat kasus di daftar disorot.
    function hover(entry) {
        hoveredEl?.classList.remove('is-hover');
        hoveredEl = entry ? clusterGroup.getVisibleParent(entry.marker)?.getElement() ?? null : null;
        hoveredEl?.classList.add('is-hover');
    }

    // ========================
    // SIDEBAR: kasus yang terlihat di peta saat ini
    // ========================
    // Lokasi ringkas untuk daftar: kabupaten/kota & provinsi (desa hanya bila keduanya kosong).
    function shortLoc(c) {
        return [c.regency, c.province].filter(Boolean).join(', ') || c.village || '';
    }

    function itemHtml(e) {
        const c = e.c;
        const color = getStatusColorHex(c.status?.group);
        const loc = shortLoc(c);
        return `<button type="button" class="gd-case-item" data-entry="${e.i}">
            <span class="gd-case-item-title">${escapeHtml(c.title ?? c.case_number ?? '')}</span>
            <span class="gd-case-item-meta">
                <span class="dot" style="background:${color}"></span>
                <span class="status">${escapeHtml(statusLabel(c.status?.name) ?? '-')}</span>
                ${loc ? `<span aria-hidden="true">·</span><span class="loc">${escapeHtml(loc)}</span>` : ''}
            </span>
            <span class="gd-case-item-code font-data">${escapeHtml(c.case_number ?? '')}${c.event_date ? ' · ' + escapeHtml(dateText(c.event_date)) : ''}</span>
        </button>`;
    }

    function renderSidebar() {
        const list = $('caseList');
        const bounds = map.getBounds();
        const inView = entries.filter((e) => bounds.contains(e.marker.getLatLng()));
        $('caseListCount').textContent = fmt(I18N.inView, { shown: inView.length, total: entries.length });

        const key = entries.length + '|' + inView.map((e) => e.i).join(',');
        if (key === listKey) return markActiveItem();
        listKey = key;

        const hadFocus = list.contains(document.activeElement);
        hover(null);
        if (!entries.length) {
            list.innerHTML = `<p class="gd-case-empty font-data">${I18N.noCases}</p>`;
        } else if (!inView.length) {
            list.innerHTML = `<div class="gd-case-empty"><p>${I18N.noneInArea}</p><button type="button" data-action="fit">${I18N.showAll}</button></div>`;
        } else {
            const outside = entries.length - inView.length;
            list.innerHTML = inView.slice(0, 100).map(itemHtml).join('')
                + (outside ? `<div class="gd-case-empty"><p>${fmt(I18N.outside, { count: outside })}</p><button type="button" data-action="fit">${I18N.showAll}</button></div>` : '');
        }
        markActiveItem(true);
        // Pengguna keyboard: fokus tetap di daftar setelah daftar digambar ulang.
        if (hadFocus) (list.querySelector('.is-active') ?? list.querySelector('button'))?.focus({ preventScroll: true });
    }

    function markActiveItem(scroll = false) {
        const list = $('caseList');
        list.querySelectorAll('[data-entry]').forEach((el) => {
            const on = activeEntry !== null && Number(el.dataset.entry) === activeEntry.i;
            el.classList.toggle('is-active', on);
            if (on) el.setAttribute('aria-current', 'true');
            else el.removeAttribute('aria-current');
            if (on && scroll) gdDashMap.scrollWithin(list, el);
        });
    }

    const caseList = $('caseList');
    const entryFromEvent = (e) => {
        const item = e.target.closest('[data-entry]');
        return item ? allEntries[Number(item.dataset.entry)] : null;
    };
    caseList.addEventListener('click', (e) => {
        if (e.target.closest('[data-action="fit"]')) return fitCases(true);
        const entry = entryFromEvent(e);
        if (entry) focusEntry(entry);
    });
    caseList.addEventListener('mouseover', (e) => hover(entryFromEvent(e)));
    caseList.addEventListener('mouseleave', () => hover(null));
    caseList.addEventListener('focusin', (e) => hover(entryFromEvent(e)));
    caseList.addEventListener('focusout', () => hover(null));
    map.on('moveend', renderSidebar);

    // ========================
    // TABLE RENDER
    // ========================

    // Kategori bisa banyak; render sebagai chip dan sisanya diringkas jadi "+N"
    // supaya kolom tidak melebar / menabrak kolom Status.
    function categoryList(c) {
        if (Array.isArray(c.categories) && c.categories.length) return c.categories;
        const name = c.category?.name ?? '';
        return name ? name.split(',').map(s => s.trim()).filter(Boolean) : [];
    }

    function categoryChips(c, max = 2) {
        const cats = categoryList(c);
        if (!cats.length) return '<span class="text-xs text-[#6b7268] font-data">-</span>';
        const shown = cats.slice(0, max);
        const rest  = cats.length - shown.length;
        const chips = shown.map(name => `<span class="inline-block max-w-full truncate px-2 py-0.5 rounded-full bg-[#F5F7F1] border border-[#E2E6DA] text-[10px] text-[#374151] font-data" title="${escapeHtml(name)}">${escapeHtml(name)}</span>`);
        if (rest > 0) {
            chips.push(`<span class="inline-block px-2 py-0.5 rounded-full bg-[#F5F7F1] border border-[#E2E6DA] text-[10px] text-[#6b7268] font-data" title="${escapeHtml(cats.join(', '))}">+${rest}</span>`);
        }
        return `<div class="flex flex-wrap gap-1">${chips.join('')}</div>`;
    }

    function renderTable(data) {
        const start  = (currentPage - 1) * perPage;
        const paged  = data.slice(start, start + perPage);
        const total  = data.length;

        $('tableCount').textContent = fmt(I18N.casesCount, { count: total });
        $('paginationInfo').textContent =
            fmt(I18N.showing, { start: total ? start + 1 : 0, end: Math.min(start + perPage, total), total });
        $('btnPrev').disabled = currentPage === 1;
        $('btnNext').disabled = start + perPage >= total;

        // Desktop table
        $('caseTableBody').innerHTML = paged.map(c => {
            const color = getStatusColorHex(c.status?.group);
            const detailUrl = c.detail_url ?? '#';
            return `<tr class="hover:bg-[#F5F7F1] transition-colors align-top">
                <td class="px-5 py-3 font-data text-xs text-[#6b7268] truncate">${escapeHtml(c.case_number ?? '-')}</td>
                <td class="px-5 py-3">
                    <p class="font-medium text-[#0B1E07] truncate text-xs" title="${escapeHtml(c.title ?? '')}">${escapeHtml(c.title ?? '-')}</p>
                </td>
                <td class="px-5 py-3">${categoryChips(c)}</td>
                <td class="px-5 py-3">
                    <span style="background:${color}22;color:${color}" class="inline-block max-w-full truncate px-2.5 py-1 rounded-full text-[10px] font-bold font-data align-middle" title="${escapeHtml(statusLabel(c.status?.name) ?? '')}">
                        ${escapeHtml(statusLabel(c.status?.name) ?? '-')}
                    </span>
                </td>
                <td class="px-5 py-3 text-xs text-[#6b7268] truncate" title="${escapeHtml(locText(c) || '')}">${escapeHtml(locText(c) || '-')}</td>
                <td class="px-5 py-3 text-xs text-[#6b7268] truncate font-data">${escapeHtml(dateText(c.event_date) || '-')}</td>
                <td class="px-5 py-3 text-right">
                    <a href="${detailUrl}" class="text-xs text-[#0B1E07] hover:text-[#9BDB4D] font-medium whitespace-nowrap transition-colors">${I18N.view}</a>
                </td>
            </tr>`;
        }).join('');

        // Mobile cards
        $('mobileCardList').innerHTML = paged.map(c => {
            const color = getStatusColorHex(c.status?.group);
            const detailUrl = c.detail_url ?? '#';
            return `<div class="px-4 py-4">
                <div class="flex items-start justify-between gap-2">
                    <div class="min-w-0">
                        <p class="font-semibold text-[#0B1E07] text-sm leading-snug">${escapeHtml(c.title ?? '-')}</p>
                        <p class="text-xs text-[#6b7268] font-data mt-0.5">${escapeHtml(c.case_number ?? '')}</p>
                    </div>
                    <span style="background:${color}22;color:${color}" class="px-2 py-0.5 rounded-full text-[10px] font-bold flex-shrink-0 font-data">
                        ${escapeHtml(statusLabel(c.status?.name) ?? '-')}
                    </span>
                </div>
                <div class="mt-2">${categoryChips(c, 3)}</div>
                <div class="flex items-center gap-3 mt-2 text-xs text-[#6b7268] font-data">
                    ${locText(c) ? `<span>${escapeHtml(locText(c))}</span>` : ''}
                    ${c.event_date ? `<span>${escapeHtml(dateText(c.event_date))}</span>` : ''}
                </div>
                <a href="${detailUrl}" class="inline-block mt-2 text-xs text-[#0B1E07] hover:text-[#9BDB4D] font-medium transition-colors">${I18N.detail}</a>
            </div>`;
        }).join('');
    }

    // ========================
    // FILTER LOGIC
    // ========================
    function applyFilter() {
        const search   = $('searchInput').value.trim().toLowerCase();
        const status   = $('filterStatus').value;
        const category = $('filterCategory').value;

        filteredCases = allCases.filter(c => {
            const matchSearch = !search ||
                (c.case_number ?? '').toLowerCase().includes(search) ||
                (c.title ?? '').toLowerCase().includes(search) ||
                (c.regency ?? '').toLowerCase().includes(search) ||
                (c.village ?? '').toLowerCase().includes(search) ||
                (c.province ?? '').toLowerCase().includes(search);
            const matchStatus   = !status   || (c.status?.name ?? '') === status;
            const matchCategory = !category || (c.category?.name ?? '') === category;
            return matchSearch && matchStatus && matchCategory;
        });

        currentPage = 1;

        // Update label
        const parts = [];
        if (search)   parts.push(`"${search}"`);
        if (status)   parts.push(statusLabel(status));
        if (category) parts.push(category);
        $('filterLabel').textContent = parts.length ? parts.join(' · ') : I18N.allCases;
        $('caseCount').textContent   = fmt(I18N.casesCount, { count: filteredCases.length });

        renderMarkers(filteredCases);
        fitCases(true);
        renderSidebar();
        renderTable(filteredCases);
    }

    // ========================
    // VIEW TOGGLE
    // ========================
    window.setView = function(view) {
        currentView = view;
        $('viewMap').classList.toggle('hidden', view !== 'map');
        $('viewList').classList.toggle('hidden', view !== 'list');
        $('btnMap').className  = view === 'map'
            ? 'px-3 py-2.5 text-sm font-medium bg-[#0B1E07] text-white transition-colors flex items-center gap-1.5'
            : 'px-3 py-2.5 text-sm font-medium bg-white text-[#0B1E07] hover:bg-[#F5F7F1] transition-colors flex items-center gap-1.5';
        $('btnList').className = view === 'list'
            ? 'px-3 py-2.5 text-sm font-medium bg-[#0B1E07] text-white transition-colors flex items-center gap-1.5'
            : 'px-3 py-2.5 text-sm font-medium bg-white text-[#0B1E07] hover:bg-[#F5F7F1] transition-colors flex items-center gap-1.5';
        if (view === 'map') setTimeout(() => map.invalidateSize(), 100);
    };

    // ========================
    // PAGINATION
    // ========================
    window.changePage = function(dir) {
        const maxPage = Math.max(1, Math.ceil(filteredCases.length / perPage));
        currentPage = Math.max(1, Math.min(maxPage, currentPage + dir));
        renderTable(filteredCases);
        $('viewList').scrollIntoView({ behavior: 'smooth', block: 'start' });
    };

    // ========================
    // ESCAPE HTML
    // ========================
    function escapeHtml(text) {
        const map = { '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;' };
        return String(text).replace(/[&<>"']/g, m => map[m]);
    }

    // Gabungan lokasi: desa, kabupaten/kota, provinsi (yang tidak null).
    function locText(c) {
        return [c.village, c.regency, c.province].filter(Boolean).join(', ');
    }

    // ========================
    // EVENT LISTENERS
    // ========================
    // Pencarian ditunda sebentar agar peta tidak melompat-lompat di setiap ketikan.
    $('searchInput').addEventListener('input', () => {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(applyFilter, 250);
    });
    $('filterStatus').addEventListener('change', applyFilter);
    $('filterCategory').addEventListener('change', applyFilter);
    root.querySelector('[data-action="clear-filters"]').addEventListener('click', () => {
        $('searchInput').value = '';
        $('filterStatus').value = '';
        $('filterCategory').value = '';
        applyFilter();
    });

    // ========================
    // INIT
    // ========================
    applyFilter();

    return () => {
        clearTimeout(searchTimer);
        dash.destroy();
    };
};
</script>
<script>
    // Ganti mode (Kasus/Perkara), filter perkara, dan klik klasifikasi tanpa reload: #dashboard-page diambil ulang
    // dari server lalu ditukar; peta & grafik lama dibersihkan, lalu yang baru dipasang. Gagal → reload biasa.
    (() => {
        const ROOT = 'dashboard-page';
        let cleanup = () => {};
        let shown = location.search; // query (mode & filter) yang sedang tampil

        function init() {
            const root = document.getElementById(ROOT);
            if (!root) return;
            const start = root.dataset.mode === 'perkara' ? window.initPerkaraDashboard : window.initKasusDashboard;
            cleanup = (start && start(root)) || (() => {});
        }

        async function go(url, { push = true, focus = null } = {}) {
            try {
                const root = await window.gdSwap(url, ROOT, {
                    dim: (el) => el.querySelector('[data-dash-body]'),
                    beforeSwap: () => cleanup(),
                });
                if (push) history.pushState({ dashboard: true }, '', url);
                shown = new URL(url, location.href).search;
                init();
                if (focus) focus(root)?.focus({ preventScroll: true });
            } catch (e) {
                if (e.name !== 'AbortError') location.assign(url);
            }
        }

        function filterUrl(form) {
            const url = new URL(form.action, location.href);
            new FormData(form).forEach((value, key) => { if (value !== '') url.searchParams.set(key, value); });
            return url.toString();
        }

        document.addEventListener('click', (e) => {
            const link = e.target.closest('a[data-dash-nav]');
            if (!link || e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
            e.preventDefault();
            // Pengguna keyboard: fokus kembali ke tautan di posisi yang sama setelah konten ditukar.
            const links = [...document.querySelectorAll('a[data-dash-nav]')];
            const index = link.matches(':focus-visible') ? links.indexOf(link) : -1;
            go(link.href, { focus: index < 0 ? null : (root) => root.querySelectorAll('a[data-dash-nav]')[index] });
        });

        document.addEventListener('change', (e) => {
            const form = e.target.closest('form[data-dash-filter]');
            if (!form) return;
            const name = e.target.name;
            go(filterUrl(form), { focus: name ? (root) => root.querySelector(`form[data-dash-filter] [name="${name}"]`) : null });
        });

        document.addEventListener('submit', (e) => {
            const form = e.target.closest('form[data-dash-filter]');
            if (!form) return;
            e.preventDefault();
            go(filterUrl(form));
        });

        window.addEventListener('popstate', (e) => {
            if (e.state && e.state.dashboard && location.search !== shown) go(location.href, { push: false });
        });

        history.replaceState({ dashboard: true }, '', location.href);
        if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
        else init();
    })();
</script>
@endpush
