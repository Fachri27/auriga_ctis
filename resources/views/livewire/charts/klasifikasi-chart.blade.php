<div wire:ignore>
    <div class="max-w-5xl mx-auto px-6 py-6 space-y-4 cms-rise" style="animation-delay:.04s">
        <div class="border-b border-[color:var(--hairline)] pb-3">
            <div class="cms-eyebrow">INDEKSASI</div>
            <h1 class="text-xl font-semibold text-[color:var(--ink)] tracking-tight mt-0.5">Indeksasi Putusan Perkara</h1>
            <p class="text-xs text-[color:var(--muted)] mt-1">Jumlah perkara berdasarkan klasifikasi</p>
        </div>

        <div class="cms-panel">
            <div class="cms-panel-head">
                <div class="cms-panel-title">Grafik</div>
            </div>
            <div class="cms-panel-body" style="padding:16px 20px">
                <div id="chart" style="height: 500px; width: 100%;"></div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/echarts@6/dist/echarts.min.js"></script>
<script>
    function initChart() {
        const el = document.getElementById('chart');
        if (!el) return setTimeout(initChart, 100);

        const chartData = @json($chartData);
        if (!chartData || chartData.length === 0) return;

        const chart = echarts.init(el);

        chart.setOption({
            tooltip: {
                trigger: 'axis',
                axisPointer: { type: 'shadow' }
            },
            grid: {
                left: '3%',
                right: '4%',
                bottom: '15%',
                containLabel: true
            },
            xAxis: {
                type: 'category',
                data: chartData.map(d => d.label),
                axisLabel: {
                    rotate: 45,
                    fontSize: 11
                }
            },
            yAxis: {
                type: 'value',
                minInterval: 1
            },
            series: [{
                name: 'Jumlah Perkara',
                type: 'bar',
                data: chartData.map(d => d.value),
                itemStyle: {
                    color: '#3b82f6',
                    borderRadius: [4, 4, 0, 0]
                },
                label: {
                    show: true,
                    position: 'top',
                    fontSize: 11
                }
            }]
        });

        window.addEventListener('resize', () => chart.resize());
    }

    document.addEventListener('livewire:init', initChart);
</script>
@endpush