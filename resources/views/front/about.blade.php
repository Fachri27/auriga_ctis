@extends('layouts.main')

@php
    $id = app()->getLocale() === 'id';

    // Teks halaman: resources/lang/{id,en}/about.php.
    $t = __('about');

    $pageTitle = ($id ? 'Tentang Kami' : 'About Us') . ' — greendefender';
    $pageDescription = $t['meta_description'];

    // Intro, Tujuan, dan Komitmen bisa diganti lewat CMS (About); bila kosong dipakai teks bawaan.
    $cms = $about?->translation(app()->getLocale());
    $override = fn (?string $html) => filled(strip_tags((string) $html)) ? $html : null;
    $intro = $override($cms?->content);
    $purpose = $override($cms?->vision);
    $commitment = $override($cms?->mission);

    // Bagian yang tampil di daftar isi samping.
    $sections = [
        'tujuan' => $t['purpose']['title'],
        'komitmen' => $t['commitment']['title'],
        'pantau' => $t['monitor']['title'],
        'cara-kerja' => $t['work']['title'],
        'penerima-manfaat' => $t['beneficiaries']['title'],
        'konten' => $t['content']['title'],
    ];
@endphp

@section('content')

<style>
/* Halaman About: satu gaya untuk semua bagian (garis tipis, aksen hijau, kotak abu-hijau muda). */
.ab { color: #374151; }
.ab-layout { display: grid; gap: 3rem; grid-template-columns: minmax(0, 1fr); }
.ab-toc { display: none; }
@media (min-width: 1024px) {
    .ab-layout { grid-template-columns: 220px minmax(0, 1fr); gap: 4rem; }
    .ab-toc { display: block; position: sticky; top: 6rem; align-self: start; }
}
.ab-toc a { display: block; padding: .5rem 0 .5rem .875rem; border-left: 2px solid #E2E6DA; font-size: .8125rem; line-height: 1.4; color: #6b7268; transition: color .15s, border-color .15s; }
.ab-toc a:hover, .ab-toc a[aria-current="true"] { color: #0B1E07; border-left-color: #264c16; }
.ab-main { max-width: 760px; }
.ab-main p { line-height: 1.8; }
.ab-lead { font-size: 1.25rem; line-height: 1.6 !important; color: #0B1E07; }
.ab-section { scroll-margin-top: 6rem; margin-top: 3.5rem; padding-top: 3.5rem; border-top: 1px solid #E2E6DA; }
.ab-h2 { font-size: 1.75rem; line-height: 1.25; font-weight: 600; color: #0B1E07; }
.ab-h3 { font-size: 1.125rem; line-height: 1.4; font-weight: 600; color: #0B1E07; }
.ab-quote { margin-top: 1.5rem; padding: 1.5rem 1.75rem; border-left: 3px solid #9BDB4D; background: #F5F7F1; font-size: 1.125rem; color: #0B1E07; }
.ab-grid { display: grid; gap: 1rem; grid-template-columns: minmax(0, 1fr); margin-top: 1.75rem; }
@media (min-width: 640px) {
    .ab-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .ab-grid > :last-child:nth-child(odd) { grid-column: 1 / -1; }
}
.ab-box { padding: 1.25rem 1.5rem; border: 1px solid #E2E6DA; border-radius: 4px; background: #fff; }
.ab-box-soft { background: #F5F7F1; }
.ab-box::before { content: ""; display: block; width: 2rem; height: 3px; margin-bottom: .875rem; border-radius: 2px; background: #9BDB4D; }
.ab-steps { margin-top: 1.75rem; }
.ab-steps li { position: relative; display: grid; grid-template-columns: 2.5rem minmax(0, 1fr); gap: 1.25rem; padding-bottom: 1.75rem; }
.ab-steps li:last-child { padding-bottom: 0; }
.ab-steps li:not(:last-child)::after { content: ""; position: absolute; left: calc(1.25rem - 1px); top: 2.75rem; bottom: .25rem; width: 2px; background: #E2E6DA; }
.ab-step-num { display: flex; align-items: center; justify-content: center; width: 2.5rem; height: 2.5rem; border-radius: 999px; border: 1.5px solid #264c16; background: #fff; font-size: .8125rem; font-weight: 600; color: #264c16; }
.ab-checks { margin-top: 1.75rem; display: grid; gap: 1rem 2rem; grid-template-columns: minmax(0, 1fr); }
@media (min-width: 640px) { .ab-checks { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
.ab-checks li { display: grid; grid-template-columns: 1.5rem minmax(0, 1fr); gap: .75rem; line-height: 1.7; }
.ab-check { display: flex; align-items: center; justify-content: center; width: 1.5rem; height: 1.5rem; margin-top: .125rem; border-radius: 999px; background: #EAF5DC; color: #264c16; }
.ab-bullets { margin-top: 1rem; }
.ab-bullets li { position: relative; padding-left: 1.25rem; line-height: 1.8; }
.ab-bullets li + li { margin-top: .5rem; }
.ab-bullets li::before { content: ""; position: absolute; left: 0; top: .7em; width: 6px; height: 6px; background: #264c16; }
.ab-note { margin-top: 2.5rem; padding: 1.5rem 1.75rem; border: 1px solid #E2E6DA; border-radius: 4px; background: #FBFBF8; }
.ab-cms > * + * { margin-top: 1rem; }
.ab-cms img, .ab-cms video, .ab-cms iframe { max-width: 100%; height: auto; }
.ab p, .ab li { overflow-wrap: break-word; }
</style>

<section id="about-page" class="mt-16 bg-white">
    <x-front.page-hero :label="$t['label']" :title="$t['title']" :description="$t['tagline']" />

    <div class="ab max-w-7xl mx-auto px-4 py-12 md:py-16">
        <div class="ab-layout">
            <nav class="ab-toc" aria-label="{{ $t['toc'] }}">
                <p class="font-data text-[10px] uppercase tracking-widest text-[#6b7268] mb-3">{{ $t['toc'] }}</p>
                @foreach ($sections as $key => $title)
                    <a href="#{{ $key }}">{{ $title }}</a>
                @endforeach
            </nav>

            <div class="ab-main text-sm md:text-base">
                {{-- Tentang greendefender --}}
                @if ($intro)
                    <div class="ab-cms">{!! $intro !!}</div>
                @else
                    @foreach ($t['intro'] as $paragraph)
                        <p @class(['ab-lead font-display' => $loop->first, 'mt-5' => ! $loop->first])>{{ $paragraph }}</p>
                    @endforeach
                @endif

                <section id="tujuan" class="ab-section">
                    <h2 class="ab-h2 font-display">{{ $t['purpose']['title'] }}</h2>
                    <div class="ab-quote font-display ab-cms">{!! $purpose ?? '<p>'.e($t['purpose']['text']).'</p>' !!}</div>
                </section>

                <section id="komitmen" class="ab-section">
                    <h2 class="ab-h2 font-display">{{ $t['commitment']['title'] }}</h2>
                    <div class="mt-5 ab-cms">{!! $commitment ?? '<p>'.e($t['commitment']['text']).'</p>' !!}</div>
                </section>

                <section id="pantau" class="ab-section">
                    <h2 class="ab-h2 font-display">{{ $t['monitor']['title'] }}</h2>
                    <p class="mt-5">{{ $t['monitor']['intro'] }}</p>
                    <ul class="ab-grid">
                        @foreach ($t['monitor']['items'] as $item)
                            <li class="ab-box">
                                <h3 class="ab-h3 font-display" style="font-size:1rem">{{ $item['title'] }}</h3>
                                <p class="mt-2 text-sm text-[#6b7268]" style="line-height:1.7">{{ $item['text'] }}</p>
                            </li>
                        @endforeach
                    </ul>
                </section>

                <section id="cara-kerja" class="ab-section">
                    <h2 class="ab-h2 font-display">{{ $t['work']['title'] }}</h2>
                    <p class="mt-5">{{ $t['work']['intro'] }}</p>
                    <ol class="ab-steps">
                        @foreach ($t['work']['steps'] as $i => $step)
                            <li>
                                <span class="ab-step-num font-data">{{ $i + 1 }}</span>
                                <div style="padding-top:.375rem">
                                    <h3 class="ab-h3 font-display" style="font-size:1rem">{{ $step['title'] }}</h3>
                                    <p class="mt-1 text-sm text-[#6b7268]" style="line-height:1.7">{{ $step['text'] }}</p>
                                </div>
                            </li>
                        @endforeach
                    </ol>
                </section>

                <section id="penerima-manfaat" class="ab-section">
                    <h2 class="ab-h2 font-display">{{ $t['beneficiaries']['title'] }}</h2>
                    <p class="mt-5">{{ $t['beneficiaries']['intro'] }}</p>
                    <ul class="ab-checks">
                        @foreach ($t['beneficiaries']['items'] as $item)
                            <li>
                                <span class="ab-check" aria-hidden="true">
                                    <svg width="12" height="12" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2.5 6.5l2.25 2.25L9.5 3.75"/></svg>
                                </span>
                                <p class="text-sm" style="line-height:1.7"><strong class="font-semibold text-[#0B1E07]">{{ $item['title'] }}</strong> {{ $item['text'] }}</p>
                            </li>
                        @endforeach
                    </ul>
                </section>

                <section id="konten" class="ab-section">
                    <h2 class="ab-h2 font-display">{{ $t['content']['title'] }}</h2>

                    <h3 class="ab-h3 font-display mt-8">{{ $t['content']['terms_title'] }}</h3>
                    <dl class="ab-grid" style="margin-top:1rem">
                        @foreach ($t['content']['terms'] as $term)
                            <div class="ab-box ab-box-soft">
                                <dt class="font-display text-lg font-semibold text-[#0B1E07]">{{ $term['term'] }}</dt>
                                <dd class="mt-1 text-sm text-[#374151]" style="line-height:1.7">{{ $term['text'] }}</dd>
                            </div>
                        @endforeach
                    </dl>

                    <h3 class="ab-h3 font-display mt-10">{{ $t['content']['sources']['title'] }}</h3>
                    <p class="mt-3">{{ $t['content']['sources']['intro'] }}</p>
                    <ul class="ab-bullets">
                        @foreach ($t['content']['sources']['items'] as $source)
                            <li>{{ $source }}</li>
                        @endforeach
                    </ul>
                    <p class="mt-4">{{ $t['content']['sources']['method'] }}</p>

                    @foreach (['limits', 'articles'] as $key)
                        <h3 class="ab-h3 font-display mt-10">{{ $t['content'][$key]['title'] }}</h3>
                        <p class="mt-3">{{ $t['content'][$key]['text'] }}</p>
                    @endforeach

                    <div class="ab-note">
                        <p class="font-data text-[10px] uppercase tracking-widest text-[#6b7268]">{{ $t['content']['disclaimer']['title'] }}</p>
                        <p class="mt-2 text-sm text-[#6b7268]" style="line-height:1.7">{{ $t['content']['disclaimer']['text'] }}</p>
                    </div>
                </section>
            </div>
        </div>
    </div>
</section>

@push('scripts')
<script>
    // Daftar isi: tandai bagian yang sedang dibaca.
    (() => {
        const links = [...document.querySelectorAll('.ab-toc a')];
        if (!links.length || !('IntersectionObserver' in window)) return;
        const observer = new IntersectionObserver((entries) => {
            entries.filter((entry) => entry.isIntersecting).forEach((entry) => {
                links.forEach((link) => link.setAttribute('aria-current', link.hash === '#' + entry.target.id ? 'true' : 'false'));
            });
        }, { rootMargin: '-30% 0px -60% 0px' });
        links.forEach((link) => { const target = document.querySelector(link.hash); if (target) observer.observe(target); });
    })();
</script>
@endpush

@endsection
