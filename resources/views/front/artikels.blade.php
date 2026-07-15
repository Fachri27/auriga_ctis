@extends('layouts.main')

@php
    $pageTitle = 'Artikel & Berita Lingkungan — Auriga CTIS';
    $pageDescription = 'Baca artikel dan berita terbaru seputar penegakan hukum lingkungan hidup di Indonesia.';
@endphp

@section('content')
    <div class="max-w-7xl mx-auto mt-30 px-4 mb-20">

        {{-- Section Header --}}
        <div class="flex items-center gap-4 mb-10">
            <div class="w-8 h-px bg-slate-400"></div>
            <h2 class="text-slate-400 text-xs font-semibold tracking-[0.25em] uppercase">Artikel</h2>
            <div class="flex-1 h-px bg-gradient-to-r from-slate-200 to-transparent"></div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">

            @foreach ($artikels as $c)
                <div
                    class="group bg-white border border-gray-200 border-t-4 border-t-gray-900 rounded-sm flex flex-col hover:shadow-[4px_4px_0_#111] hover:-translate-x-0.5 hover:-translate-y-0.5 transition-all duration-200">

                    {{-- Image --}}
                    <div class="relative w-full aspect-video overflow-hidden">
                        <img src="{{ asset('storage/' . $c->image) }}" alt="{{ strip_tags($c->title) }}"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent"></div>

                        {{-- Badge Artikel --}}
                        <span
                            class="absolute top-3 left-3 px-2 py-0.5 text-xs font-bold tracking-widest uppercase bg-gray-900 text-white">
                            Artikel
                        </span>

                        {{-- Category Badge --}}
                        @if ($c->category_name)
                            <span
                                class="absolute top-3 right-3 px-2 py-0.5 text-xs font-bold tracking-widest uppercase border border-white text-white bg-black/30 backdrop-blur-sm">
                                {{ $c->category_name }}
                            </span>
                        @endif
                    </div>

                    {{-- Content --}}
                    <div class="p-5 flex flex-col flex-1">
                        {{-- Title --}}
                        <h3 class="text-lg font-bold text-gray-900 leading-tight mb-3 tracking-tight">
                            {!! strip_tags($c->title) !!}
                        </h3>

                        {{-- Excerpt --}}
                        <p class="text-sm text-gray-500 leading-relaxed flex-1 mb-4">
                            {!! Str::limit(strip_tags($c->excerpt), 300) !!}
                        </p>

                        {{-- Footer --}}
                        <div class="pt-3 border-t border-gray-100">
                            @if ($c->type === 'internal')
                                <a href="{{ route('public.artikel.detail', ['slug' => $c->slug]) }}"
                                    class="text-xs font-bold uppercase tracking-widest text-[#032A36] hover:text-[#264c16] transition-colors after:content-['_→']">
                                    Baca Selengkapnya
                                </a>
                            @else
                                <a href="{{ $c->link }}" target="_blank" rel="noopener"
                                    class="text-xs font-bold uppercase tracking-widest text-[#032A36] hover:text-[#264c16] transition-colors after:content-['_→']">
                                    Baca Selengkapnya
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach

        </div>
    </div>
@endsection
