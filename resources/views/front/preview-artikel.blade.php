@extends('layouts.main')

@php
    $pageTitle = ($case->title ?? $case->slug ?? 'Artikel') . ' — Auriga CTIS';
    $pageDescription = 'Artikel dari Auriga CTIS tentang penegakan hukum lingkungan hidup di Indonesia.';
    $ogImage = $case->image ? asset('storage/' . $case->image) : asset('img/image.png');
    $ogType = 'article';
@endphp

@section('content')

<div id="artikel-page" class="mt-16 bg-white">
    {{-- ============================= --}}
    {{-- BAGIAN PARALLAX HEADER --}}
    {{-- ============================= --}}
    <section x-data="{ offset: 0 }" x-init="
                window.addEventListener('scroll', () => {
                    if (window.innerWidth >= 768) {
                        offset = window.scrollY * 0.3
                    }
                })
            "
        class="relative min-h-[40vh] md:min-h-[70vh] overflow-hidden bg-[#0B1E07]">

        {{-- ponytail: parallax scroll listener retained verbatim --}}

        <!-- Background Image -->
        <div class="absolute inset-0 w-full h-full bg-center bg-cover z-0"
            :style="`transform: translateY(${offset}px); background-image: url('{{ asset('storage/' . $case->image) }}')`">
        </div>

        <!-- Console gradient + blueprint grid overlay -->
        <div
            class="absolute inset-0 z-10 console-grid bg-gradient-to-t from-[#0B1E07] via-[#0B1E07]/70 to-[#0B1E07]/40">
        </div>

        <!-- Mono meta bar -->
        <div
            class="absolute bottom-0 left-0 right-0 z-20 border-t border-white/10 bg-[#0B1E07]/80 backdrop-blur-sm py-4 px-4 md:px-8">
            <div class="max-w-7xl mx-auto flex flex-wrap items-center justify-between gap-3">
                <span class="font-data uppercase tracking-[0.2em] text-[10px] text-[#9BDB4D]">Auriga CTIS / Artikel</span>

                <div class="flex flex-wrap items-center gap-3 font-data text-[11px] text-white/70">
                    @if (!empty($case->category_name))
                        <span>{{ $case->category_name }}</span>
                    @endif
                    @if (!empty($case->created_at))
                        @if (!empty($case->category_name))
                            <span class="text-white/30">/</span>
                        @endif
                        <span>{{ optional($case->created_at)->format('d M Y') }}</span>
                    @endif
                </div>
            </div>
        </div>
    </section>

    {{-- Title band --}}
    <header class="bg-white border-b border-[#E2E6DA]">
        <div class="max-w-4xl mx-auto px-4 py-12 md:py-16 text-center">
            <p class="font-data uppercase tracking-[0.2em] text-xs text-[#264c16] mb-4">Artikel</p>
            <h1 class="font-display text-3xl md:text-5xl font-bold text-[#0B1E07] leading-tight">
                {{ $case->title ?? $case->slug }}
            </h1>
        </div>
    </header>

    {{-- Article body --}}
    <article class="
          prose
          max-w-3xl mx-auto
          px-4
          py-12 md:py-16
          text-[#0B1E07]
          prose-headings:font-display
          prose-a:text-[#2F6C14]

          md:text-base text-sm
          text-left

          prose-p:leading-relaxed md:prose-p:leading-relaxed
          prose-p:tracking-[0.020em]
          prose-p:my-[1em]

          prose-h2:text-[24px]
          prose-h2:mt-8 prose-h2:mb-4 prose-h2:font-bold

          prose-h3:text-[21px]
          prose-h3:mt-6 prose-h3:mb-3 prose-h3:font-semibold
        ">
        {!! $case->content !!}
    </article>

    {{-- Back to articles --}}
    <div class="max-w-3xl mx-auto px-4 pb-16 md:pb-24">
        <div class="border-t border-[#E2E6DA] py-8 md:py-12">
            <a href="{{ route('public.artikel.list', ['locale' => app()->getLocale()]) }}"
                class="inline-flex items-center gap-2 font-data text-xs uppercase tracking-[0.2em] text-[#6b7268] hover:text-[#0B1E07] transition-colors">
                <span class="text-[#9BDB4D]">&larr;</span>
                Kembali ke Artikel
            </a>
        </div>
    </div>
</div>

@endsection
