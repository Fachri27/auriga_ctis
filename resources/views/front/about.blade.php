@extends('layouts.main')

@php
    $pageTitle = 'Tentang Kami — Auriga CTIS';
    $pageDescription = 'Auriga CTIS adalah platform transparansi kasus hukum lingkungan hidup di Indonesia. Pelajari misi dan komitmen kami untuk penegakan hukum lingkungan.';
@endphp

@section('content')

<style>
.about-content img,
.about-content video,
.about-content iframe {
    max-width: 100%;
    height: auto;
}
.about-content p,
.about-content div {
    word-break: break-word;
    overflow-wrap: break-word;
}
</style>

{{-- ponytail: reusing existing .about-content style and translation getters unchanged; only markup/classes restyled --}}

<section id="about-page" class="mt-16 bg-[#F5F7F1]">
    {{-- Hero band --}}
    <div class="bg-[#0B1E07] console-grid text-white">
        <div class="max-w-7xl mx-auto px-4 py-16 md:py-24 text-center">
            <p class="font-data uppercase tracking-[0.2em] text-xs text-[#9BDB4D] mb-4">{{ app()->getLocale() === 'id' ? 'Tentang Platform' : 'About the Platform' }}</p>
            <h1 class="font-display text-3xl md:text-5xl lg:text-6xl font-semibold">{{ __('messages.about') }}</h1>
            <p class="mt-5 max-w-2xl mx-auto text-sm md:text-base text-white/80">
                {{ app()->getLocale() === 'id' ? 'Auriga CTIS menghadirkan transparansi data kasus hukum lingkungan hidup untuk memperkuat akuntabilitas dan penegakan hukum di Indonesia.' : 'Auriga CTIS brings transparency to environmental law cases to strengthen accountability and enforcement in Indonesia.' }}
            </p>
        </div>
    </div>

    {{-- About content band --}}
    <div class="bg-white">
        <div class="max-w-3xl mx-auto px-4 py-12 md:py-20">
            <div class="about-content text-[#0B1E07] text-sm md:text-base leading-relaxed">
                @php $content = $about?->translation(app()->getLocale())?->content; @endphp
                {!! $content ?: '<em class="text-[#6b7268]">' . (app()->getLocale() === 'id' ? 'Belum ada teks about' : 'No about text yet') . '</em>' !!}
            </div>

            <div class="mt-10">
                <a
                    href="#"
                    class="inline-flex items-center gap-2 bg-[#9BDB4D] text-[#0B1E07] hover:bg-[#9BDB4D]/90 font-medium text-sm md:text-base px-6 py-3 rounded-sm transition"
                >
                    Hubungi Kami
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                    </svg>
                </a>
            </div>
        </div>
    </div>

    {{-- Vision & Mission cards band --}}
    <div class="bg-[#F5F7F1]">
        <div class="max-w-7xl mx-auto px-4 py-12 md:py-20">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Visi --}}
                <div class="bg-white border border-[#E2E6DA] rounded-sm p-5 md:p-8 about-content">
                    <p class="font-data text-[10px] uppercase tracking-widest text-[#6b7268] mb-3">{{ app()->getLocale() === 'id' ? 'Visi' : 'Vision' }}</p>
                    <h2 class="font-display text-2xl md:text-3xl font-semibold text-[#0B1E07] mb-4">{{ app()->getLocale() === 'id' ? 'Visi Kami' : 'Our Vision' }}</h2>
                    <div class="text-sm md:text-base text-[#374151] leading-relaxed">
                        @php $vision = $about?->translation(app()->getLocale())?->vision; @endphp
                        {!! $vision ?: '<em class="text-[#6b7268]">' . (app()->getLocale() === 'id' ? 'Belum ada teks visi' : 'No vision text yet') . '</em>' !!}
                    </div>
                </div>

                {{-- Misi --}}
                <div class="bg-white border border-[#E2E6DA] rounded-sm p-5 md:p-8 about-content">
                    <p class="font-data text-[10px] uppercase tracking-widest text-[#6b7268] mb-3">{{ app()->getLocale() === 'id' ? 'Misi' : 'Mission' }}</p>
                    <h2 class="font-display text-2xl md:text-3xl font-semibold text-[#0B1E07] mb-4">{{ app()->getLocale() === 'id' ? 'Misi Kami' : 'Our Mission' }}</h2>
                    <div class="text-sm md:text-base text-[#374151] leading-relaxed">
                        @php $mission = $about?->translation(app()->getLocale())?->mission; @endphp
                        {!! $mission ?: '<em class="text-[#6b7268]">' . (app()->getLocale() === 'id' ? 'Belum ada teks misi' : 'No mission text yet') . '</em>' !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection
