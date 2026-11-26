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

    // Ikon garis (gaya Lucide), urut sesuai butir monitor.items dan work.steps di file bahasa.
    $icons = [
        'trees' => '<path d="M10 10v.2A3 3 0 0 1 8.9 16H5a3 3 0 0 1-1-5.8V10a3 3 0 0 1 6 0Z"/><path d="M7 16v6"/><path d="M13 19v3"/><path d="M12 19h8.3a1 1 0 0 0 .7-1.7L18 14h.3a1 1 0 0 0 .7-1.7L16 9h.2a1 1 0 0 0 .8-1.7L13 3l-1.4 1.5"/>',
        'pickaxe' => '<path d="M14.531 12.469 6.619 20.38a1 1 0 1 1-3-3l7.912-7.912"/><path d="M15.686 4.314A12.5 12.5 0 0 0 5.461 2.958 1 1 0 0 0 5.58 4.71a22 22 0 0 1 6.318 3.393"/><path d="M17.7 3.7a1 1 0 0 0-1.4 0l-4.6 4.6a1 1 0 0 0 0 1.4l2.6 2.6a1 1 0 0 0 1.4 0l4.6-4.6a1 1 0 0 0 0-1.4z"/><path d="M19.686 8.314a12.501 12.501 0 0 1 1.356 10.225 1 1 0 0 1-1.751-.119 22 22 0 0 0-3.393-6.319"/>',
        'factory' => '<path d="M2 20a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8l-7 5V8l-7 5V4a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2Z"/><path d="M17 18h1"/><path d="M12 18h1"/><path d="M7 18h1"/>',
        'palm' => '<path d="M13 8c0-2.76-2.46-5-5.5-5S2 5.24 2 8h2l1-1 1 1h4"/><path d="M13 7.14A5.82 5.82 0 0 1 16.5 6c3.04 0 5.5 2.24 5.5 5h-3l-1-1-1 1h-3"/><path d="M5.89 9.71c-2.15 2.15-2.3 5.47-.35 7.43l4.24-4.25.7-.7.71-.71 2.12-2.12c-1.95-1.96-5.27-1.8-7.42.35"/><path d="M11 15.5c.5 2.5-.17 4.5-1 6.5h4c2-5.5-.5-12-1-14"/>',
        'paw' => '<circle cx="11" cy="4" r="2"/><circle cx="18" cy="8" r="2"/><circle cx="20" cy="16" r="2"/><path d="M9 10a5 5 0 0 1 5 5v3.5a3.5 3.5 0 0 1-6.84 1.045Q6.52 17.48 4.46 16.84A3.5 3.5 0 0 1 5.5 10Z"/>',
        'inbox' => '<polyline points="22 12 16 12 14 15 10 15 8 12 2 12"/><path d="M5.45 5.11 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/>',
        'clipboard' => '<rect width="8" height="4" x="8" y="2" rx="1" ry="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><path d="m9 14 2 2 4-4"/>',
        'eye' => '<path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/>',
        'chart' => '<path d="M3 3v18h18"/><path d="M18 17V9"/><path d="M13 17V5"/><path d="M8 17v-3"/>',
        'megaphone' => '<path d="m3 11 18-5v12L3 14v-3z"/><path d="M11.6 16.8a3 3 0 1 1-5.8-1.6"/>',
    ];
    $monitorIcons = ['trees', 'pickaxe', 'factory', 'palm', 'paw'];
    $stepIcons = ['inbox', 'clipboard', 'eye', 'chart', 'megaphone'];
    $icon = fn (?string $name) => $name
        ? '<span class="ab-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">'.$icons[$name].'</svg></span>'
        : '';
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
.ab-main p { line-height: 1.75; }
.ab-section { scroll-margin-top: 6rem; margin-top: 2.25rem; padding-top: 2.25rem; border-top: 1px solid #E2E6DA; }
.ab-h2 { font-size: 1.625rem; line-height: 1.25; font-weight: 600; color: #0B1E07; }
.ab-h3 { font-size: 1.125rem; line-height: 1.4; font-weight: 600; color: #0B1E07; }
.ab-quote { margin-top: 1rem; padding: 1.25rem 1.5rem; border-left: 3px solid #9BDB4D; background: #F5F7F1; font-size: 1.125rem; color: #0B1E07; }
.ab-grid { display: grid; gap: .875rem; grid-template-columns: minmax(0, 1fr); margin-top: 1.25rem; }
@media (min-width: 640px) {
    .ab-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .ab-grid > :last-child:nth-child(odd) { grid-column: 1 / -1; }
}
.ab-box { padding: 1.125rem 1.25rem; border: 1px solid #E2E6DA; border-radius: 4px; background: #fff; }
.ab-box-soft { background: #F5F7F1; }
.ab-box-soft::before { content: ""; display: block; width: 2rem; height: 3px; margin-bottom: .75rem; border-radius: 2px; background: #9BDB4D; }
.ab-icon { display: flex; flex-shrink: 0; align-items: center; justify-content: center; width: 2.5rem; height: 2.5rem; border-radius: 8px; background: #EAF5DC; color: #264c16; }
.ab-icon svg { width: 1.25rem; height: 1.25rem; }
/* Alur kerja: kotak bersambung panah. Layar sempit menurun; layar lebar berbentuk S (dua kolom, arah tiap baris bergantian). */
.ab-flow { --gap: 2rem; display: grid; gap: var(--gap); grid-template-columns: minmax(0, 1fr); margin-top: 1.25rem; }
.ab-flow li { position: relative; padding: 1rem 1.25rem; border: 1px solid #E2E6DA; border-radius: 6px; background: #F5F7F1; }
.ab-flow-node { display: flex; align-items: center; gap: .875rem; padding-right: 1.5rem; }
.ab-flow-num { position: absolute; top: .75rem; right: 1rem; font-size: .75rem; font-weight: 600; color: #9AA294; }
.ab-flow-text { margin-top: .25rem; padding-left: 3.375rem; font-size: .8125rem; line-height: 1.65 !important; color: #6b7268; }
.ab-flow-arrow { position: absolute; top: 100%; left: calc(2.5rem - 1px); width: 2px; height: calc(var(--gap) - 7px); background: #264c16; }
.ab-flow-arrow::after { content: ""; position: absolute; top: 100%; left: -4px; border-style: solid; border-color: #264c16 transparent transparent; border-width: 7px 5px 0; }
@media (min-width: 768px) {
    .ab-flow { --col-gap: 2.5rem; --row-gap: 2.25rem; --bend: 1.5rem; grid-template-columns: repeat(2, minmax(0, 1fr)); grid-auto-flow: dense; gap: var(--row-gap) var(--col-gap); padding: 0 var(--bend); }
    .ab-flow li { --mid: 2.375rem; padding: 1.125rem 1.25rem; } /* --mid: tinggi tengah ikon, tempat panah menempel */
    .ab-flow-node { align-items: flex-start; }
    .ab-flow-node h3 { padding-top: .5rem; }
    .ab-flow-text { margin-top: .625rem; padding-left: 0; }
    /* Urutan S: 1 kiri, 2 kanan, 3 kanan (baris berikutnya), 4 kiri, dst. */
    .ab-flow li:nth-child(4n+1), .ab-flow li:nth-child(4n+4) { grid-column: 1; }
    .ab-flow li:nth-child(4n+2), .ab-flow li:nth-child(4n+3) { grid-column: 2; }
    .ab-flow-arrow { left: auto; width: calc(var(--col-gap) - 7px); height: 2px; top: calc(var(--mid) - 1px); }
    .ab-flow-arrow::after { top: -4px; left: auto; }
    /* Panah mendatar: ke kanan di baris ganjil, ke kiri di baris genap. */
    .ab-flow li:nth-child(4n+1) .ab-flow-arrow { left: 100%; }
    .ab-flow li:nth-child(4n+1) .ab-flow-arrow::after { left: 100%; border-color: transparent transparent transparent #264c16; border-width: 5px 0 5px 7px; }
    .ab-flow li:nth-child(4n+3) .ab-flow-arrow { right: 100%; }
    .ab-flow li:nth-child(4n+3) .ab-flow-arrow::after { right: 100%; border-color: transparent #264c16 transparent transparent; border-width: 5px 7px 5px 0; }
    /* Belokan di ujung baris: lengkung dari ikon kotak ini ke ikon kotak di bawahnya (tinggi baris boleh berbeda). */
    .ab-flow li:nth-child(4n+2) .ab-flow-arrow, .ab-flow li:nth-child(4n+4) .ab-flow-arrow { width: var(--bend); height: calc(100% + var(--row-gap) + 4px); background: none; border: 2px solid #264c16; }
    .ab-flow li:nth-child(4n+2) .ab-flow-arrow { left: 100%; border-left: 0; border-radius: 0 14px 14px 0; }
    .ab-flow li:nth-child(4n+2) .ab-flow-arrow::after { top: auto; bottom: -6px; left: 0; border-color: transparent #264c16 transparent transparent; border-width: 5px 7px 5px 0; }
    .ab-flow li:nth-child(4n+4) .ab-flow-arrow { right: 100%; border-right: 0; border-radius: 14px 0 0 14px; }
    .ab-flow li:nth-child(4n+4) .ab-flow-arrow::after { top: auto; bottom: -6px; right: 0; border-color: transparent transparent transparent #264c16; border-width: 5px 0 5px 7px; }
}
.ab-checks { margin-top: 1.25rem; display: grid; gap: .875rem 2rem; grid-template-columns: minmax(0, 1fr); }
@media (min-width: 640px) { .ab-checks { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
.ab-checks li { display: grid; grid-template-columns: 1.5rem minmax(0, 1fr); gap: .75rem; line-height: 1.7; }
.ab-check { display: flex; align-items: center; justify-content: center; width: 1.5rem; height: 1.5rem; margin-top: .125rem; border-radius: 999px; background: #EAF5DC; color: #264c16; }
.ab-bullets { margin-top: .75rem; }
.ab-bullets li { position: relative; padding-left: 1.25rem; line-height: 1.75; }
.ab-bullets li + li { margin-top: .375rem; }
.ab-bullets li::before { content: ""; position: absolute; left: 0; top: .7em; width: 6px; height: 6px; background: #264c16; }
.ab-note { margin-top: 1.75rem; padding: 1.25rem 1.5rem; border: 1px solid #E2E6DA; border-radius: 4px; background: #FBFBF8; }
.ab-cms > * + * { margin-top: 1rem; }
.ab-cms img, .ab-cms video, .ab-cms iframe { max-width: 100%; height: auto; }
.ab p, .ab li { overflow-wrap: break-word; }
</style>

<section id="about-page" class="mt-16 bg-white">
    <x-front.page-hero :label="$t['label']" :title="$t['title']" :description="$t['tagline']" />

    <div class="ab max-w-7xl mx-auto px-4 py-10 md:py-12">
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
                        <p @class(['mt-4' => ! $loop->first])>{{ $paragraph }}</p>
                    @endforeach
                @endif

                <section id="tujuan" class="ab-section">
                    <h2 class="ab-h2 font-display">{{ $t['purpose']['title'] }}</h2>
                    <div class="ab-quote font-display ab-cms">{!! $purpose ?? '<p>'.e($t['purpose']['text']).'</p>' !!}</div>
                </section>

                <section id="komitmen" class="ab-section">
                    <h2 class="ab-h2 font-display">{{ $t['commitment']['title'] }}</h2>
                    <div class="mt-3 ab-cms">{!! $commitment ?? '<p>'.e($t['commitment']['text']).'</p>' !!}</div>
                </section>

                <section id="pantau" class="ab-section">
                    <h2 class="ab-h2 font-display">{{ $t['monitor']['title'] }}</h2>
                    <p class="mt-3">{{ $t['monitor']['intro'] }}</p>
                    <ul class="ab-grid">
                        @foreach ($t['monitor']['items'] as $i => $item)
                            <li class="ab-box">
                                {!! $icon($monitorIcons[$i] ?? null) !!}
                                <h3 class="ab-h3 font-display mt-3" style="font-size:1rem">{{ $item['title'] }}</h3>
                                <p class="mt-2 text-sm text-[#6b7268]" style="line-height:1.7">{{ $item['text'] }}</p>
                            </li>
                        @endforeach
                    </ul>
                </section>

                <section id="cara-kerja" class="ab-section">
                    <h2 class="ab-h2 font-display">{{ $t['work']['title'] }}</h2>
                    <p class="mt-3">{{ $t['work']['intro'] }}</p>
                    <ol class="ab-flow">
                        @foreach ($t['work']['steps'] as $i => $step)
                            <li>
                                <span class="ab-flow-num font-data" aria-hidden="true">{{ $i + 1 }}</span>
                                <div class="ab-flow-node">
                                    {!! $icon($stepIcons[$i] ?? null) !!}
                                    <h3 class="ab-h3 font-display" style="font-size:.9375rem">{{ $step['title'] }}</h3>
                                </div>
                                <p class="ab-flow-text">{{ $step['text'] }}</p>
                                @unless ($loop->last)
                                    <span class="ab-flow-arrow" aria-hidden="true"></span>
                                @endunless
                            </li>
                        @endforeach
                    </ol>
                </section>

                <section id="penerima-manfaat" class="ab-section">
                    <h2 class="ab-h2 font-display">{{ $t['beneficiaries']['title'] }}</h2>
                    <p class="mt-3">{{ $t['beneficiaries']['intro'] }}</p>
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

                    <h3 class="ab-h3 font-display mt-5">{{ $t['content']['terms_title'] }}</h3>
                    <dl class="ab-grid" style="margin-top:.75rem">
                        @foreach ($t['content']['terms'] as $term)
                            <div class="ab-box ab-box-soft">
                                <dt class="font-display text-lg font-semibold text-[#0B1E07]">{{ $term['term'] }}</dt>
                                <dd class="mt-1 text-sm text-[#374151]" style="line-height:1.7">{{ $term['text'] }}</dd>
                            </div>
                        @endforeach
                    </dl>

                    <h3 class="ab-h3 font-display mt-6">{{ $t['content']['sources']['title'] }}</h3>
                    <p class="mt-2">{{ $t['content']['sources']['intro'] }}</p>
                    <ul class="ab-bullets">
                        @foreach ($t['content']['sources']['items'] as $source)
                            <li>{{ $source }}</li>
                        @endforeach
                    </ul>
                    <p class="mt-3">{{ $t['content']['sources']['method'] }}</p>

                    <h3 class="ab-h3 font-display mt-6">{{ $t['content']['limits']['title'] }}</h3>
                    <p class="mt-2">{{ $t['content']['limits']['text'] }}</p>

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
