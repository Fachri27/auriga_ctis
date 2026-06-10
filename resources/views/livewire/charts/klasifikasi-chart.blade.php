<div wire:ignore>
    <div class="max-w-5xl mx-auto py-10">
        <h1 class="text-2xl font-bold mb-2">Indeksasi Putusan Perkara</h1>
        <p class="text-gray-500 mb-6">Jumlah perkara berdasarkan klasifikasi</p>

        <div class="bg-white border rounded-lg p-6 shadow-sm">
            <div id="chart" style="height: 500px; width: 100%;"></div>
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
