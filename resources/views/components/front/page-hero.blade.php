@props(['label', 'title', 'description' => null, 'inner' => 'px-4'])

{{-- Hero halaman publik: jarak, ukuran judul, dan deskripsi sama di semua halaman.
     Slot "aside" (opsional) tampil di kanan, mis. waktu pembaruan data. --}}
<section {{ $attributes->merge(['class' => 'bg-[#0B1E07] console-grid text-white']) }}>
    <div class="max-w-7xl mx-auto {{ $inner }} py-16 md:py-20">
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
            <div>
                <p class="font-data text-xs uppercase tracking-[0.2em] text-[#9BDB4D] mb-3">{{ $label }}</p>
                <h1 class="font-display text-3xl md:text-4xl font-bold leading-tight mb-4">{{ $title }}</h1>
                @if ($description)
                    <p class="max-w-2xl text-white/80 text-sm md:text-base leading-relaxed">{{ $description }}</p>
                @endif
            </div>
            @isset($aside)
                <div class="flex-shrink-0">{{ $aside }}</div>
            @endisset
        </div>
    </div>
</section>
