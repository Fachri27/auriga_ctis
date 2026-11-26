{{-- KPI perkara (indeksasi putusan) untuk mode Perkara dashboard. Butuh $kpiData (App\Services\PerkaraStatistics::kpis). --}}
@php $byTitle = fn($t) => collect($kpiData ?? [])->firstWhere('title', $t); @endphp
<div class="mt-8 border border-[#E2E6DA] bg-white">
    <dl class="grid grid-cols-1 sm:grid-cols-3 divide-y sm:divide-y-0 sm:divide-x divide-[#E2E6DA]">
        @foreach (['Perkara', 'Terdakwa', 'Pengadilan'] as $t)
        @php $k = $byTitle($t); @endphp
        @if ($k)
        <div class="px-6 py-5">
            <dd class="font-data text-2xl sm:text-3xl font-semibold text-gray-900">{{ $k['display'] }}</dd>
            <dt class="font-data text-[10px] tracking-[0.18em] uppercase text-gray-500 mt-1">{{ __($k['title']) }}</dt>
        </div>
        @endif
        @endforeach
    </dl>
    <div class="grid grid-cols-1 lg:grid-cols-2 border-t border-[#E2E6DA] divide-y lg:divide-y-0 lg:divide-x divide-[#E2E6DA]">
        <div>
            {{-- Rincian KPI Terdakwa: jumlah terdakwa per subjek hukum --}}
            <p class="px-6 pt-4 font-data text-[10px] tracking-[0.22em] uppercase text-gray-400">{{ __('Keterangan Terdakwa') }}</p>
            <dl class="grid grid-cols-2 divide-x divide-[#E2E6DA]">
                @foreach (['Perorangan', 'Korporasi'] as $t)
                @php $k = $byTitle($t); @endphp
                @if ($k)
                <div class="px-6 py-4">
                    <dd class="font-data text-xl font-semibold text-gray-900">
                        {{ $k['display'] }}
                        @if (!empty($k['share']))
                            <span class="text-xs font-normal text-gray-400">{{ $k['share'] }}</span>
                        @endif
                    </dd>
                    <dt class="font-data text-[10px] tracking-[0.18em] uppercase text-gray-500 mt-1">{{ __($k['title']) }}</dt>
                </div>
                @endif
                @endforeach
            </dl>
        </div>
        <div>
            <p class="px-6 pt-4 font-data text-[10px] tracking-[0.22em] uppercase text-gray-400">{{ __('Vonis Putusan') }}</p>
            <dl class="grid grid-cols-3 divide-x divide-[#E2E6DA]">
                @foreach (['Bersalah', 'Bebas', 'Lepas'] as $t)
                @php $k = $byTitle('Vonis ' . $t); @endphp
                @if ($k)
                <div class="px-6 py-4">
                    <dd class="font-data text-xl font-semibold text-gray-900">{{ $k['display'] }}</dd>
                    <dt class="font-data text-[10px] tracking-[0.18em] uppercase text-gray-500 mt-1">{{ __($t) }}</dt>
                </div>
                @endif
                @endforeach
            </dl>
        </div>
    </div>
    @if ($byTitle('Rata-rata Penjara'))
    <div class="border-t border-[#E2E6DA]">
        <p class="px-6 pt-4 font-data text-[10px] tracking-[0.22em] uppercase text-gray-400">
            {{ __('Vonis Pidana') }} <span style="text-transform: none; letter-spacing: normal">· {{ __('semua putusan') }}</span>
        </p>
        <dl class="grid grid-cols-1 sm:grid-cols-3 divide-y sm:divide-y-0 sm:divide-x divide-[#E2E6DA]">
            @foreach (['Rata-rata Penjara', 'Rata-rata Denda', 'Total Denda'] as $t)
            @php $k = $byTitle($t); @endphp
            <div class="px-6 py-4">
                <dd class="font-data text-xl font-semibold text-gray-900">{{ $k['display'] }}</dd>
                <dt class="font-data text-[10px] tracking-[0.18em] uppercase text-gray-500 mt-1">{{ __($t) }}</dt>
            </div>
            @endforeach
        </dl>
    </div>
    @endif
</div>
