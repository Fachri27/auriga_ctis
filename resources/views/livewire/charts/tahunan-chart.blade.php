<div wire:ignore>
    <div class="max-w-5xl mx-auto py-10">
        <h1 class="text-2xl font-bold mb-2">Putusan Perkara per Tahun</h1>
        <p class="text-gray-500 mb-6">Total perkara berdasarkan tahun</p>

        <div class="flex items-center gap-4 mb-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tampilan</label>
                <select wire:model.live="view" wire:change="$refresh"
                    class="border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="tahunan">Total per Tahun</option>
                    <option value="klasifikasi">per Klasifikasi</option>
                </select>
            </div>

            @if ($view === 'klasifikasi' && count($years) > 0)
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Filter Tahun</label>
                <select wire:model.live="selectedYear" wire:change="$refresh"
                    class="border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    @foreach ($years as $y)
                        <option value="{{ $y }}">{{ $y }}</option>
                    @endforeach
                </select>
            </div>
            @endif
        </div>

        <div class="bg-white border rounded-lg p-6 shadow-sm">
            <div id="tahunanChart" style="height: 500px; width: 100%;"></div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/echarts@6/dist/echarts.min.js"></script>
<script>
    function initTahunanChart() {
        const el = document.getElementById('tahunanChart');
        if (!el) return setTimeout(initTahunanChart, 100);

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
                name: 'Jumlah',
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

    document.addEventListener('livewire:init', initTahunanChart);
    document.addEventListener('livewire:navigated', initTahunanChart);
</script>
@endpush