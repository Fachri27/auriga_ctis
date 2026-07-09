@extends('layouts.main')

@section('content')
    @include('front.components.peta')
    @include('front.components.card', ['limit' => 3, 'offset' => 0])

    {{-- ===================== HERO STATS ===================== --}}
    <div class="bg-white border-b border-gray-100 py-10">
        <div class="max-w-full mx-auto px-4 sm:px-6">

            <div class="text-center mb-8">
                <p class="text-xs font-semibold tracking-[0.25em] uppercase text-gray-400 mb-1">Data Real-Time</p>
                <h2 class="text-2xl font-bold text-gray-800">Kasus dalam Angka</h2>
            </div>

            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="text-center bg-gray-300 rounded-2xl p-5 border border-gray-100">
                    <p class="text-3xl sm:text-4xl font-bold text-gray-900">{{ $totalCases ?? '—' }}</p>
                    <p class="text-[18px] text-gray-500 mt-1 font-bold">Total Kasus Terdaftar</p>
                    <p class="text-sm text-gray-500 mt-1">Sejak sistem diluncurkan</p>
                </div>
                <div class="text-center bg-yellow-300 rounded-2xl p-5 border border-yellow-100">
                    <p class="text-3xl sm:text-4xl font-bold text-yellow-600">{{ $activeCases ?? '—' }}</p>
                    <p class="text-[18px] text-gray-500 mt-1 font-bold">Kasus Aktif</p>
                    <p class="text-sm text-gray-500 mt-1">Sedang dalam proses hukum</p>
                </div>
                <div class="text-center bg-green-300 rounded-2xl p-5 border border-green-100">
                    <p class="text-3xl sm:text-4xl font-bold text-green-600">{{ $completedCases ?? '—' }}</p>
                    <p class="text-[18px] text-gray-500 mt-1 font-bold">Kasus Selesai / Divonis</p>
                    <p class="text-sm text-gray-500 mt-1">Proses hukum sudah final</p>
                </div>
                <div class="text-center bg-blue-300 rounded-2xl p-5 border border-blue-100">
                    <p class="text-3xl sm:text-4xl font-bold text-blue-600">{{ $provinceCovered ?? '—' }}</p>
                    <p class="text-[18px] text-gray-500 mt-1 font-bold">Provinsi Terdampak</p>
                    <p class="text-sm text-gray-500 mt-1">Di seluruh Indonesia</p>
                </div>
            </div>

            <p class="text-center text-xs text-gray-400 mt-5">
                🕐 Data terakhir diperbarui: <strong>{{ now()->format('d M Y, H:i') }} WIB</strong>
                &nbsp;·&nbsp; Sumber: KPK, Kejaksaan Agung, Kepolisian RI
            </p>
        </div>
    </div>

    {{-- ===================== STATISTIK + PENJELASAN ===================== --}}
    <!--<div
        class="relative w-full bg-gradient-to-br from-[#002E36] via-[#003C45] to-[#002A31] py-20 sm:py-28 text-white overflow-hidden">
        <div class="absolute -top-32 -left-32 w-96 h-96 bg-teal-400/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-32 -right-32 w-96 h-96 bg-emerald-400/10 rounded-full blur-3xl pointer-events-none">
        </div>

        <div class="relative max-w-6xl mx-auto px-4 sm:px-6 text-center">
            <div class="text-sm tracking-[0.3em] text-white/60 uppercase mb-6">
                {{ __('messages.public_transparency') }}
            </div>
            <h2 class="text-3xl sm:text-4xl md:text-5xl font-semibold leading-tight">
                {{ __('messages.transparency') }} <span class="text-teal-300">{{ __('messages.case_statistics') }}</span>
            </h2>
            <p class="mt-6 text-base sm:text-lg text-white/80 max-w-3xl mx-auto leading-relaxed">
                {{ __('messages.explore') }}
            </p>
            <div class="mt-8 flex justify-center">
                <div class="w-24 h-[2px] bg-gradient-to-r from-transparent via-teal-400 to-transparent"></div>
            </div>
        </div>
    </div>-->

    <!--<div class="bg-white py-16 sm:py-20">
        <div class="max-w-full mx-auto px-4 sm:px-6">

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

                {{-- Category Chart --}}
                <div class="bg-gray-50 rounded-2xl shadow-sm p-5 sm:p-6">
                    <h3 class="text-base font-semibold mb-1">{{ __('messages.case_category') }}</h3>
                    <p class="text-xs text-gray-400 mb-5" id="categoryInsight">Memuat data...</p>
                    <div id="categoryChart" style="height: 380px;"></div>
                </div>

                {{-- Status Chart --}}
                <div class="bg-gray-50 rounded-2xl shadow-sm p-5 sm:p-6">
                    <h3 class="text-base font-semibold mb-1">{{ __('messages.case_status') }}</h3>
                    <p class="text-xs text-gray-400 mb-5" id="statusInsight">Memuat data...</p>
                    <div id="statusChart" style="height: 380px;"></div>
                </div>

            </div>

            {{-- Monthly Chart --}}
            <div class="bg-gray-50 rounded-2xl shadow-sm p-5 sm:p-6 mt-8">
                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-2 mb-1">
                    <h3 class="text-base font-semibold">{{ __('messages.case_trend') }}</h3>
                </div>
                <p class="text-xs text-gray-400 mb-5" id="monthlyInsight">Memuat data...</p>
                <div id="monthlyChart" style="height: 380px;"></div>
            </div>

            {{-- Transparansi & Unduh --}}
            {{-- <div class="mt-8 grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-gray-50 rounded-2xl p-5 border border-gray-100 flex items-start gap-3">
                <span class="text-2xl">📋</span>
                <div>
                    <p class="text-sm font-semibold text-gray-800">Sumber Data</p>
                    <p class="text-xs text-gray-500 mt-1 leading-relaxed">Data bersumber dari KPK, Kejaksaan Agung, dan Kepolisian RI yang diverifikasi tim kami.</p>
                </div>
            </div>
            <div class="bg-gray-50 rounded-2xl p-5 border border-gray-100 flex items-start gap-3">
                <span class="text-2xl">🔄</span>
                <div>
                    <p class="text-sm font-semibold text-gray-800">Pembaruan Rutin</p>
                    <p class="text-xs text-gray-500 mt-1 leading-relaxed">Data diperbarui secara berkala mengikuti perkembangan kasus dari lembaga resmi.</p>
                </div>
            </div>
            <div class="bg-gray-50 rounded-2xl p-5 border border-gray-100 flex items-start gap-3">
                <span class="text-2xl">📥</span>
                <div>
                    <p class="text-sm font-semibold text-gray-800">Unduh Data</p>
                    <p class="text-xs text-gray-500 mt-1 leading-relaxed">Tersedia untuk jurnalis dan peneliti.</p>
                    <a href="#" class="inline-block mt-2 text-xs text-blue-600 hover:underline font-medium">Unduh CSV →</a>
                </div>
            </div>
        </div> --}}

        </div>
    </div>-->

    {{-- ===================== CHART DATA (ECHARTS) ===================== --}}
    @if (!empty($publicCharts))
    <div x-data="{ view: 'chart', switchView(v) { this.view = v; if (v === 'chart') setTimeout(function() { pubResizeAll(); }, 100); } }" class="bg-[#E6F2DD] py-16 border-t border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6">
            <div class="text-center mb-10">
                <p class="text-xs font-semibold tracking-[0.25em] uppercase text-gray-400 mb-2">Statistik Perkara</p>
                <h2 class="text-2xl font-bold text-gray-800">Indeksasi Putusan Perkara</h2>
                <div class="flex flex-col sm:flex-row items-center justify-between mt-4 gap-3">
                    <div class="inline-flex bg-gray-200 rounded-lg p-1">
                        <button @click="switchView('chart')"
                            class="px-4 py-1.5 text-sm font-medium rounded-md transition-colors"
                            :class="view === 'chart' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700'">
                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/></svg>
                            Chart
                        </button>
                        <button @click="switchView('table')"
                            class="px-4 py-1.5 text-sm font-medium rounded-md transition-colors"
                            :class="view === 'table' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700'">
                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                            Table
                        </button>
                    </div>
                    @if (!empty($filterOptions))
                    <form id="filter-form" method="GET" action="{{ url()->current() }}" class="flex flex-wrap items-end gap-2">
                        <div>
                            <select name="tahun" onchange="applyFilters()" class="text-xs border border-gray-200 rounded-lg px-2 py-1.5 bg-white">
                                <option value="">Tahun</option>
                                @foreach ($filterOptions['tahun'] as $t)
                                <option value="{{ $t }}" {{ (string) $filterTahun === (string) $t ? 'selected' : '' }}>{{ $t }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <select name="klasifikasi" onchange="applyFilters()" class="text-xs border border-gray-200 rounded-lg px-2 py-1.5 bg-white max-w-[140px]">
                                <option value="">Klasifikasi</option>
                                @foreach ($filterOptions['klasifikasi'] as $k)
                                <option value="{{ $k }}" {{ $filterKlasifikasi === $k ? 'selected' : '' }}>{{ $k }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <select name="pulau" onchange="applyFilters()" class="text-xs border border-gray-200 rounded-lg px-2 py-1.5 bg-white">
                                <option value="">Pulau</option>
                                @foreach ($filterOptions['pulau'] as $p)
                                <option value="{{ $p }}" {{ $filterPulau === $p ? 'selected' : '' }}>{{ $p }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div id="reset-filter-container" @if (!($filterTahun || $filterKlasifikasi || $filterPulau)) style="display:none" @endif>
                            <a href="#" onclick="resetFilters(); return false;" class="text-xs text-blue-600 hover:underline whitespace-nowrap">Reset</a>
                        </div>
                    </form>
                    @endif
                </div>
            </div>

            {{-- CHART VIEW --}}
            <div x-show="view === 'chart'" class="max-w-7xl mx-auto space-y-6">

            @php
                $byTitle = fn($t) => collect($kpiData ?? [])->firstWhere('title', $t);
            @endphp
            <div id="kpi-container" class="max-w-7xl mx-auto px-4 sm:px-6 mb-4 space-y-6">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    @foreach (['Perkara', 'Terdakwa', 'Pengadilan'] as $t)
                    @php $k = $byTitle($t); @endphp
                    @if ($k)
                    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 text-center hover:shadow-md transition-shadow">
                        <div class="text-lg font-bold text-gray-900">{{ $k['display'] }}</div>
                        <div class="text-xs text-gray-500 mt-0.5">{{ $k['title'] }}</div>
                    </div>
                    @endif
                    @endforeach
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div>
                        <h4 class="text-sm font-semibold text-gray-700 mb-2 text-center">Subjek Hukum</h4>
                        <div class="grid grid-cols-2 gap-3">
                            @foreach (['Perorangan', 'Korporasi'] as $t)
                            @php $k = $byTitle($t); @endphp
                            @if ($k)
                            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 text-center hover:shadow-md transition-shadow">
                                <div class="text-lg font-bold text-gray-900">{{ $k['display'] }}</div>
                                <div class="text-xs text-gray-500 mt-0.5">{{ $k['title'] }}</div>
                            </div>
                            @endif
                            @endforeach
                        </div>
                    </div>
                    <div>
                        <h4 class="text-sm font-semibold text-gray-700 mb-2 text-center">Vonis Putusan</h4>
                        <div class="grid grid-cols-3 gap-3">
                            @foreach (['Bebas', 'Lepas', 'Bersalah'] as $t)
                            @php $vn = $byTitle('Vonis ' . $t); @endphp
                            @if ($vn)
                            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 text-center hover:shadow-md transition-shadow">
                                <div class="text-lg font-bold text-gray-900">{{ $vn['display'] }}</div>
                                <div class="text-xs text-gray-500 mt-0.5">{{ $vn['title'] }}</div>
                            </div>
                            @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

                <div id="charts-container">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6" id="charts-row-1">
                    @foreach (array_slice($publicCharts ?? [], 0, 2) as $ch)
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow">
                        <div class="px-5 py-4 border-b border-gray-50">
                            <h3 class="text-sm font-semibold text-gray-800">{{ $ch['title'] }}</h3>
                        </div>
                        <div wire:ignore>
                            <div class="pub-echart" id="pub-{{ $ch['id'] }}"
                                data-chart='{{ json_encode($ch['data']) }}'
                                data-type="{{ $ch['type'] }}"
                                style="height:400px;width:100%"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6" id="charts-row-2">
                    @foreach (array_slice($publicCharts ?? [], 2) as $ch)
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow">
                        <div class="px-5 py-4 border-b border-gray-50">
                            <h3 class="text-sm font-semibold text-gray-800">{{ $ch['title'] }}</h3>
                        </div>
                        <div wire:ignore>
                            <div class="pub-echart" id="pub-{{ $ch['id'] }}"
                                data-chart='{{ json_encode($ch['data']) }}'
                                data-type="{{ $ch['type'] }}"
                                style="height:350px;width:100%"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            </div>

            {{-- TABLE VIEW --}}
            <div x-show="view === 'table'" x-cloak class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-2 gap-6" id="tables-container">
                @forelse ($tableData ?? [] as $td)
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-50 flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-gray-800">{{ $td['title'] }}</h3>
                        <span class="text-xs text-gray-400">{{ count($td['rows']) }} baris</span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="bg-gray-50 border-b">
                                    <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-500 uppercase w-8">#</th>
                                    <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-500 uppercase">Nama</th>
                                    <th class="px-4 py-2.5 text-right text-xs font-semibold text-gray-500 uppercase">Jumlah</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($td['rows'] as $i => $r)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-4 py-2 text-gray-400 text-xs">{{ $i + 1 }}</td>
                                    <td class="px-4 py-2 text-gray-700 max-w-md truncate">{{ $r['label'] }}</td>
                                    <td class="px-4 py-2 text-right font-medium text-gray-800">{{ number_format($r['value']) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @empty
                <div class="text-center py-10 text-gray-400">Belum ada data.</div>
                @endforelse
            </div>

        </div>
    </div>
    @endif

    {{-- ===================== CTA SECTION ===================== --}}
@endsection

@push('scripts')
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/echarts@6/dist/echarts.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            const categories = @json($casesByCategory);
            const statuses = @json($status);
            const months = @json($casesPerMonth);

            // ========================
            // INSIGHT: Category
            // ========================
            if (categories.length) {
                const top = categories.reduce((a, b) => a.count > b.count ? a : b);
                const total = categories.reduce((sum, i) => sum + i.count, 0);
                const pct = Math.round((top.count / total) * 100);
                document.getElementById('categoryInsight').textContent =
                    `"${top.category_name}" mendominasi dengan ${top.count} kasus (${pct}% dari total ${total} kasus terdaftar).`;
            }

            // ========================
            // INSIGHT: Status
            // ========================
            if (statuses.length) {
                const total = statuses.reduce((sum, i) => sum + i.count, 0);
                const active = statuses.filter(s => ['investigation', 'penyelidikan', 'penyidikan', 'prosecution',
                    'trial'
                ].includes(s.status_key)).reduce((sum, i) => sum + i.count, 0);
                const activePct = Math.round((active / total) * 100);
                document.getElementById('statusInsight').textContent =
                    `${activePct}% dari ${total} kasus sedang dalam proses hukum aktif.`;
            }

            // ========================
            // INSIGHT: Monthly
            // ========================
            if (months.length) {
                const latest = months[months.length - 1];
                const prev = months.length > 1 ? months[months.length - 2] : null;
                let insight = `Pada ${latest.month}, terdapat ${latest.count} kasus baru.`;
                if (prev) {
                    const diff = latest.count - prev.count;
                    if (diff > 0) insight += ` Naik ${diff} kasus dibanding bulan sebelumnya.`;
                    else if (diff < 0) insight += ` Turun ${Math.abs(diff)} kasus dibanding bulan sebelumnya.`;
                    else insight += ' Sama dengan bulan sebelumnya.';
                }
                document.getElementById('monthlyInsight').textContent = insight;
            }

            // ========================
            // CATEGORY CHART
            // ========================
            Highcharts.chart('categoryChart', {
                chart: {
                    type: 'column',
                    style: {
                        fontFamily: 'inherit'
                    }
                },
                title: {
                    text: null
                },
                xAxis: {
                    categories: categories.map(i => i.category_name),
                    labels: {
                        style: {
                            fontSize: '11px'
                        }
                    }
                },
                yAxis: {
                    min: 0,
                    title: {
                        text: 'Jumlah Kasus'
                    },
                    allowDecimals: false
                },
                tooltip: {
                    formatter: function() {
                        return `<b>${this.x}</b><br/>Jumlah kasus: <b>${this.y}</b>`;
                    }
                },
                legend: {
                    enabled: false
                },
                series: [{
                    name: 'Kasus',
                    data: categories.map(i => i.count),
                    color: '#0d9488'
                }],
                credits: {
                    enabled: false
                },
                plotOptions: {
                    column: {
                        borderRadius: 4
                    }
                }
            });

            // ========================
            // STATUS CHART
            // ========================
            Highcharts.chart('statusChart', {
                chart: {
                    type: 'pie',
                    style: {
                        fontFamily: 'inherit'
                    }
                },
                title: {
                    text: null
                },
                tooltip: {
                    formatter: function() {
                        return `<b>${this.point.name}</b><br/>
                    ${this.y} kasus (${Math.round(this.percentage)}%)`;
                    }
                },
                plotOptions: {
                    pie: {
                        allowPointSelect: true,
                        cursor: 'pointer',
                        dataLabels: {
                            enabled: true,
                            format: '<b>{point.name}</b><br>{point.percentage:.0f}%',
                            style: {
                                fontSize: '11px',
                                fontWeight: 'normal'
                            }
                        }
                    }
                },
                series: [{
                    name: 'Kasus',
                    data: statuses.map(i => ({
                        name: i.status_name,
                        y: i.count
                    }))
                }],
                credits: {
                    enabled: false
                }
            });

            // ========================
            // MONTHLY CHART
            // ========================
            Highcharts.chart('monthlyChart', {
                chart: {
                    type: 'area',
                    style: {
                        fontFamily: 'inherit'
                    }
                },
                title: {
                    text: null
                },
                xAxis: {
                    categories: months.map(i => i.month),
                    labels: {
                        style: {
                            fontSize: '11px'
                        }
                    }
                },
                yAxis: {
                    title: {
                        text: 'Jumlah Kasus'
                    },
                    allowDecimals: false
                },
                tooltip: {
                    formatter: function() {
                        return `<b>${this.x}</b><br/>Kasus baru: <b>${this.y}</b>`;
                    }
                },
                series: [{
                    name: 'Kasus Baru',
                    data: months.map(i => i.count),
                    color: '#0d9488',
                    fillColor: {
                        linearGradient: {
                            x1: 0,
                            y1: 0,
                            x2: 0,
                            y2: 1
                        },
                        stops: [
                            [0, 'rgba(13,148,136,0.25)'],
                            [1, 'rgba(13,148,136,0)']
                        ]
                    },
                    lineWidth: 2,
                    marker: {
                        radius: 4
                    }
                }],
                credits: {
                    enabled: false
                }
            });

        });

        // ========================
        // PUBLIC CHARTS FROM CSV (ECharts)
        // ========================
        (function() {
            window.pubChartInstances = window.pubChartInstances || {};
            window.pubAllChartData = window.pubAllChartData || {};

            function pubInitChart(el) {
                var id = el.id;
                var raw = el.getAttribute('data-chart');
                if (!raw) return;
                var data = JSON.parse(raw);
                window.pubAllChartData[id] = data;

                if (window.pubChartInstances[id]) window.pubChartInstances[id].dispose();

                var chart = echarts.init(el);
                window.pubChartInstances[id] = chart;
                pubUpdateChart(id);
            }
            window.pubInitChart = pubInitChart;

            function pubUpdateChart(id) {
                var chart = window.pubChartInstances[id];
                var data = window.pubAllChartData[id];
                var el = document.getElementById(id);
                if (!chart || !data || data.length === 0) return;

                var chartType = el.getAttribute('data-type') || 'bar';

                var labels = data.map(function(d) { return d.label; });
                var values = data.map(function(d) { return d.value; });
                var long = labels.length > 15;

                if (chartType === 'pie') {
                    var total = values.reduce(function(a, b) { return a + b; }, 0);
                    var pieData = data.map(function(d) {
                        return { name: d.label, value: d.value };
                    });
                    var colors = ['#0d9488','#0891b2','#2563eb','#7c3aed','#db2777','#dc2626','#ea580c','#d97706','#65a30d','#059669'];
                    chart.setOption({
                        tooltip: {
                            trigger: 'item',
                            formatter: function(p) {
                                return '<b>' + p.name + '</b><br/>' + p.value.toLocaleString() + ' (' + p.percent.toFixed(1) + '%)';
                            }
                        },
                        series: [{
                            type: 'pie',
                            radius: ['30%', '65%'],
                            center: ['50%', '50%'],
                            data: pieData,
                            itemStyle: {
                                color: function(p) { return colors[p.dataIndex % colors.length]; },
                                borderRadius: 4,
                                borderColor: '#fff',
                                borderWidth: 2
                            },
                            label: {
                                formatter: function(p) { return p.name + '\n' + p.value.toLocaleString(); },
                                fontSize: 11,
                                fontWeight: 'bold'
                            },
                            emphasis: {
                                itemStyle: { shadowBlur: 10, shadowOffsetX: 0, shadowColor: 'rgba(0,0,0,0.3)' }
                            }
                        }]
                    });
                } else if (chartType === 'line') {
                    chart.setOption({
                        tooltip: {
                            trigger: 'axis',
                            formatter: function(params) {
                                var p = params[0];
                                return '<b>' + p.name + '</b><br/>Jumlah: <b>' + p.value.toLocaleString() + '</b>';
                            }
                        },
                        grid: { left: '3%', right: '4%', bottom: long ? '25%' : '12%', containLabel: true },
                        xAxis: { type: 'category', data: labels, axisLabel: { interval: long ? Math.ceil(labels.length / 20) : 0, rotate: long ? 55 : 40, fontSize: long ? 8 : 10 } },
                        yAxis: { type: 'value', minInterval: 1 },
                        series: [{
                            type: 'line',
                            data: values,
                            smooth: false,
                            lineStyle: { width: 3, color: '#0d9488' },
                            itemStyle: { color: '#0d9488' },
                            areaStyle: {
                                color: { type: 'linear', x: 0, y: 0, x2: 0, y2: 1,
                                    colorStops: [{ offset: 0, color: 'rgba(13,148,136,0.3)' }, { offset: 1, color: 'rgba(13,148,136,0.02)' }]
                                }
                            },
                            symbol: 'circle',
                            symbolSize: 8,
                            label: { show: true, position: 'top', fontSize: 10, fontWeight: 'bold', color: '#374151' }
                        }]
                    });
                } else if (chartType === 'hbar') {
                    chart.setOption({
                        tooltip: { trigger: 'axis', axisPointer: { type: 'shadow' },
                            formatter: function(params) {
                                var p = params[0];
                                return '<b>' + p.name + '</b><br/>Jumlah: <b>' + p.value.toLocaleString() + '</b>';
                            }
                        },
                        grid: { left: '20%', right: '5%', bottom: '5%', top: '5%', containLabel: true },
                        xAxis: { type: 'value', minInterval: 1 },
                        yAxis: { type: 'category', data: labels.slice().reverse(), axisLabel: { fontSize: 9, width: 120, overflow: 'truncate' } },
                        series: [{
                            type: 'bar',
                            data: values.slice().reverse(),
                            itemStyle: {
                                color: { type: 'linear', x: 0, y: 0, x2: 1, y2: 0,
                                    colorStops: [{ offset: 0, color: '#0d9488' }, { offset: 1, color: '#0f766e' }]
                                },
                                borderRadius: [0,4,4,0]
                            },
                            emphasis: { itemStyle: { color: '#115e59' } },
                            label: { show: true, position: 'right', fontSize: 9, fontWeight: 'bold', color: '#374151' }
                        }]
                    });
                } else {
                    chart.setOption({
                        tooltip: { trigger: 'axis', axisPointer: { type: 'shadow' },
                            formatter: function(params) {
                                var p = params[0];
                                return '<b>' + p.name + '</b><br/>Jumlah: <b>' + p.value.toLocaleString() + '</b>';
                            }
                        },
                        grid: { left: '3%', right: '4%', bottom: long ? '25%' : '12%', containLabel: true },
                        xAxis: { type: 'category', data: labels, axisLabel: { interval: long ? Math.ceil(labels.length / 20) : 0, rotate: long ? 55 : 40, fontSize: long ? 8 : 10 } },
                        yAxis: { type: 'value', minInterval: 1, max: chart.getDom().getAttribute('data-max-y') ? parseInt(chart.getDom().getAttribute('data-max-y')) : undefined },
                        dataZoom: long ? [{ type: 'slider', show: true, start: 0, end: 30, height: 16, bottom: 5 }] : undefined,
                        series: [{
                            type: 'bar',
                            data: values,
                            itemStyle: {
                                color: { type: 'linear', x: 0, y: 0, x2: 0, y2: 1,
                                    colorStops: [{ offset: 0, color: '#0d9488' }, { offset: 1, color: '#0f766e' }]
                                },
                                borderRadius: [4,4,0,0]
                            },
                            emphasis: { itemStyle: { color: '#115e59' } },
                            label: { show: !long, position: 'top', fontSize: 9, fontWeight: 'bold', color: '#374151' }
                        }]
                    });
                }
            }

            document.querySelectorAll('.pub-echart').forEach(pubInitChart);

            window.applyFilters = function() {
                var form = document.getElementById('filter-form');
                if (!form) return;
                var params = new URLSearchParams(new FormData(form)).toString();
                var url = window.location.pathname + '?' + params;

                fetch(url, { headers: { 'Accept': 'application/json' } })
                    .then(function(r) { return r.json(); })
                    .then(function(data) {
                        // Update KPIs
                        function kpiByTitle(arr, t) { return arr.find(function(k) { return k.title === t; }); }

                        var kpiHtml = '<div class="grid grid-cols-1 sm:grid-cols-3 gap-3">';
                        ['Perkara','Terdakwa','Pengadilan'].forEach(function(t) {
                            var k = kpiByTitle(data.kpiData, t);
                            if (k) kpiHtml += '<div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 text-center hover:shadow-md transition-shadow"><div class="text-lg font-bold text-gray-900">' + k.display + '</div><div class="text-xs text-gray-500 mt-0.5">' + k.title + '</div></div>';
                        });
                        kpiHtml += '</div>';
                        kpiHtml += '<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">';
                        kpiHtml += '<div><h4 class="text-sm font-semibold text-gray-700 mb-2 text-center">Subjek Hukum</h4><div class="grid grid-cols-2 gap-3">';
                        ['Perorangan','Korporasi'].forEach(function(t) {
                            var k = kpiByTitle(data.kpiData, t);
                            if (k) kpiHtml += '<div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 text-center hover:shadow-md transition-shadow"><div class="text-lg font-bold text-gray-900">' + k.display + '</div><div class="text-xs text-gray-500 mt-0.5">' + k.title + '</div></div>';
                        });
                        kpiHtml += '</div></div>';
                        kpiHtml += '<div><h4 class="text-sm font-semibold text-gray-700 mb-2 text-center">Vonis Putusan</h4><div class="grid grid-cols-3 gap-3">';
                        ['Bebas','Lepas','Bersalah'].forEach(function(t) {
                            var k = kpiByTitle(data.kpiData, 'Vonis ' + t);
                            if (k) kpiHtml += '<div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 text-center hover:shadow-md transition-shadow"><div class="text-lg font-bold text-gray-900">' + k.display + '</div><div class="text-xs text-gray-500 mt-0.5">' + k.title + '</div></div>';
                        });
                        kpiHtml += '</div></div></div></div>';
                        document.getElementById('kpi-container').innerHTML = kpiHtml;

                        // Show/hide reset link
                        var sp = new URLSearchParams(params);
                        document.getElementById('reset-filter-container').style.display = (sp.has('tahun') || sp.has('klasifikasi') || sp.has('pulau')) ? '' : 'none';

                        // Update charts
                        var row1 = document.getElementById('charts-row-1');
                        var row2 = document.getElementById('charts-row-2');
                        row1.innerHTML = '';
                        row2.innerHTML = '';

                        data.publicCharts.forEach(function(ch, idx) {
                            var card = document.createElement('div');
                            card.className = 'bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow';
                            card.innerHTML = '<div class="px-5 py-4 border-b border-gray-50"><h3 class="text-sm font-semibold text-gray-800">' + ch.title + '</h3></div>'
                                + '<div wire:ignore><div class="pub-echart" id="pub-' + ch.id + '" data-chart=\'' + JSON.stringify(ch.data) + '\' data-type="' + ch.type + '" style="height:' + (idx < 2 ? 400 : 350) + 'px;width:100%"></div></div>';
                            if (idx < 2) row1.appendChild(card);
                            else row2.appendChild(card);
                        });

                        // Re-init charts
                        Object.keys(window.pubChartInstances).forEach(function(id) {
                            if (window.pubChartInstances[id]) { window.pubChartInstances[id].dispose(); delete window.pubChartInstances[id]; }
                        });
                        document.querySelectorAll('.pub-echart').forEach(window.pubInitChart);
                        setTimeout(pubResizeAll, 100);

                        // Update tables
                        var tableHtml = '';
                        data.tableData.forEach(function(td) {
                            var rowCount = td.rows ? td.rows.length : 0;
                            tableHtml += '<div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">';
                            tableHtml += '<div class="px-5 py-4 border-b border-gray-50 flex items-center justify-between">';
                            tableHtml += '<h3 class="text-sm font-semibold text-gray-800">' + td.title + '</h3>';
                            tableHtml += '<span class="text-xs text-gray-400">' + rowCount + ' baris</span></div>';
                            tableHtml += '<div class="overflow-x-auto"><table class="min-w-full text-sm"><thead><tr class="bg-gray-50 border-b">';
                            tableHtml += '<th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-500 uppercase w-8">#</th>';
                            tableHtml += '<th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-500 uppercase">Nama</th>';
                            tableHtml += '<th class="px-4 py-2.5 text-right text-xs font-semibold text-gray-500 uppercase">Jumlah</th></tr></thead>';
                            tableHtml += '<tbody class="divide-y divide-gray-100">';
                            if (td.rows) {
                                td.rows.forEach(function(r, i) {
                                    var val = r.value !== undefined && r.value !== null ? Number(r.value).toLocaleString() : '-';
                                    tableHtml += '<tr class="hover:bg-gray-50 transition-colors">';
                                    tableHtml += '<td class="px-4 py-2 text-gray-400 text-xs">' + (i + 1) + '</td>';
                                    tableHtml += '<td class="px-4 py-2 text-gray-700 max-w-md truncate">' + (r.label || '') + '</td>';
                                    tableHtml += '<td class="px-4 py-2 text-right font-medium text-gray-800">' + val + '</td></tr>';
                                });
                            }
                            tableHtml += '</tbody></table></div></div>';
                        });
                        if (!tableHtml) tableHtml = '<div class="text-center py-10 text-gray-400">Belum ada data.</div>';
                        document.getElementById('tables-container').innerHTML = tableHtml;
                    });
            };

            window.resetFilters = function() {
                document.getElementById('filter-form').querySelectorAll('select').forEach(function(s) { s.value = ''; });
                applyFilters();
            };

            function pubResizeAll() {
                Object.keys(window.pubChartInstances).forEach(function(id) {
                    if (window.pubChartInstances[id]) window.pubChartInstances[id].resize();
                });
            }
            window.pubResizeAll = pubResizeAll;
            window.addEventListener('resize', pubResizeAll);
        })();

        // ========================
        // FAQ Accordion
        // ========================
        function toggleFaqHome(btn) {
            const body = btn.nextElementSibling;
            const icon = btn.querySelector('.faq-icon');
            const isOpen = !body.classList.contains('hidden');

            document.querySelectorAll('.faq-body').forEach(b => b.classList.add('hidden'));
            document.querySelectorAll('.faq-icon').forEach(i => i.classList.remove('rotate-180'));

            if (!isOpen) {
                body.classList.remove('hidden');
                icon.classList.add('rotate-180');
            }
        }
    </script>
@endpush
