<div wire:ignore>
    <div class="max-w-5xl mx-auto px-6 py-6 space-y-4 cms-rise" style="animation-delay:.04s">
        <div class="border-b border-[color:var(--hairline)] pb-3">
            <div class="cms-eyebrow">TAHUNAN</div>
            <h1 class="text-xl font-semibold text-[color:var(--ink)] tracking-tight mt-0.5">Putusan Perkara per Tahun</h1>
            <p class="text-xs text-[color:var(--muted)] mt-1">Total perkara berdasarkan tahun</p>
        </div>

        <div class="cms-panel">
            <div class="cms-panel-head">
                <div class="cms-panel-title">Filter</div>
            </div>
            <div class="cms-panel-body" style="padding:16px 20px">
                <div class="flex items-end gap-4 flex-wrap">
                    <div>
                        <label class="block text-xs font-medium text-[color:var(--muted)] mb-1.5">Tampilan</label>
                        <select wire:model.live="view" wire:change="$refresh" class="cms-input">
                            <option value="tahunan">Total per Tahun</option>
                            <option value="klasifikasi">per Klasifikasi</option>
                        </select>
                    </div>

                    @if ($view === 'klasifikasi' && count($years) > 0)
                    <div>
                        <label class="block text-xs font-medium text-[color:var(--muted)] mb-1.5">Filter Tahun</label>
                        <select wire:model.live="selectedYear" wire:change="$refresh" class="cms-input">
                            @foreach ($years as $y)
                                <option value="{{ $y }}">{{ $y }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="cms-panel">
            <div class="cms-panel-head">
                <div class="cms-panel-title">Grafik</div>
            </div>
            <div class="cms-panel-body" style="padding:16px 20px">
                <div id="tahunanChart" style="height: 500px; width: 100%;"></div>
            </div>
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