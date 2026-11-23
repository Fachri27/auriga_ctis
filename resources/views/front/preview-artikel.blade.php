@extends('layouts.main')

@php
    $pageTitle = ($case->title ?? $case->slug ?? __('Artikel')) . ' — greendefender';
    $pageDescription = \Illuminate\Support\Str::limit(trim(html_entity_decode(strip_tags((string) ($case->excerpt ?: $case->content)))), 160)
        ?: __('Artikel dari greendefender tentang penegakan hukum lingkungan hidup di Indonesia.');
    $ogImage = $case->image
        ? (\Illuminate\Support\Str::startsWith($case->image, ['http://', 'https://']) ? $case->image : asset('storage/' . $case->image))
        : asset('img/image.png');
    $ogType = 'article';
    // Tautan yang dibagikan selalu alamat resmi artikel, tanpa parameter pelacak (?fbclid, ?utm_...) atau #.
    $shareUrl = route('public.artikel.detail', ['locale' => app()->getLocale(), 'slug' => $case->slug]);
    $shareTitle = $case->title ?? $case->slug;
@endphp

@section('structured-data')
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "Article",
    "headline": {{ json_encode($case->title ?? $case->slug ?? __('Artikel')) }},
    "description": {{ json_encode(mb_substr(strip_tags($case->content ?? ''), 0, 300)) }},
    "image": {{ json_encode($case->image ? asset('storage/' . $case->image) : asset('img/image.png')) }},
    "datePublished": {{ json_encode(optional($case->created_at)->toIso8601String()) }},
    "dateModified": {{ json_encode(optional($case->updated_at)->toIso8601String()) }},
    "author": {
        "@@type": "Organization",
        "name": "greendefender"
    },
    "publisher": {
        "@@type": "Organization",
        "name": "greendefender",
        "logo": { "@@type": "ImageObject", "url": {{ json_encode(asset('img/image.png')) }} }
    },
    "mainEntityOfPage": {
        "@@type": "WebPage",
        "@@id": {{ json_encode(url()->current()) }}
    },
    "articleSection": "Lingkungan"
}
</script>
@endsection

@section('content')

@php
    $coverUrl = $case->image
        ? (\Illuminate\Support\Str::startsWith($case->image, ['http://', 'https://']) ? $case->image : asset('storage/' . $case->image))
        : null;
    $description = trim(strip_tags((string) $case->content)) !== '' ? $case->content : $case->excerpt;
    // Hanya tautan web yang ditampilkan (mencegah javascript: dsb. dari isian tautan).
    $publicationLink = \Illuminate\Support\Str::startsWith((string) $case->link, ['http://', 'https://']) ? $case->link : null;
    $pdfs = array_filter(['Indonesia' => $case->pdf_id ?? null, 'English' => $case->pdf_en ?? null]);
@endphp

{{-- Tata letak publikasi: cover & unduhan PDF di kiri; judul, deskripsi, dan tautan publikasi di kanan.
     Grid & tombol ditulis di sini karena kelas Tailwind-nya belum ada di CSS hasil build. --}}
<style>
    #artikel-page .pub-grid { display: grid; gap: 2rem; padding-top: 2.5rem; border-top: 2px solid #2F6C14; }
    #artikel-page .pub-cover { max-width: 280px; margin: 0 auto; }
    @media (min-width: 768px) {
        #artikel-page .pub-grid { grid-template-columns: minmax(0, 1fr) minmax(0, 2fr); gap: 3rem; align-items: start; }
        #artikel-page .pub-cover { max-width: none; margin: 0; }
    }
    #artikel-page .pdf-btn {
        display: inline-flex; align-items: center; justify-content: center; min-width: 9rem;
        padding: .55rem 1.5rem; border: 2px solid #2F6C14; border-radius: 9999px; color: #2F6C14;
        font-size: .875rem; font-weight: 600; letter-spacing: .06em; text-transform: uppercase;
        transition: background-color .2s, color .2s;
    }
    #artikel-page .pdf-btn:hover, #artikel-page .pdf-btn:focus-visible { background: #2F6C14; color: #fff; }
</style>

<div id="artikel-page" class="mt-16 bg-white">
    <div class="max-w-6xl mx-auto px-4 py-12 md:py-16">
        <p class="font-data uppercase tracking-[0.2em] text-xs text-[#264c16] mb-6">
            {{ __('Analisis Auriga') }}
            @if (!empty($case->published_at))
                <span class="text-[#6b7268]">/ {{ \Illuminate\Support\Carbon::parse($case->published_at)->translatedFormat('d M Y') }}</span>
            @endif
        </p>

        <div class="pub-grid">
            {{-- Cover & unduhan --}}
            <div class="pub-cover w-full">
                @if ($coverUrl)
                    <img src="{{ $coverUrl }}" alt="{{ $case->title ?? $case->slug }}" class="w-full shadow-xl border border-[#E2E6DA]">
                @else
                    <div class="relative w-full shadow-xl" style="aspect-ratio: 3 / 4">
                        @include('front.components.artikel-image-fallback', ['label' => __('Analisis Auriga')])
                    </div>
                @endif

                @if ($pdfs)
                    <div class="mt-8">
                        <p class="font-data uppercase tracking-[0.2em] text-xs text-[#6b7268] mb-3">{{ __('Unduh PDF') }}</p>
                        <div class="flex flex-wrap gap-3">
                            @foreach ($pdfs as $language => $pdf)
                                <a href="{{ asset('storage/' . $pdf) }}" target="_blank" rel="noopener" class="pdf-btn"
                                    aria-label="{{ __('Unduh PDF :language', ['language' => $language]) }}">{{ $language }}</a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            {{-- Judul, deskripsi, tautan publikasi --}}
            <div>
                <h1 class="font-display text-3xl md:text-4xl font-bold text-[#2F6C14] leading-tight">
                    {{ $case->title ?? $case->slug }}
                </h1>

                @if (trim(strip_tags((string) $description)) !== '')
                    <div class="mt-8 prose max-w-none text-[#0B1E07] prose-a:text-[#2F6C14] md:text-base text-sm prose-p:leading-relaxed prose-p:tracking-[0.020em] prose-p:my-[1em]">
                        {!! $description !!}
                    </div>
                @endif

                @if ($publicationLink)
                    <p class="mt-6 text-sm md:text-base break-all">
                        <a href="{{ $publicationLink }}" target="_blank" rel="noopener"
                            class="text-[#2F6C14] underline underline-offset-4 hover:text-[#0B1E07]">{{ $publicationLink }}</a>
                    </p>
                @endif
            </div>
        </div>

        {{-- Bagikan, lalu kembali ke daftar --}}
        <section class="border-t border-[#E2E6DA] pt-8" style="margin-top: 3rem">
            <h2 class="font-data text-[11px] font-semibold uppercase tracking-widest text-[#6b7268] mb-4">{{ __('Bagikan artikel ini') }}</h2>
            @include('front.components.share-buttons', ['url' => $shareUrl, 'title' => $shareTitle])
        </section>
        <div class="border-t border-[#E2E6DA] mt-8 pt-8">
            <a href="{{ route('public.artikel.list', ['locale' => app()->getLocale(), 'jenis' => 'analisis']) }}"
                class="inline-flex items-center gap-2 font-data text-xs uppercase tracking-[0.2em] text-[#6b7268] hover:text-[#0B1E07] transition-colors">
                <span class="text-[#9BDB4D]">&larr;</span>
                {{ __('Kembali ke Artikel') }}
            </a>
        </div>
    </div>
</div>

@endsection
