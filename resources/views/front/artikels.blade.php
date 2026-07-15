@extends('layouts.main')

@php
    $pageTitle = 'Artikel & Berita Lingkungan — Auriga CTIS';
    $pageDescription = 'Baca artikel dan berita terbaru seputar penegakan hukum lingkungan hidup di Indonesia.';
@endphp

@section('content')
    <div id="artikels-page" class="mt-16 bg-[#F5F7F1]">
        <div class="max-w-7xl mx-auto px-4 py-16 md:py-24">

            {{-- Section Header --}}
            <header class="mb-12 md:mb-16">
                <p class="font-data uppercase tracking-[0.2em] text-xs text-[#264c16] mb-3">Artikel</p>
                <h1 class="font-display text-3xl md:text-5xl font-bold text-[#0B1E07] mb-4">
                    Artikel & Berita Lingkungan
                </h1>
                <p class="text-[#6b7268] max-w-2xl">
                    Kumpulan tulisan, laporan, dan berita seputar penegakan hukum lingkungan hidup di Indonesia.
                </p>
            </header>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">

                @foreach ($artikels as $c)
                    <article
                        class="group bg-white border border-[#E2E6DA] rounded-sm flex flex-col overflow-hidden hover:border-[#9BDB4D] transition-colors duration-200">

                        {{-- Image --}}
                        <div class="relative w-full aspect-video overflow-hidden">
                            <img src="{{ asset('storage/' . $c->image) }}" alt="{{ strip_tags($c->title) }}"
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">

                            {{-- Badge Artikel --}}
                            <span
                                class="absolute top-3 left-3 px-2 py-0.5 font-data uppercase tracking-widest text-[10px] bg-[#0B1E07] text-white">
                                Artikel
                            </span>

                            {{-- Category Badge --}}
                            @if ($c->category_name)
                                <span
                                    class="absolute top-3 right-3 px-2 py-0.5 font-data uppercase tracking-widest text-[10px] border border-white text-white bg-black/30 backdrop-blur-sm">
                                    {{ $c->category_name }}
                                </span>
                            @endif
                        </div>

                        {{-- Content --}}
                        <div class="p-5 flex flex-col flex-1">
                            {{-- Title --}}
                            <h3 class="font-display text-lg font-semibold text-[#0B1E07] leading-tight mb-3">
                                {!! strip_tags($c->title) !!}
                            </h3>

                            {{-- Excerpt --}}
                            <p class="text-sm text-[#6b7268] leading-relaxed flex-1 mb-4">
                                {!! Str::limit(strip_tags($c->excerpt), 300) !!}
                            </p>

                            {{-- Footer --}}
                            <div class="pt-3 border-t border-[#E2E6DA]">
                                @if ($c->type === 'internal')
                                    <a href="{{ route('public.artikel.detail', ['slug' => $c->slug]) }}"
                                        class="inline-flex items-center gap-2 font-data text-[10px] uppercase tracking-widest text-[#0B1E07] hover:text-[#2F6C14] transition-colors">
                                        Baca Selengkapnya
                                        <span class="text-[#9BDB4D] text-lg leading-none">&rarr;</span>
                                    </a>
                                @else
                                    <a href="{{ $c->link }}" target="_blank" rel="noopener"
                                        class="inline-flex items-center gap-2 font-data text-[10px] uppercase tracking-widest text-[#0B1E07] hover:text-[#2F6C14] transition-colors">
                                        Baca Selengkapnya
                                        <span class="text-[#9BDB4D] text-lg leading-none">&rarr;</span>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </article>
                @endforeach

            </div>
        </div>
    </div>
@endsection
