<div wire:ignore>
    <div class="max-w-5xl mx-auto py-10">
        <h1 class="text-2xl font-bold mb-2">{{ $title }}</h1>
        <p class="text-gray-500 mb-6">Top {{ $limit }} tertinggi</p>

        <div class="bg-white border rounded-lg p-6 shadow-sm">
            <div id="datasetChart" style="height: 500px; width: 100%;"></div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/echarts@6/dist/echarts.min.js"></script>
<script>
    function initDatasetChart() {
        const el = document.getElementById('datasetChart');
        if (!el) return setTimeout(initDatasetChart, 100);

        const chartData = @json($chartData);
        if (!chartData || chartData.length === 0) return;

        const chart = echarts.init(el);
        const labels = chartData.map(d => d.label);
        const values = chartData.map(d => d.value);

        const isLong = labels.length > 20;

        chart.setOption({
            tooltip: {
                trigger: 'axis',
                axisPointer: { type: 'shadow' }
            },
            grid: {
                left: '3%',
                right: '4%',
                bottom: isLong ? '30%' : '15%',
                containLabel: true
            },
            xAxis: {
                type: 'category',
                data: labels,
                axisLabel: {
                    rotate: isLong ? 60 : 45,
                    fontSize: isLong ? 9 : 11
                }
            },
            yAxis: {
                type: 'value',
                minInterval: 1
            },
            
            dataZoom: isLong ? [{
                type: 'slider',
                show: true,
                start: 0,
                end: 30,
                height: 20,
                bottom: 10
            }] : undefined,

            series: [{
                type: 'bar',
                data: values,
                itemStyle: {
                    color: '#3b82f6',
                    borderRadius: [4, 4, 0, 0]
                },
                label: {
                    show: true,
                    position: 'top',
                    fontSize: 10
                }
            }]
        });

        window.addEventListener('resize', () => chart.resize());
    }

    document.addEventListener('livewire:init', initDatasetChart);
    document.addEventListener('livewire:navigated', initDatasetChart);
</script>
@endpush