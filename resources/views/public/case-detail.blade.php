@extends('layouts.main')

@section('content')
<div class="bg-gray-50 mt-20">

    {{-- HERO --}}
    <section class="bg-white border-b">
        <div class="max-w-7xl mx-auto px-6 py-10">
            <span
                class="inline-block mb-3 px-3 py-1 rounded-full text-xs font-semibold
                {{ $case->status?->key === 'investigation' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800' }}">
                {{ $case->status->name ?? 'Status' }}
            </span>

            <h1 class="text-3xl md:text-4xl font-bold leading-tight max-w-4xl">
                {!! optional($case->translations->first())->title !!}
            </h1>

            <p class="mt-4 text-gray-600 max-w-3xl">
                {!! optional($case->translations->first())->summary !!}
            </p>

            <div class="mt-6 flex flex-wrap gap-6 text-sm text-gray-700">
                <div>
                    <span class="block text-gray-400">Nomor Kasus</span>
                    <strong>{{ $case->case_number }}</strong>
                </div>
                <div>
                    <span class="block text-gray-400">Tanggal Kejadian</span>
                    <strong>{{ $case->event_date ?? '-' }}</strong>
                </div>
                <div>
                    <span class="block text-gray-400">Dipublikasikan</span>
                    <strong>{{ optional($case->published_at)->format('d M Y') }}</strong>
                </div>
            </div>
        </div>
    </section>

    {{-- MAP --}}
    <section class="max-w-7xl mx-auto px-6 py-10">
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div id="map" class="h-[420px] w-full"></div>
            <div class="px-6 py-4 text-sm text-gray-600">
                📍 {{ $location['province'] ?? 'Lokasi tidak diketahui' }} - {{ $location['district'] ?? 'Lokasi tidak
                diketahui' }}
            </div>
        </div>
    </section>

    {{-- INFO GRID --}}
    <section class="max-w-6xl mx-auto px-6">
        <div class="grid md:grid-cols-4 gap-4">
            @php
            $info = [
            ['Kategori', optional($case->category?->translations->first())->name],
            ['Status', $case->status->name ?? '-'],
            ['Provinsi', $location['province'] ?? 'Lokasi tidak diketahui'],
            ['Kab/Kota', $location['district'] ?? 'Lokasi tidak diketahui'],
            ];
            @endphp

            @foreach ($info as [$label, $value])
            <div class="bg-white rounded-xl p-5 shadow-sm">
                <p class="text-xs text-gray-400 uppercase tracking-wide">{{ $label }}</p>
                <p class="mt-2 font-semibold text-gray-800">{{ $value }}</p>
            </div>
            @endforeach
        </div>
    </section>

    {{-- two column --}}


    {{-- DESKRIPSI --}}
    <section class="max-w-7xl mx-auto px-6 py-12">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div class="md:border-r-1 mb-5">
                <h2 class="text-2xl font-bold mb-4">Kronologi Kasus</h2>
                <div class="prose prose-gray max-w-none mb-10 pr-15">
                    {!! optional($case->translations->first())->description !!}
                </div>
                <h2 class="text-2xl font-bold mb-6">Perkembangan Kasus</h2>

                <div class="space-y-6 border-l-2 border-green-600 pl-6">
                    {{-- OLD: The timeline used to show process-based entries: $t->process->translations->first()
                    This is part of the legacy workflow and is intentionally hidden from public UI. --}}

                    @forelse ($case->simple_timeline as $entry)
                    <div class="relative">
                        <span class="absolute -left-[11px] top-1 w-4 h-4 bg-green-600 rounded-full"></span>

                        <h3 class="font-semibold text-gray-800">
                            {{ $entry['title'] ?? 'Perkembangan Kasus' }}
                        </h3>

                        <p class="text-sm text-gray-600 mt-1">
                            {{ $entry['notes'] }}
                        </p>

                        <p class="text-xs text-gray-400 mt-2">
                            {{ \Carbon\Carbon::parse($entry['created_at'])->format('d M Y H:i') }}
                        </p>
                    </div>
                    @empty
                    <p class="text-gray-500">Belum ada perkembangan</p>
                    @endforelse
                </div>
            </div>
            <div>
                <h2 class="text-2xl font-bold mb-4">Artikel Terkait</h2>
                <div class="overflow-hidden transition">
                    {{-- two column --}}
                    @foreach ($artikel as $data)
                    <div class="grid grid-cols-1 md:grid-cols-2">
                        <div>
                            <img src="{{ asset('storage/'. $data->image) }}" alt=""
                                class="w-full h-[200px] object-cover">
                        </div>
                        <div class="pl-3">
                            <h3 class="text-lg font-bold  mb-1">
                                <a href="">
                                    {{ $data->translation->first()->title ?? '' }}
                                </a>
                            </h3>
                            <p>
                                {!! $data->translation->first()->excerpt ?? '' !!}
                            </p>
                        </div>
                    </div>
                    @endforeach
                    
                </div>
            </div>
        </div>
    </section>

    {{-- TIMELINE --}}
    {{-- <section class="max-w-4xl mx-auto px-6 pb-16">

    </section> --}}

</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {

    const lat = {{ $case->latitude ?? 'null' }};
    const lng = {{ $case->longitude ?? 'null' }};

    if (!lat || !lng) return;

    const map = L.map('map', { scrollWheelZoom: false })
        .setView([lat, lng], 12);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png')
        .addTo(map);

    L.marker([lat, lng]).addTo(map);
});
</script>
@endpush