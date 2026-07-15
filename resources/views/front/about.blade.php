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


<div class="max-w-6xl mx-auto md:mt-40 mt-30">
    <div class="font-sans md:text-5xl text-3xl font-semibold text-center">{{ __('messages.about') }}</div>
    <div class="md:text-center font-sans md:text-lg text-sm mx-5 text-justify font-light mt-5 about-content">
        @php $content = $about?->translation(app()->getLocale())?->content; @endphp
        {!! $content ?: '<em class="text-gray-400">' . (app()->getLocale() === 'id' ? 'Belum ada teks about' : 'No about text yet') . '</em>' !!}
    </div>
    <div class="text-center mt-10">
        <button
            class="bg-[#264c16] hover:bg-[#1f3d12] text-white font-bold md:py-5 py-3 md:px-8 px-5 font-sans md:text-lg text-sm rounded">
            Hubungi Kami
        </button>
    </div>

    <div class="md:grid grid-cols-2 gap-8 mt-10 mx-4 space-y-5 md:space-y-0 mb-5">
        <div class="p-6 rounded-lg shadow-xl about-content">
            <h2 class="text-2xl font-semibold mb-4 font-sans">{{ app()->getLocale() === 'id' ? 'Visi Kami' : 'Our Vision' }}</h2>
            <div class="text-sm md:text-lg font-light">
                @php $vision = $about?->translation(app()->getLocale())?->vision; @endphp
                {!! $vision ?: '<em class="text-gray-400">' . (app()->getLocale() === 'id' ? 'Belum ada teks visi' : 'No vision text yet') . '</em>' !!}
            </div>
        </div>
        <div class="p-6 rounded-lg shadow-xl about-content">
            <h2 class="text-2xl font-semibold mb-4 font-sans">{{ app()->getLocale() === 'id' ? 'Misi Kami' : 'Our Mission' }}</h2>
            <div class="text-sm md:text-lg font-light">
                @php $mission = $about?->translation(app()->getLocale())?->mission; @endphp
                {!! $mission ?: '<em class="text-gray-400">' . (app()->getLocale() === 'id' ? 'Belum ada teks misi' : 'No mission text yet') . '</em>' !!}
            </div>
        </div>
    </div>
</div>

@endsection
