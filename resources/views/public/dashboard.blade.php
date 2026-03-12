@extends('layouts.main')

@section('content')
<div class="bg-gray-50 mt-20 poppins-regular">

    {{-- ===================== HEADER ===================== --}}
    <section class="bg-white border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-8 sm:py-12">
            <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold tracking-[0.2em] uppercase text-gray-400 mb-1">Transparansi Publik</p>
                    <h1 class="text-2xl sm:text-3xl md:text-4xl font-bold text-gray-900">Dashboard Kasus Publik</h1>
                    <p class="text-gray-500 text-sm mt-1">Pantau dan telusuri informasi kasus hukum yang terbuka untuk masyarakat</p>
                </div>
                <p class="text-xs text-gray-400 flex-shrink-0">
                    🕐 Diperbarui: <strong>{{ now()->format('d M Y, H:i') }} WIB</strong>
                </p>
            </div>
        </div>
    </section>

    {{-- ===================== SUMMARY CARDS ===================== --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 py-6">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">

            {{-- Total --}}
            <div class="bg-white rounded-xl shadow-sm p-4 sm:p-6 border border-gray-100">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-xs text-gray-400 uppercase tracking-wide font-medium">Total Kasus</p>
                        <p class="mt-1.5 text-2xl sm:text-3xl font-bold text-gray-900">{{ $totalCases }}</p>
                        <p class="text-xs text-gray-400 mt-1">Sejak sistem diluncurkan</p>
                    </div>
                    <div class="w-10 h-10 bg-blue-50 rounded-lg flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Aktif --}}
            <div class="bg-white rounded-xl shadow-sm p-4 sm:p-6 border border-gray-100">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-xs text-gray-400 uppercase tracking-wide font-medium">Kasus Aktif</p>
                        <p class="mt-1.5 text-2xl sm:text-3xl font-bold text-yellow-600">{{ $activeCases }}</p>
                        <p class="text-xs text-gray-400 mt-1">Sedang dalam proses hukum</p>
                    </div>
                    <div class="w-10 h-10 bg-yellow-50 rounded-lg flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Selesai --}}
            <div class="bg-white rounded-xl shadow-sm p-4 sm:p-6 border border-gray-100">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-xs text-gray-400 uppercase tracking-wide font-medium">Kasus Selesai</p>
                        <p class="mt-1.5 text-2xl sm:text-3xl font-bold text-green-600">{{ $closedCases }}</p>
                        <p class="text-xs text-gray-400 mt-1">Proses hukum sudah final</p>
                    </div>
                    <div class="w-10 h-10 bg-green-50 rounded-lg flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Provinsi --}}
            <div class="bg-white rounded-xl shadow-sm p-4 sm:p-6 border border-gray-100 col-span-2 lg:col-span-1">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-xs text-gray-400 uppercase tracking-wide font-medium">Provinsi Terdampak</p>
                        <p class="mt-1.5 text-2xl sm:text-3xl font-bold text-purple-600">
                            {{ collect($cases)->pluck('province')->filter()->unique()->count() }}
                        </p>
                        <p class="text-xs text-gray-400 mt-1">Dari 38 provinsi di Indonesia</p>
                    </div>
                    <div class="w-10 h-10 bg-purple-50 rounded-lg flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

        </div>
    </section>

    {{-- ===================== FILTER & SEARCH ===================== --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 pb-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 sm:p-5">
            <div class="flex flex-col sm:flex-row gap-3">

                {{-- Search --}}
                <div class="relative flex-1">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 105 11a6 6 0 0012 0z"/>
                    </svg>
                    <input type="text" id="searchInput" placeholder="Cari nomor kasus, judul, atau lokasi..."
                        class="w-full pl-9 pr-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-300 transition-colors">
                </div>

                {{-- Filter Status --}}
                <select id="filterStatus"
                    class="px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-300 bg-white text-gray-700 transition-colors">
                    <option value="">Semua Status</option>
                    @foreach($cases->pluck('status')->filter()->unique('name') as $s)
                    <option value="{{ $s->name ?? '' }}">{{ $s->name ?? '' }}</option>
                    @endforeach
                </select>

                {{-- Filter Kategori --}}
                <select id="filterCategory"
                    class="px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-300 bg-white text-gray-700 transition-colors">
                    <option value="">Semua Kategori</option>
                    @foreach($cases->pluck('category')->filter()->unique('name') as $cat)
                    <option value="{{ $cat->name ?? '' }}">{{ $cat->name ?? '' }}</option>
                    @endforeach
                </select>

                {{-- Toggle View --}}
                <div class="flex rounded-lg border border-gray-200 overflow-hidden flex-shrink-0">
                    <button id="btnMap" onclick="setView('map')"
                        class="px-3 py-2.5 text-sm font-medium bg-gray-900 text-white transition-colors flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                        </svg>
                        Peta
                    </button>
                    <button id="btnList" onclick="setView('list')"
                        class="px-3 py-2.5 text-sm font-medium bg-white text-gray-600 hover:bg-gray-50 transition-colors flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                        </svg>
                        Tabel
                    </button>
                </div>

            </div>

            {{-- Active filter label --}}
            <div class="mt-3 flex items-center gap-2 text-xs text-gray-400">
                <span>Menampilkan:</span>
                <span id="filterLabel" class="font-semibold text-gray-600">Semua Kasus</span>
                <span id="caseCount" class="bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full font-medium"></span>
            </div>
        </div>
    </section>

    {{-- ===================== MAP + SIDEBAR ===================== --}}
    <section id="viewMap" class="max-w-7xl mx-auto px-4 sm:px-6 pb-8">
        <div class="bg-white rounded-xl shadow-sm overflow-hidden border border-gray-100">
            <div class="flex flex-col lg:flex-row" style="height: 600px;">

                {{-- Sidebar list --}}
                <div class="w-full lg:w-72 border-b lg:border-b-0 lg:border-r border-gray-100 flex flex-col overflow-hidden"
                     style="height: 200px; min-height: 200px;" id="sidebarWrapper">
                    <div class="px-4 py-3 border-b border-gray-100 bg-gray-50 flex-shrink-0">
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Kasus di Tampilan Saat Ini</p>
                        <p class="text-xs text-gray-400 mt-0.5">Klik untuk zoom ke lokasi</p>
                    </div>
                    <div id="caseList" class="overflow-y-auto flex-1 divide-y divide-gray-50">
                        {{-- Filled by JS --}}
                    </div>
                </div>

                {{-- Map --}}
                <div class="flex-1 relative">
                    {{-- Legenda --}}
                    <div class="absolute bottom-4 left-4 z-[1000] bg-white rounded-xl shadow-md border border-gray-100 p-3 text-xs">
                        <p class="font-semibold text-gray-600 mb-2 uppercase tracking-wide text-[10px]">Legenda Status</p>
                        <div class="space-y-1.5">
                            <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-yellow-400 flex-shrink-0"></span><span class="text-gray-600">Aktif / Investigasi</span></div>
                            <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-blue-500 flex-shrink-0"></span><span class="text-gray-600">Persidangan</span></div>
                            <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-green-500 flex-shrink-0"></span><span class="text-gray-600">Selesai / Divonis</span></div>
                            <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-gray-400 flex-shrink-0"></span><span class="text-gray-600">Ditutup / Lainnya</span></div>
                        </div>
                    </div>
                    <div id="map" class="w-full h-full"></div>
                </div>

            </div>
        </div>
    </section>

    {{-- ===================== TABLE VIEW ===================== --}}
    <section id="viewList" class="max-w-7xl mx-auto px-4 sm:px-6 pb-8 hidden">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h2 class="font-semibold text-gray-800 text-sm">Daftar Kasus</h2>
                <span id="tableCount" class="text-xs bg-gray-100 text-gray-500 px-2.5 py-1 rounded-full font-medium"></span>
            </div>

            {{-- Mobile: card list --}}
            <div id="mobileCardList" class="divide-y divide-gray-50 sm:hidden"></div>

            {{-- Desktop: table --}}
            <div class="hidden sm:block overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-xs text-gray-400 uppercase tracking-wider">
                        <tr>
                            <th class="text-left px-5 py-3 font-semibold">No. Kasus</th>
                            <th class="text-left px-5 py-3 font-semibold">Judul</th>
                            <th class="text-left px-5 py-3 font-semibold">Kategori</th>
                            <th class="text-left px-5 py-3 font-semibold">Status</th>
                            <th class="text-left px-5 py-3 font-semibold">Lokasi</th>
                            <th class="text-left px-5 py-3 font-semibold">Tanggal</th>
                            <th class="px-5 py-3"></th>
                        </tr>
                    </thead>
                    <tbody id="caseTableBody" class="divide-y divide-gray-50 text-gray-700">
                        {{-- Filled by JS --}}
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="px-5 py-4 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-3">
                <p id="paginationInfo" class="text-xs text-gray-400"></p>
                <div class="flex gap-2">
                    <button id="btnPrev" onclick="changePage(-1)"
                        class="px-3 py-1.5 text-xs border border-gray-200 rounded-lg hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed transition-colors">
                        ← Sebelumnya
                    </button>
                    <button id="btnNext" onclick="changePage(1)"
                        class="px-3 py-1.5 text-xs border border-gray-200 rounded-lg hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed transition-colors">
                        Berikutnya →
                    </button>
                </div>
            </div>
        </div>
    </section>

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ========================
    // DATA
    // ========================
    const allCases = @json($cases);
    let filteredCases = [...allCases];
    let currentPage = 1;
    const perPage = 10;
    let mapMarkers = [];
    let currentView = 'map';

    // Status color mapping
    function getStatusColor(statusKey) {
        const active  = ['investigation','penyelidikan','penyidikan','prosecution','open','verified'];
        const trial   = ['trial'];
        const done    = ['executed','completed','vonis','Berkekuatan hukum tetap'];
        if (active.includes(statusKey))  return '#eab308'; // yellow
        if (trial.includes(statusKey))   return '#3b82f6'; // blue
        if (done.includes(statusKey))    return '#22c55e'; // green
        return '#9ca3af'; // gray
    }

    function getStatusColorHex(statusKey) {
        return getStatusColor(statusKey);
    }

    function createColoredIcon(color) {
        return L.divIcon({
            className: '',
            html: `<div style="
                width:14px;height:14px;border-radius:50%;
                background:${color};border:2.5px solid white;
                box-shadow:0 1px 4px rgba(0,0,0,0.3);
            "></div>`,
            iconSize: [14, 14],
            iconAnchor: [7, 7],
            popupAnchor: [0, -10]
        });
    }

    // ========================
    // MAP INIT
    // ========================
    var map = L.map('map', { center: [-2.5, 118], zoom: 5, scrollWheelZoom: true });
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors', maxZoom: 19
    }).addTo(map);

    var clusterGroup = L.markerClusterGroup({
        disableClusteringAtZoom: 12,
        spiderfyOnMaxZoom: true,
        showCoverageOnHover: false
    });
    map.addLayer(clusterGroup);

    // ========================
    // RENDER MAP MARKERS
    // ========================
    function renderMarkers(data) {
        clusterGroup.clearLayers();
        mapMarkers = [];

        data.forEach(function(c) {
            if (!c.latitude || !c.longitude) return;
            const lat = parseFloat(c.latitude);
            const lng = parseFloat(c.longitude);
            if (isNaN(lat) || isNaN(lng)) return;

            const statusKey   = c.status?.key ?? '';
            const statusName  = c.status?.name ?? '-';
            const catName     = c.category?.name ?? '-';
            const color       = getStatusColorHex(statusKey);
            const icon        = createColoredIcon(color);

            const marker = L.marker([lat, lng], { icon });

            const detailUrl = c.slug ? `/kasus/${c.slug}` : '#';

            marker.bindPopup(`
                <div style="min-width:200px;font-family:inherit">
                    <div style="font-weight:700;font-size:13px;color:#111;margin-bottom:6px">
                        ${escapeHtml(c.case_number ?? '')}
                    </div>
                    ${c.title ? `<div style="font-size:12px;color:#374151;margin-bottom:6px;line-height:1.4">${escapeHtml(c.title)}</div>` : ''}
                    <div style="font-size:11px;color:#6b7280;margin-bottom:3px">
                        📁 <strong>Kategori:</strong> ${escapeHtml(catName)}
                    </div>
                    <div style="font-size:11px;margin-bottom:3px">
                        <span style="display:inline-block;padding:2px 8px;border-radius:99px;font-size:10px;font-weight:600;background:${color}22;color:${color}">
                            ${escapeHtml(statusName)}
                        </span>
                    </div>
                    ${c.event_date ? `<div style="font-size:11px;color:#6b7280;margin-bottom:8px">📅 ${escapeHtml(c.event_date)}</div>` : ''}
                    ${c.province ? `<div style="font-size:11px;color:#6b7280;margin-bottom:8px">📍 ${escapeHtml(c.province)}</div>` : ''}
                    <a href="${detailUrl}" style="display:inline-block;margin-top:4px;padding:5px 12px;background:#111;color:#fff;border-radius:6px;font-size:11px;font-weight:600;text-decoration:none">
                        Lihat Detail →
                    </a>
                </div>
            `);

            mapMarkers.push({ marker, caseData: c });
            clusterGroup.addLayer(marker);
        });

        renderSidebar(data);
    }

    // ========================
    // SIDEBAR LIST
    // ========================
    function renderSidebar(data) {
        const list = document.getElementById('caseList');
        if (!data.length) {
            list.innerHTML = '<p class="text-xs text-gray-400 italic p-4 text-center">Tidak ada kasus ditemukan</p>';
            return;
        }
        list.innerHTML = data.slice(0, 50).map(c => {
            const color = getStatusColorHex(c.status?.key ?? '');
            return `<div class="px-4 py-3 hover:bg-gray-50 cursor-pointer transition-colors text-xs"
                        onclick="zoomToCase(${c.latitude}, ${c.longitude})">
                <div class="font-semibold text-gray-800 truncate">${escapeHtml(c.case_number ?? '')}</div>
                ${c.title ? `<div class="text-gray-500 truncate mt-0.5">${escapeHtml(c.title)}</div>` : ''}
                <div class="flex items-center gap-1.5 mt-1.5">
                    <span style="background:${color}" class="w-2 h-2 rounded-full flex-shrink-0"></span>
                    <span class="text-gray-400">${escapeHtml(c.status?.name ?? '-')}</span>
                    ${c.province ? `<span class="text-gray-300">·</span><span class="text-gray-400 truncate">${escapeHtml(c.province)}</span>` : ''}
                </div>
            </div>`;
        }).join('');
    }

    // ========================
    // TABLE RENDER
    // ========================
    function renderTable(data) {
        const start  = (currentPage - 1) * perPage;
        const paged  = data.slice(start, start + perPage);
        const total  = data.length;

        document.getElementById('tableCount').textContent = `${total} kasus`;
        document.getElementById('paginationInfo').textContent =
            `Menampilkan ${start + 1}–${Math.min(start + perPage, total)} dari ${total} kasus`;
        document.getElementById('btnPrev').disabled = currentPage === 1;
        document.getElementById('btnNext').disabled = start + perPage >= total;

        // Desktop table
        document.getElementById('caseTableBody').innerHTML = paged.map(c => {
            const color = getStatusColorHex(c.status?.key ?? '');
            const detailUrl = c.slug ? `/kasus/${c.slug}` : '#';
            return `<tr class="hover:bg-gray-50 transition-colors">
                <td class="px-5 py-3 font-mono text-xs text-gray-500 whitespace-nowrap">${escapeHtml(c.case_number ?? '-')}</td>
                <td class="px-5 py-3 max-w-[200px]">
                    <p class="font-medium text-gray-800 truncate text-xs">${escapeHtml(c.title ?? '-')}</p>
                </td>
                <td class="px-5 py-3 text-xs text-gray-500 whitespace-nowrap">${escapeHtml(c.category?.name ?? '-')}</td>
                <td class="px-5 py-3 whitespace-nowrap">
                    <span style="background:${color}22;color:${color}" class="px-2.5 py-1 rounded-full text-[10px] font-bold">
                        ${escapeHtml(c.status?.name ?? '-')}
                    </span>
                </td>
                <td class="px-5 py-3 text-xs text-gray-500 whitespace-nowrap">${escapeHtml(c.province ?? '-')}</td>
                <td class="px-5 py-3 text-xs text-gray-400 whitespace-nowrap">${escapeHtml(c.event_date ?? '-')}</td>
                <td class="px-5 py-3 text-right">
                    <a href="${detailUrl}" class="text-xs text-blue-600 hover:underline font-medium whitespace-nowrap">Lihat →</a>
                </td>
            </tr>`;
        }).join('');

        // Mobile cards
        document.getElementById('mobileCardList').innerHTML = paged.map(c => {
            const color = getStatusColorHex(c.status?.key ?? '');
            const detailUrl = c.slug ? `/kasus/${c.slug}` : '#';
            return `<div class="px-4 py-4">
                <div class="flex items-start justify-between gap-2">
                    <div class="min-w-0">
                        <p class="font-semibold text-gray-800 text-sm leading-snug">${escapeHtml(c.title ?? '-')}</p>
                        <p class="text-xs text-gray-400 font-mono mt-0.5">${escapeHtml(c.case_number ?? '')}</p>
                    </div>
                    <span style="background:${color}22;color:${color}" class="px-2 py-0.5 rounded-full text-[10px] font-bold flex-shrink-0">
                        ${escapeHtml(c.status?.name ?? '-')}
                    </span>
                </div>
                <div class="flex items-center gap-3 mt-2 text-xs text-gray-400">
                    <span>📁 ${escapeHtml(c.category?.name ?? '-')}</span>
                    ${c.province ? `<span>📍 ${escapeHtml(c.province)}</span>` : ''}
                    ${c.event_date ? `<span>📅 ${escapeHtml(c.event_date)}</span>` : ''}
                </div>
                <a href="${detailUrl}" class="inline-block mt-2 text-xs text-blue-600 hover:underline font-medium">Lihat Detail →</a>
            </div>`;
        }).join('');
    }

    // ========================
    // FILTER LOGIC
    // ========================
    function applyFilter() {
        const search   = document.getElementById('searchInput').value.toLowerCase();
        const status   = document.getElementById('filterStatus').value;
        const category = document.getElementById('filterCategory').value;

        filteredCases = allCases.filter(c => {
            const matchSearch = !search ||
                (c.case_number ?? '').toLowerCase().includes(search) ||
                (c.title ?? '').toLowerCase().includes(search) ||
                (c.province ?? '').toLowerCase().includes(search);
            const matchStatus   = !status   || (c.status?.name ?? '') === status;
            const matchCategory = !category || (c.category?.name ?? '') === category;
            return matchSearch && matchStatus && matchCategory;
        });

        currentPage = 1;

        // Update label
        const parts = [];
        if (search)   parts.push(`"${search}"`);
        if (status)   parts.push(status);
        if (category) parts.push(category);
        document.getElementById('filterLabel').textContent = parts.length ? parts.join(' · ') : 'Semua Kasus';
        document.getElementById('caseCount').textContent   = `${filteredCases.length} kasus`;

        renderMarkers(filteredCases);
        renderTable(filteredCases);
    }

    // ========================
    // VIEW TOGGLE
    // ========================
    window.setView = function(view) {
        currentView = view;
        document.getElementById('viewMap').classList.toggle('hidden', view !== 'map');
        document.getElementById('viewList').classList.toggle('hidden', view !== 'list');
        document.getElementById('btnMap').className  = view === 'map'
            ? 'px-3 py-2.5 text-sm font-medium bg-gray-900 text-white transition-colors flex items-center gap-1.5'
            : 'px-3 py-2.5 text-sm font-medium bg-white text-gray-600 hover:bg-gray-50 transition-colors flex items-center gap-1.5';
        document.getElementById('btnList').className = view === 'list'
            ? 'px-3 py-2.5 text-sm font-medium bg-gray-900 text-white transition-colors flex items-center gap-1.5'
            : 'px-3 py-2.5 text-sm font-medium bg-white text-gray-600 hover:bg-gray-50 transition-colors flex items-center gap-1.5';
        if (view === 'map') setTimeout(() => map.invalidateSize(), 100);
    };

    // ========================
    // ZOOM TO CASE
    // ========================
    window.zoomToCase = function(lat, lng) {
        if (!lat || !lng) return;
        map.setView([parseFloat(lat), parseFloat(lng)], 14);
        setView('map');
    };

    // ========================
    // PAGINATION
    // ========================
    window.changePage = function(dir) {
        const maxPage = Math.ceil(filteredCases.length / perPage);
        currentPage = Math.max(1, Math.min(maxPage, currentPage + dir));
        renderTable(filteredCases);
        document.getElementById('viewList').scrollIntoView({ behavior: 'smooth', block: 'start' });
    };

    // ========================
    // ESCAPE HTML
    // ========================
    function escapeHtml(text) {
        const map = { '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;' };
        return String(text).replace(/[&<>"']/g, m => map[m]);
    }

    // ========================
    // EVENT LISTENERS
    // ========================
    document.getElementById('searchInput').addEventListener('input', applyFilter);
    document.getElementById('filterStatus').addEventListener('change', applyFilter);
    document.getElementById('filterCategory').addEventListener('change', applyFilter);

    // ========================
    // INIT
    // ========================
    applyFilter();
    document.getElementById('caseCount').textContent = `${allCases.length} kasus`;
});
</script>
@endpush