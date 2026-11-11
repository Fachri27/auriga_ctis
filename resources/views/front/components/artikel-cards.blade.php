{{-- Kartu artikel: Analisis Auriga (dibaca di situs) dan berita media (tautan ke berita aslinya). --}}
@php
    // Potong teks maksimal 3 baris (kelas line-clamp-3 belum ada di CSS hasil build).
    $clamp3 = 'display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden';
@endphp
@foreach ($artikels as $a)
    @php
        $isAnalisis = $a->type === 'internal';
        $image = $a->image && ! \Illuminate\Support\Str::startsWith($a->image, ['http://', 'https://']) ? asset('storage/' . $a->image) : $a->image;
        $excerpt = trim(preg_replace('/\s+/', ' ', strip_tags(preg_replace('/<\/(p|div|li|h[1-6])>|<br\s*\/?>/i', ' ', (string) $a->excerpt))));
    @endphp
    <a href="{{ $isAnalisis ? route('public.artikel.detail', ['slug' => $a->slug]) : $a->link }}"
        @unless ($isAnalisis) target="_blank" rel="noopener nofollow" @endunless
        class="group bg-white border border-[#E2E6DA] rounded-sm flex flex-col overflow-hidden hover:border-[#9BDB4D] transition-colors duration-200">

        {{-- Gambar; bila tidak ada / gagal dimuat, tampil gambar cadangan --}}
        <div class="relative w-full aspect-video overflow-hidden">
            @include('front.components.artikel-image-fallback', ['label' => $a->category_name ?? ($isAnalisis ? __('Analisis') : __('Berita'))])
            @if ($image)
                <img src="{{ $image }}" alt="{{ strip_tags($a->title) }}" loading="lazy"
                    @unless ($isAnalisis) referrerpolicy="no-referrer" @endunless onerror="this.remove()"
                    class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
            @endif

            @if ($isAnalisis)
                <span class="absolute top-3 left-3 px-2 py-0.5 font-data uppercase tracking-widest text-[10px] font-semibold bg-[#9BDB4D] text-[#0B1E07]">
                    {{ __('Analisis Auriga') }}
                </span>
            @else
                <span class="absolute top-3 left-3 px-2 py-0.5 font-data uppercase tracking-widest text-[10px] bg-[#0B1E07] text-white">
                    {{ __('Berita') }}
                </span>
            @endif

            @if ($a->category_name)
                <span class="absolute top-3 right-3 px-2 py-0.5 font-data uppercase tracking-widest text-[10px] border border-white text-white bg-black/30 backdrop-blur-sm">
                    {{ $a->category_name }}
                </span>
            @endif
        </div>

        <div class="p-5 flex flex-col flex-1">
            <h3 class="font-display text-lg font-semibold text-[#0B1E07] leading-tight mb-3 line-clamp-2">
                {{ strip_tags($a->title) }}
            </h3>
            <div class="flex-1 mb-4">
                <p class="text-sm text-[#6b7268] leading-relaxed" style="{{ $clamp3 }}">
                    {{ $excerpt !== '' ? $excerpt : __('Diberitakan oleh :source. Klik untuk membaca selengkapnya.', ['source' => $a->source_name ?? __('media')]) }}
                </p>
            </div>
            <div class="pt-3 border-t border-[#E2E6DA] flex items-center justify-between gap-3 font-data text-[10px] uppercase tracking-widest text-[#6b7268]">
                <span class="text-[#264c16] font-semibold truncate">{{ $isAnalisis ? 'Auriga Nusantara' : ($a->source_name ?? __('Media')) }}</span>
                @if ($a->published_at)
                    <span class="flex-shrink-0">{{ \Illuminate\Support\Carbon::parse($a->published_at)->translatedFormat('d M Y') }}</span>
                @endif
            </div>
        </div>
    </a>
@endforeach
