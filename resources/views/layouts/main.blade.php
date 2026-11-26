<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="{{ $robots ?? 'index, follow' }}">
    <meta name="author" content="greendefender">
    <meta name="theme-color" content="#264c16">
    @php
        // Verifikasi Google Search Console. Fallback hardcoded agar meta tag selalu
        // ter-render walau config:cache stale atau GOOGLE_SITE_VERIFICATION belum
        // ada di .env server. Kode ini publik (bukan rahasia).
        $gsc = config('services.google_search_console.verification')
            ?: env('GOOGLE_SITE_VERIFICATION')
            ?: 'mT-qCKq2bk-V0tVNX8jUjigGd0y3uEW_CCfzXY5X_LQ';
    @endphp
    @if ($gsc)
        <meta name="google-site-verification" content="{{ $gsc }}">
    @endif

    {{-- Favicon --}}
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

    {{-- Primary SEO --}}
    <title>{{ $pageTitle ?? __('greendefender — Transparansi Kasus Hukum Lingkungan') }}</title>
    <meta name="description" content="{{ $pageDescription ?? __('greendefender: Platform transparansi kasus hukum lingkungan hidup di Indonesia. Lacak perkembangan kasus dari penyelidikan hingga putusan pengadilan.') }}">
    <meta name="keywords" content="{{ __('kasus lingkungan, hukum lingkungan, transparansi publik, environmental defender, greendefender, Indonesia, kasus korupsi lingkungan, ekologi, penegakan hukum') }}">
    <link rel="canonical" href="{{ url()->current() }}">

    {{-- Open Graph / Facebook --}}
    <meta property="og:site_name" content="greendefender">
    <meta property="og:title" content="{{ $ogTitle ?? $pageTitle ?? __('greendefender — Transparansi Kasus Hukum Lingkungan') }}">
    <meta property="og:description" content="{{ $ogDescription ?? $pageDescription ?? __('Platform transparansi kasus hukum lingkungan hidup di Indonesia.') }}">
    <meta property="og:image" content="{{ $ogImage ?? asset('img/image.png') }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:type" content="{{ $ogType ?? 'website' }}">
    <meta property="og:locale" content="{{ str_replace('_', '-', app()->getLocale()) == 'id' ? 'id_ID' : 'en_US' }}">

    {{-- Twitter Card --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:site" content="@auriga_id">
    <meta name="twitter:title" content="{{ $ogTitle ?? $pageTitle ?? 'greendefender' }}">
    <meta name="twitter:description" content="{{ $ogDescription ?? $pageDescription ?? __('Platform transparansi kasus hukum lingkungan hidup di Indonesia.') }}">
    <meta name="twitter:image" content="{{ $ogImage ?? asset('img/image.png') }}">

    <script async src="https://www.googletagmanager.com/gtag/js?id=G-M1HMVJ0KDK"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());

      gtag('config', 'G-M1HMVJ0KDK');
    </script>

    {{-- JSON-LD Structured Data --}}
    @yield('structured-data')

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css">
    <link rel="stylesheet"
        href="https://unpkg.com/@raruto/leaflet-gesture-handling@1.6.3/dist/leaflet-gesture-handling.min.css"
        type="text/css">

    <style>
        :root {
            --ink: #0B1E07;
            --ink-2: #143009;
            --brand: #264c16;
            --leaf: #9BDB4D;
            --leaf-deep: #2F6C14;
            --paper: #F5F7F1;
            --hairline: #E2E6DA;
        }
        body {
            font-family: 'Poppins', sans-serif;
            line-height: 24px;
        }
        [x-cloak] { display: none !important; }
        .font-display { font-family: 'Poppins', sans-serif; }
        .font-data    { font-family: 'Poppins', sans-serif; }
        /* Make every public page wrapper (id ending in -page) fill the available main height */
        main > div[id$="-page"], main > section[id$="-page"] { flex: 1 1 auto; }
        .console-grid {
            background-image:
                repeating-linear-gradient(0deg, transparent, transparent 55px, rgba(255,255,255,0.03) 56px),
                repeating-linear-gradient(90deg, transparent, transparent 55px, rgba(255,255,255,0.03) 56px);
            background-size: 56px 56px;
        }

        /* Dropdown seragam di halaman publik. Panah bawaan browser diganti panah sendiri yang berjarak pas dari tepi;
           tinggi 42px sama dengan kolom cari & tombol di sebelahnya. Varian kecil (gd-select-sm) untuk toolbar ringkas. */
        .gd-select {
            -webkit-appearance: none;
            appearance: none;
            min-width: 0;
            padding: 10px 40px 10px 14px;
            background: #fff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%236b7268' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E") no-repeat right 12px center / 16px 16px;
            border: 1px solid var(--hairline);
            border-radius: 2px;
            color: var(--ink);
            font-size: 14px;
            line-height: 20px;
            white-space: nowrap;
            text-overflow: ellipsis;
            overflow: hidden;
            cursor: pointer;
            transition: border-color .15s, box-shadow .15s, background-color .15s;
        }
        .gd-select:hover { border-color: #c5ccbb; }
        .gd-select:focus { outline: none; border-color: var(--leaf); box-shadow: 0 0 0 1px var(--leaf); }
        .gd-select:disabled { opacity: .55; cursor: not-allowed; }
        .gd-select option { color: var(--ink); background: #fff; font-weight: 400; }
        /* Filter yang sedang dipakai (bukan pilihan "Semua …") ditandai agar terlihat sekilas. */
        .gd-select[data-filter]:has(option:checked:not([value=""])) { border-color: var(--leaf-deep); background-color: #F5F9EE; font-weight: 500; }
        .gd-select-sm {
            padding: 7px 30px 7px 10px;
            background-position: right 9px center;
            background-size: 14px 14px;
            font-size: 11px;
            line-height: 18px;
        }
        /* Lebar tetap di layar lebar agar dropdown sejajar rapi; di ponsel selebar kolom. */
        @media (min-width: 640px) { .gd-select-fixed { width: 13rem; flex-shrink: 0; } }
        /* Toolbar filter ringkas (mis. statistik di Beranda): di ponsel tiga kolom sama lebar, di layar lebar berjajar. */
        .gd-filter-toolbar-wrap { width: 100%; }
        .gd-filter-toolbar { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); align-items: center; gap: 8px; width: 100%; }
        .gd-filter-toolbar .gd-select { width: 100%; }
        @media (min-width: 640px) {
            .gd-filter-toolbar-wrap { width: auto; }
            .gd-filter-toolbar { display: flex; flex-wrap: wrap; width: auto; }
            .gd-filter-toolbar .gd-select { width: 10rem; }
        }
        /* Tombol Peta/Tabel: di ponsel dua tombol sama lebar. */
        .gd-view-toggle > button { flex: 1 1 0; justify-content: center; }
        @media (min-width: 640px) { .gd-view-toggle > button { flex: none; } }
        /* Kotak cari dengan tombol (mis. halaman Artikel), setinggi dropdown di atas. */
        .gd-search { position: relative; display: flex; width: 100%; }
        @media (min-width: 768px) { .gd-search { width: 26rem; } }
        .gd-search-icon { position: absolute; left: 14px; top: 50%; z-index: 2; transform: translateY(-50%); color: #6b7268; pointer-events: none; }
        .gd-search input { flex: 1; min-width: 0; height: 42px; padding: 0 12px 0 40px; background: #fff; border: 1px solid var(--hairline); border-right: 0; border-radius: 2px 0 0 2px; color: var(--ink); font-size: 14px; }
        .gd-search input::placeholder { color: #8a9186; }
        .gd-search input::-webkit-search-cancel-button { -webkit-appearance: none; width: 14px; height: 14px; cursor: pointer; background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%236b7268' stroke-width='2.5' stroke-linecap='round'%3E%3Cpath d='M18 6 6 18M6 6l12 12'/%3E%3C/svg%3E") center / 14px no-repeat; }
        .gd-search input:focus { position: relative; z-index: 1; outline: none; border-color: var(--leaf); box-shadow: 0 0 0 1px var(--leaf); }
        .gd-search button { flex-shrink: 0; height: 42px; padding: 0 18px; background: var(--ink); color: #fff; border-radius: 0 2px 2px 0; font-family: 'Poppins', sans-serif; font-size: 11px; font-weight: 500; letter-spacing: .18em; text-transform: uppercase; cursor: pointer; transition: background-color .15s, color .15s; }
        .gd-search button:hover { background: var(--brand); color: var(--leaf); }
        .gd-search button:focus-visible { outline: 2px solid var(--leaf); outline-offset: 2px; }
    </style>

    {{-- Scripts --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>

{{--

<body class="antialiased">
    <div class="min-h-screen bg-gray-100">
        @include('front.components.navigation')

        <!-- Page Heading -->
        @isset($header)
        <header class="bg-white shadow">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                {{ $header }}
            </div>
        </header>
        @endisset

        <!-- Page Content -->
        <main class="">
            @yield('content')
            {{ $slot ?? '' }}
        </main>
    </div>
    @livewireScripts

</body> --}}

<body class="flex flex-col min-h-screen bg-white font-sans">
    {{-- Navbar --}}
    @include('front.components.navigation')


    <!-- Page Content -->
    <main class="flex-grow flex flex-col">
        @yield('content')
        {{ $slot ?? '' }}
    </main>

    {{-- Footer --}}
    @include('front.components.footer')
    @livewireScripts
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>
    <script src="https://unpkg.com/@raruto/leaflet-gesture-handling@1.6.3/dist/leaflet-gesture-handling.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/prunecluster@2.1.0/dist/PruneCluster.js"></script>
    {{-- Alpine sudah dibundel di dalam @livewireScripts (Livewire 3) — memuatnya lagi dari
         CDN membuat dua instance Alpine berjalan bersamaan dan merusak sinkronisasi DOM
         Livewire (wire:ignore, morph), yang bikin widget Turnstile kehilangan referensinya. --}}
    @stack('scripts')

</body>

</html>
