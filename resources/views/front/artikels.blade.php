@extends('layouts.main')

@section('content')

<div class="max-w-7xl mx-auto mt-30 px-4 mb-20">

    {{-- Section Header --}}
    <div class="flex items-center gap-4 mb-10">
        <div class="w-8 h-px bg-slate-400"></div>
        <h2 class="text-slate-400 text-xs font-semibold tracking-[0.25em] uppercase">Artikel</h2>
        <div class="flex-1 h-px bg-gradient-to-r from-slate-200 to-transparent"></div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">

        @foreach ($artikels as $c)
        <div
            class="group bg-white flex flex-col overflow-hidden shadow-sm hover:-translate-y-1.5 hover:shadow-xl hover:shadow-blue-900/10 transition-all duration-300">

            <div class="relative w-full aspect-video overflow-hidden">
                <img src="{{ asset('storage/'. $c->image) }}" alt="{{ strip_tags($c->title) }}"
                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                <div
                    class="absolute inset-0 bg-gradient-to-t from-blue-950/30 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                </div>
                <span
                    class="absolute top-3 left-3 bg-[#003974] text-white text-[10px] font-semibold tracking-widest uppercase px-2.5 py-1">
                    Artikel
                </span>
                {{-- kategori badge --}}
                @if ($c->category_name)
                    <span class="absolute top-3 right-3 bg-[#003974] text-white text-[10px] font-semibold tracking-widest uppercase px-2.5 py-1">
                        {{ $c->category_name }}
                    </span>
                @endif
            </div>

            <div class="relative flex flex-col flex-1 p-5 border-t-[3px] border-[#003974]">
                <a href="{{ route('public.artikel.detail', ['slug'=>$c->slug]) }}"
                    class="text-[#003974] font-bold text-lg leading-snug tracking-wide uppercase hover:text-blue-700 transition-colors duration-200">
                    {!! $c->title !!}
                </a>
                <p class="mt-2.5 text-sm text-slate-500 leading-relaxed font-light flex-1">
                    {!! Str::limit(strip_tags($c->excerpt), 160) !!}
                </p>
                <div class="mt-4 pt-3.5 border-t border-slate-100">
                    @if ($c->type === 'internal')
                    <a href="{{ route('public.artikel.detail', ['slug'=>$c->slug]) }}"
                        class="inline-flex items-center gap-1.5 text-[11px] font-semibold tracking-[0.15em] uppercase text-[#003974] hover:[&>svg]:translate-x-1 [&>svg]:transition-transform [&>svg]:duration-200">
                        Baca Selengkapnya
                        <svg class="w-3 h-3 stroke-current fill-none stroke-2" viewBox="0 0 24 24"
                            stroke-linecap="round" stroke-linejoin="round">
                            <line x1="5" y1="12" x2="19" y2="12" />
                            <polyline points="12 5 19 12 12 19" />
                        </svg>
                    </a>
                    @else
                    <a href="{{ $c->link }}"
                        class="inline-flex items-center gap-1.5 text-[11px] font-semibold tracking-[0.15em] uppercase text-[#003974] hover:[&>svg]:translate-x-1 [&>svg]:transition-transform [&>svg]:duration-200">
                        Baca Selengkapnya
                        <svg class="w-3 h-3 stroke-current fill-none stroke-2" viewBox="0 0 24 24"
                            stroke-linecap="round" stroke-linejoin="round">
                            <line x1="5" y1="12" x2="19" y2="12" />
                            <polyline points="12 5 19 12 12 19" />
                        </svg>
                    </a>
                    @endif

                </div>
                <div
                    class="absolute bottom-0 right-0 w-0 h-0 border-b-[26px] border-l-[26px] border-l-transparent border-b-[#003974] group-hover:border-b-[32px] group-hover:border-l-[32px] transition-all duration-300">
                </div>
            </div>
        </div>
        @endforeach

    </div>
</div>

@endsection