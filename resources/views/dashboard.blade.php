<x-internal-layout>

    {{-- ================= COMMAND STRIP ================= --}}
    <section class="console-grid text-white">
        <div class="max-w-7xl mx-auto px-6 py-8">
            <div class="cms-rise flex flex-col md:flex-row md:items-end md:justify-between gap-5">
                <div>
                    <div class="cms-eyebrow on-ink">Command Overview</div>
                    <h1 class="font-display text-[34px] leading-[1.1] font-bold tracking-tight mt-2">
                        Case Tracking Console
                    </h1>
                    <p class="text-white/65 text-sm mt-2 max-w-xl">
                        Operasional harian Auriga CTIS — pantau kasus, laporan masuk, dan distribusi publikasi.
                    </p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('charts.sync') }}" class="cms-btn cms-btn-ghost" style="border-color:rgba(255,255,255,0.22);color:#fff;">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        Sync Charts
                    </a>
                    <a href="{{ route('dashboard.export.csv') }}" class="cms-btn" style="background:var(--leaf);color:var(--ink);border:1px solid var(--leaf);">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Export CSV
                    </a>
                    <a href="{{ route('dashboard.export.excel') }}" class="cms-btn cms-btn-ghost" style="border-color:rgba(255,255,255,0.22);color:#fff;">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Excel
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- ================= STAT TILES ================= --}}
    <div class="max-w-7xl mx-auto px-6 -mt-5 relative z-10">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="cms-tile cms-rise" style="animation-delay:.04s">
                <div class="cms-tile-glyph">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <div class="cms-tile-meta">Total Cases</div>
                <div class="cms-tile-num">{{ number_format($totalCases) }}</div>
                <div class="cms-tile-foot">All cases in system</div>
            </div>

            <div class="cms-tile is-ok cms-rise" style="animation-delay:.10s">
                <div class="cms-tile-glyph">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                </div>
                <div class="cms-tile-meta">New Reports · Today</div>
                <div class="cms-tile-num">{{ number_format($newReportsToday) }}</div>
                <div class="cms-tile-foot">Created today</div>
            </div>

            <div class="cms-tile is-ok cms-rise" style="animation-delay:.16s">
                <div class="cms-tile-glyph">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                </div>
                <div class="cms-tile-meta">Published</div>
                <div class="cms-tile-num">{{ number_format($publishedCases) }}</div>
                <div class="cms-tile-foot">Public cases</div>
            </div>

            <div class="cms-tile is-warn cms-rise" style="animation-delay:.22s">
                <div class="cms-tile-glyph">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59"/></svg>
                </div>
                <div class="cms-tile-meta">Unpublished</div>
                <div class="cms-tile-num">{{ number_format($unpublishedCases) }}</div>
                <div class="cms-tile-foot">Private cases</div>
            </div>
        </div>
    </div>

    {{-- ================= CHARTS ================= --}}
    <div class="max-w-7xl mx-auto px-6 mt-8 space-y-4">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <div class="cms-panel cms-rise" style="animation-delay:.28s">
                <div class="cms-panel-head">
                    <div>
                        <div class="cms-eyebrow">Distribution</div>
                        <h3 class="cms-panel-title mt-1">Cases per Category</h3>
                    </div>
                </div>
                <div class="cms-panel-body" style="position:relative;height:300px;">
                    <canvas id="categoryChart"></canvas>
                </div>
            </div>

            <div class="cms-panel cms-rise" style="animation-delay:.34s">
                <div class="cms-panel-head">
                    <div>
                        <div class="cms-eyebrow">Composition</div>
                        <h3 class="cms-panel-title mt-1">Case Status Distribution</h3>
                    </div>
                </div>
                <div class="cms-panel-body" style="position:relative;height:300px;">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
        </div>

        <div class="cms-panel cms-rise" style="animation-delay:.40s">
            <div class="cms-panel-head">
                <div>
                    <div class="cms-eyebrow">Inflow · Last 30 Days</div>
                    <h3 class="cms-panel-title mt-1">Reports Over Time</h3>
                </div>
            </div>
            <div class="cms-panel-body" style="position:relative;height:300px;">
                <canvas id="timeChart"></canvas>
            </div>
        </div>
    </div>

    {{-- ================= MAP ================= --}}
    <div class="max-w-7xl mx-auto px-6 mt-4">
        <div class="cms-panel cms-rise" style="animation-delay:.46s">
            <div class="cms-panel-head">
                <div>
                    <div class="cms-eyebrow">Geospatial</div>
                    <h3 class="cms-panel-title mt-1">Case Locations</h3>
                    <p class="cms-panel-sub">All cases with location data (published and unpublished)</p>
                </div>
            </div>
            <div id="caseMap" class="w-full" style="height:500px;"></div>
        </div>
    </div>

    {{-- ================= LATEST CASES ================= --}}
    <div class="max-w-7xl mx-auto px-6 mt-4 mb-20">
        <div class="cms-panel cms-rise" style="animation-delay:.52s">
            <div class="cms-panel-head">
                <div>
                    <div class="cms-eyebrow">Recent Activity</div>
                    <h3 class="cms-panel-title mt-1">Latest Cases</h3>
                    <p class="cms-panel-sub">Most recently created cases</p>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="cms-table">
                    <thead>
                        <tr>
                            <th>Case Number</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th>Event Date</th>
                            <th>Public?</th>
                            <th class="text-right" style="text-align:right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($latestCases as $case)
                        <tr>
                            <td class="num">{{ $case->case_number }}</td>
                            <td>{{ $case->category?->translation('id')?->name ?? $case->category?->slug ?? 'Uncategorized' }}</td>
                            <td>
                                <x-internal.badge
                                    variant="{{ $case->status?->key === 'new' || $case->status?->key === 'unverified' ? 'new' : ($case->status?->key === 'investigation' || $case->status?->key === 'in_progress' ? 'investigation' : ($case->is_public ? 'published' : 'default')) }}">
                                    {{ $case->status?->name ?? 'Unknown' }}
                                </x-internal.badge>
                            </td>
                            <td>{{ $case->event_date ? \Carbon\Carbon::parse($case->event_date)->format('M d, Y') : 'N/A' }}</td>
                            <td>
                                @if($case->is_public)
                                    <span class="cms-pill cms-pill-ok"><span class="dot"></span>Publik</span>
                                @else
                                    <span class="cms-pill cms-pill-default"><span class="dot"></span>Privat</span>
                                @endif
                            </td>
                            <td style="text-align:right;">
                                <div class="flex justify-end items-center gap-4">
                                    <a href="{{ route('case.detail', $case->id) }}" class="text-xs font-semibold link">Review</a>
                                    @can('case.update', $case)
                                    @if(!$case->is_public)
                                    <a href="{{ route('case.detail', $case->id) }}" class="text-xs font-semibold" style="color:var(--ok);">Publish</a>
                                    @else
                                    <a href="{{ route('case.detail', $case->id) }}" class="text-xs font-semibold" style="color:var(--warn);">Unpublish</a>
                                    @endif
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" style="text-align:center;color:var(--muted);padding:40px;">No cases found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    @endpush

    @push('scripts')
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const INK   = '#0B1E07';
            const LEAF  = '#9BDB4D';
            const DEEP  = '#2F6C14';
            const BRAND = '#264c16';
            const WARN  = '#B5761A';
            const DANGER= '#B23A3A';
            const MUTED = '#8a9082';

            // ================= BAR CHART: Cases per Category =================
            const categoryData = @json($casesByCategory);
            new Chart(document.getElementById('categoryChart').getContext('2d'), {
                type: 'bar',
                data: {
                    labels: categoryData.map(item => item.category_name),
                    datasets: [{
                        label: 'Number of Cases',
                        data: categoryData.map(item => item.count),
                        backgroundColor: DEEP,
                        hoverBackgroundColor: LEAF,
                        borderRadius: 5,
                        borderSkipped: false,
                        maxBarThickness: 38
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, ticks: { stepSize: 1, color: MUTED, font: { family: 'JetBrains Mono', size: 10 } }, grid: { color: 'rgba(11,30,7,0.06)' } },
                        x: { ticks: { color: '#6b7268', font: { size: 11 } }, grid: { display: false } }
                    }
                }
            });

            // ================= PIE CHART: Case Status Distribution =================
            const statusData = @json($casesByStatus);
            new Chart(document.getElementById('statusChart').getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: statusData.map(item => item.status_name),
                    datasets: [{
                        data: statusData.map(item => item.count),
                        backgroundColor: [INK, LEAF, DEEP, BRAND, WARN, DANGER, MUTED],
                        borderColor: '#FFFFFF',
                        borderWidth: 3,
                        hoverOffset: 6
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false, cutout: '58%',
                    plugins: {
                        legend: { position: 'bottom', labels: { color: '#6b7268', font: { size: 11 }, boxWidth: 10, boxHeight: 10, usePointStyle: true, pointStyle: 'circle', padding: 14 } }
                    }
                }
            });

            // ================= LINE CHART: Reports Over Time =================
            const timeData = @json($reportsOverTime);
            const dates = [], counts = [];
            const today = new Date();
            for (let i = 29; i >= 0; i--) {
                const date = new Date(today);
                date.setDate(date.getDate() - i);
                const dateStr = date.toISOString().split('T')[0];
                dates.push(dateStr);
                const dataPoint = timeData.find(d => d.date === dateStr);
                counts.push(dataPoint ? dataPoint.count : 0);
            }
            new Chart(document.getElementById('timeChart').getContext('2d'), {
                type: 'line',
                data: {
                    labels: dates.map(d => new Date(d).toLocaleDateString('en-US', { month: 'short', day: 'numeric' })),
                    datasets: [{
                        label: 'Cases Created',
                        data: counts,
                        borderColor: DEEP,
                        backgroundColor: 'rgba(155,219,77,0.16)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 0,
                        pointHoverRadius: 4,
                        pointBackgroundColor: LEAF
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, ticks: { stepSize: 1, color: MUTED, font: { family: 'JetBrains Mono', size: 10 } }, grid: { color: 'rgba(11,30,7,0.06)' } },
                        x: { ticks: { color: '#6b7268', font: { size: 10 }, maxRotation: 0, autoSkip: true, maxTicksLimit: 8 }, grid: { display: false } }
                    }
                }
            });

            // ================= LEAFLET MAP =================
            const map = L.map('caseMap', { center: [-2.5, 118], zoom: 5, scrollWheelZoom: true });
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors', maxZoom: 19
            }).addTo(map);

            const casesWithLocation = @json($casesWithLocation);

            function getMarkerIcon(color) {
                return L.divIcon({
                    className: 'custom-marker',
                    html: `<div style="background-color:${color};width:16px;height:16px;border-radius:50%;border:3px solid #fff;box-shadow:0 2px 8px rgba(11,30,7,0.35);"></div>`,
                    iconSize: [16, 16], iconAnchor: [8, 8]
                });
            }

            casesWithLocation.forEach(function(caseItem) {
                if (!caseItem.latitude || !caseItem.longitude) return;
                const lat = parseFloat(caseItem.latitude);
                const lng = parseFloat(caseItem.longitude);
                if (isNaN(lat) || isNaN(lng)) return;

                const colors = { red: DANGER, yellow: WARN, green: DEEP, gray: MUTED };
                const markerColor = colors[caseItem.color] || colors.gray;

                const marker = L.marker([lat, lng], { icon: getMarkerIcon(markerColor) });

                let popupContent = '<div class="p-3" style="min-width:210px;font-family:Poppins,sans-serif;">';
                popupContent += '<div style="font-size:11px;letter-spacing:.18em;text-transform:uppercase;color:#8a9082;font-family:JetBrains Mono,monospace;">No. Kasus</div>';
                popupContent += '<div style="font-weight:700;color:#0B1E07;margin-bottom:10px;">' + escapeHtml(caseItem.case_number) + '</div>';
                popupContent += '<div style="font-size:13px;margin-bottom:2px;color:#6b7268">Status: <span style="color:#0B1E07;font-weight:600">' + escapeHtml(caseItem.status_name) + '</span></div>';
                popupContent += '<div style="font-size:13px;margin-bottom:12px;color:#6b7268">Category: <span style="color:#0B1E07;font-weight:600">' + escapeHtml(caseItem.category_name) + '</span></div>';
                popupContent += '<a href="/cms/cases/' + caseItem.id + '/detail" style="display:inline-flex;align-items:center;gap:6px;padding:7px 12px;background:#0B1E07;color:#fff;border-radius:8px;font-size:11px;font-weight:600;letter-spacing:.1em;text-transform:uppercase;text-decoration:none;">Review Case</a>';
                popupContent += '</div>';

                marker.bindPopup(popupContent);
                marker.addTo(map);
            });

            function escapeHtml(text) {
                const map = { '&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#039;' };
                return String(text).replace(/[&<>"']/g, function(m) { return map[m]; });
            }
        });
    </script>

    <style>
        .custom-marker { background: transparent !important; border: none !important; }
    </style>
    @endpush

</x-internal-layout>