<div>
    <div class="max-w-7xl mx-auto py-10 px-4">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Dashboard Chart</h1>
                <p class="text-gray-500 mt-1">Indeksasi Putusan Perkara</p>
            </div>
            <a href="{{ route('charts.upload') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah Data Baru
            </a>
        </div>

        @if (count($allYears) > 0)
        <div class="bg-white border border-gray-200 rounded-xl p-4 mb-6 shadow-sm" id="yearFilterBox">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-semibold text-gray-700">Filter Tahun</span>
                <button type="button" id="toggleAllYears" class="text-xs text-blue-600 hover:underline">Uncheck All</button>
            </div>
            <div class="flex flex-wrap gap-3">
                @foreach ($allYears as $year)
                    <label class="inline-flex items-center gap-1.5 cursor-pointer select-none text-sm year-label" data-year="{{ $year }}">
                        <input type="checkbox" value="{{ $year }}" checked class="year-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        {{ $year }}
                    </label>
                @endforeach
            </div>
        </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6" id="chartGrid">
            @foreach ($charts as $ch)
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition-shadow {{ $loop->last ? 'md:col-span-2' : '' }}">
                    <div class="p-4 border-b border-gray-100">
                        <h2 class="text-sm font-semibold text-gray-800">{{ $ch['title'] }}</h2>
                    </div>
                    <div class="p-4" wire:ignore>
                        <div class="echart-el" id="{{ $ch['id'] }}"
                            data-chart='{{ json_encode($ch['data']) }}'
                            {{ str_contains($ch['id'], 'pengadilan') ? 'data-max-y=200' : '' }}
                            style="height:340px;width:100%"></div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/echarts@6/dist/echarts.min.js"></script>
    <script>
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

    function updateChart(id) {
        var chart = chartInstances[id];
        var data = allChartData[id];
        if (!chart || !data || data.length === 0) return;

        var selected = {};
        document.querySelectorAll('.year-checkbox').forEach(function(cb) {
            selected[cb.value] = cb.checked;
        });

        var filtered = data.filter(function(d) {
            if (selected[d.label] === undefined) return true;
            return selected[d.label] === true;
        });

        if (filtered.length === 0) {
            chart.clear();
            return;
        }

        var labels = filtered.map(function(d) { return d.label; });
        var values = filtered.map(function(d) { return d.value; });
        var long = labels.length > 15;

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
            yAxis: { type: 'value', minInterval: 1, max: chart.getDom().getAttribute('data-max-y') ? parseInt(chart.getDom().getAttribute('data-max-y')) : undefined },
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

    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.echart-el').forEach(initChart);

        document.querySelectorAll('.year-checkbox').forEach(function(cb) {
            cb.addEventListener('change', function() {
                var year = this.value;
                var label = this.closest('.year-label');
                if (label) {
                    label.classList.toggle('text-gray-800', this.checked);
                    label.classList.toggle('text-gray-400', !this.checked);
                }
                Object.keys(chartInstances).forEach(updateChart);
            });
        });

        var toggleBtn = document.getElementById('toggleAllYears');
        if (toggleBtn) {
            toggleBtn.addEventListener('click', function() {
                var checked = this.textContent === 'Check All';
                document.querySelectorAll('.year-checkbox').forEach(function(cb) {
                    cb.checked = checked;
                    var label = cb.closest('.year-label');
                    if (label) {
                        label.classList.toggle('text-gray-800', checked);
                        label.classList.toggle('text-gray-400', !checked);
                    }
                });
                this.textContent = checked ? 'Uncheck All' : 'Check All';
                Object.keys(chartInstances).forEach(updateChart);
            });
        }
    });
    </script>
    @endpush
</div>
