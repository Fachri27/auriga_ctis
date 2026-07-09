<div>
    <div class="max-w-7xl mx-auto py-10 px-4">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Dashboard Chart</h1>
                <p class="text-gray-500 mt-1">Indeksasi Putusan Perkara</p>
            </div>
            <div class="flex items-center gap-3">
                <div class="flex bg-gray-100 rounded-lg p-1">
                    <button wire:click="$set('view', 'chart')"
                        class="px-4 py-1.5 text-sm font-medium rounded-md transition-colors {{ $view === 'chart' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/></svg>
                        Chart
                    </button>
                    <button wire:click="$set('view', 'table')"
                        class="px-4 py-1.5 text-sm font-medium rounded-md transition-colors {{ $view === 'table' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                        Table
                    </button>
                </div>
                <a href="{{ route('charts.upload') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Tambah Data Baru
                </a>
                <a href="{{ route('charts.sync') }}"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    Sync Now
                </a>
            </div>
        </div>

        @php
            $maxYear = max(array_merge($allYears, [date('Y')]));
        @endphp

        {{-- ===== CHART VIEW ===== --}}
        @if ($view === 'chart')
            @if (count($allYears) > 0)
            <div class="bg-white border border-gray-200 rounded-xl p-4 mb-6 shadow-sm">
                <div class="flex items-center gap-4 flex-wrap">
                    <span class="text-sm font-semibold text-gray-700">Filter Tahun</span>
                    <div class="flex items-center gap-2">
                        <label class="text-xs text-gray-500">Dari</label>
                        <select id="yearFrom" class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            @for ($year = 2000; $year <= $maxYear; $year++)
                                <option value="{{ $year }}" {{ $year == 2000 ? 'selected' : '' }}>{{ $year }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="flex items-center gap-2">
                        <label class="text-xs text-gray-500">Sampai</label>
                        <select id="yearTo" class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            @for ($year = 2000; $year <= $maxYear; $year++)
                                <option value="{{ $year }}" {{ $year == $maxYear ? 'selected' : '' }}>{{ $year }}</option>
                            @endfor
                        </select>
                    </div>
                </div>
            </div>
            @endif

            <div class="space-y-6" id="chartGrid">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @foreach (array_slice($charts, 0, 2) as $ch)
                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition-shadow">
                        <div class="p-4 border-b border-gray-100 flex items-center justify-between">
                            <h2 class="text-sm font-semibold text-gray-800">{{ $ch['title'] }}</h2>
                            <button wire:click="downloadCsv('{{ $ch['dataset'] }}')"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-green-700 bg-green-50 rounded-lg hover:bg-green-100 transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                Export CSV
                            </button>
                        </div>
                        <div wire:ignore>
                            <div class="echart-el" id="{{ $ch['id'] }}"
                                data-chart='{{ json_encode($ch['data']) }}'
                                data-type="{{ $ch['type'] }}"
                                style="height:360px;width:100%"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    @foreach (array_slice($charts, 2) as $ch)
                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition-shadow">
                        <div class="p-4 border-b border-gray-100 flex items-center justify-between">
                            <h2 class="text-sm font-semibold text-gray-800">{{ $ch['title'] }}</h2>
                            <button wire:click="downloadCsv('{{ $ch['dataset'] }}')"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-green-700 bg-green-50 rounded-lg hover:bg-green-100 transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                Export CSV
                            </button>
                        </div>
                        <div wire:ignore>
                            <div class="echart-el" id="{{ $ch['id'] }}"
                                data-chart='{{ json_encode($ch['data']) }}'
                                data-type="{{ $ch['type'] }}"
                                style="height:320px;width:100%"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- ===== TABLE VIEW ===== --}}
        @if ($view === 'table')
            <div class="space-y-8">
                @forelse ($tableData as $td)
                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                            <h2 class="text-sm font-semibold text-gray-800">{{ $td['title'] }}</h2>
                            <span class="text-xs text-gray-400">{{ count($td['rows']) }} baris</span>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-sm">
                                <thead>
                                    <tr class="bg-gray-50 border-b">
                                        <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-500 uppercase w-8">#</th>
                                        <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-500 uppercase">Label</th>
                                        <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-500 uppercase">Tahun</th>
                                        <th class="px-4 py-2.5 text-right text-xs font-semibold text-gray-500 uppercase">Nilai</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach ($td['rows'] as $i => $r)
                                        <tr class="hover:bg-gray-50 transition-colors">
                                            <td class="px-4 py-2 text-gray-400 text-xs">{{ $i + 1 }}</td>
                                            <td class="px-4 py-2 text-gray-700 max-w-md truncate">{{ $r['label'] }}</td>
                                            <td class="px-4 py-2 text-gray-500">{{ $r['year'] ?? '-' }}</td>
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
        @endif
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/echarts@6/dist/echarts.min.js"></script>
    <script>
    document.addEventListener('livewire:navigated', function() {
        var chartInstances = {};
        var allChartData = {};

        function initChart(el) {
            var id = el.id;
            var raw = el.getAttribute('data-chart');
            if (!raw) return;
            var data = JSON.parse(raw);
            allChartData[id] = data;

            if (chartInstances[id]) chartInstances[id].dispose();

            var chart = echarts.init(el);
            chartInstances[id] = chart;
            updateChart(id);
        }

        function getYearRange() {
            var from = parseInt(document.getElementById('yearFrom')?.value);
            var to = parseInt(document.getElementById('yearTo')?.value);
            if (isNaN(from) || isNaN(to)) return null;
            return { from: Math.min(from, to), to: Math.max(from, to) };
        }

        function updateChart(id) {
            var chart = chartInstances[id];
            var data = allChartData[id];
            var el = document.getElementById(id);
            if (!chart || !data || data.length === 0) return;

            var chartType = el.getAttribute('data-type') || 'bar';

            var range = getYearRange();

            var filtered = data;
            if (range) {
                filtered = data.filter(function(d) {
                    var year = parseInt(d.label);
                    if (isNaN(year)) return true;
                    return year >= range.from && year <= range.to;
                });
            }

            if (filtered.length === 0) {
                chart.clear();
                return;
            }

            var labels = filtered.map(function(d) { return d.label; });
            var values = filtered.map(function(d) { return d.value; });
            var long = labels.length > 15;

            if (chartType === 'pie') {
                var total = values.reduce(function(a, b) { return a + b; }, 0);
                var pieData = filtered.map(function(d) {
                    return { name: d.label, value: d.value };
                });
                var colors = ['#3b82f6','#2563eb','#7c3aed','#db2777','#dc2626','#ea580c','#d97706','#65a30d','#059669','#0d9488'];
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
                        smooth: true,
                        lineStyle: { width: 3, color: '#3b82f6' },
                        itemStyle: { color: '#3b82f6' },
                        areaStyle: {
                            color: { type: 'linear', x: 0, y: 0, x2: 0, y2: 1,
                                colorStops: [{ offset: 0, color: 'rgba(59,130,246,0.3)' }, { offset: 1, color: 'rgba(59,130,246,0.02)' }]
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
                                colorStops: [{ offset: 0, color: '#3b82f6' }, { offset: 1, color: '#1d4ed8' }]
                            },
                            borderRadius: [0,4,4,0]
                        },
                        emphasis: { itemStyle: { color: '#1e40af' } },
                        label: { show: true, position: 'right', fontSize: 9, fontWeight: 'bold', color: '#374151' }
                    }]
                });
            } else {
                chart.setOption({
                    tooltip: {
                        trigger: 'axis',
                        axisPointer: { type: 'shadow' },
                        formatter: function(params) {
                            var p = params[0];
                            return '<b>' + p.name + '</b><br/>Jumlah: <b>' + p.value.toLocaleString() + '</b>';
                        }
                    },
                    grid: { left: '3%', right: '4%', bottom: long ? '25%' : '12%', containLabel: true },
                    xAxis: {
                        type: 'category',
                        data: labels,
                        axisLabel: { interval: long ? Math.ceil(labels.length / 20) : 0, rotate: long ? 55 : 40, fontSize: long ? 8 : 10 }
                    },
                    yAxis: { type: 'value', minInterval: 1, max: el.getAttribute('data-max-y') ? parseInt(el.getAttribute('data-max-y')) : undefined },
                    dataZoom: long ? [{ type: 'slider', show: true, start: 0, end: 30, height: 16, bottom: 5 }] : undefined,
                    series: [{
                        type: 'bar',
                        data: values,
                        itemStyle: {
                            color: { type: 'linear', x: 0, y: 0, x2: 0, y2: 1,
                                colorStops: [{ offset: 0, color: '#3b82f6' }, { offset: 1, color: '#1d4ed8' }]
                            },
                            borderRadius: [4,4,0,0]
                        },
                        emphasis: { itemStyle: { color: '#1e40af' } },
                        label: { show: !long, position: 'top', fontSize: 9, fontWeight: 'bold', color: '#374151' }
                    }]
                });
            }
        }

        function initAllCharts() {
            document.querySelectorAll('.echart-el').forEach(initChart);

            window.addEventListener('resize', function() {
                Object.keys(chartInstances).forEach(function(id) {
                    if (chartInstances[id]) chartInstances[id].resize();
                });
            });

            ['yearFrom', 'yearTo'].forEach(function(id) {
                var el = document.getElementById(id);
                if (el) {
                    el.addEventListener('change', function() {
                        Object.keys(chartInstances).forEach(updateChart);
                    });
                }
            });
        }

        if (document.querySelector('.echart-el')) {
            if (document.readyState === 'complete') {
                initAllCharts();
            } else {
                window.addEventListener('load', initAllCharts);
            }
        }
    });
    </script>
    @endpush
</div>
