<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">


    <title>{{ $pageTitle ?? 'Auriga - Environmental Defender' }}</title>
    <meta name="description" content="{{ $pageDescription ?? 'Platform transparansi kasus hukum lingkungan hidup di Indonesia.' }}">
    <meta name="keywords" content="kasus lingkungan, hukum lingkungan, transparansi publik, environmental defender, Indonesia">

    <!-- Open Graph -->
    <meta property="og:title" content="{{ $ogTitle ?? 'Auriga - Environmental Defender' }}">
    <meta property="og:description" content="{{ $ogDescription ?? 'Platform transparansi kasus hukum lingkungan hidup di Indonesia.' }}">
    <meta property="og:image" content="{{ $ogImage ?? asset('img/image.png') }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:type" content="{{ $ogType ?? 'website' }}">
    <meta property="og:locale" content="{{ str_replace('_', '-', app()->getLocale()) }}">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $ogTitle ?? 'Auriga - Environmental Defender' }}">
    <meta name="twitter:description" content="{{ $ogDescription ?? 'Platform transparansi kasus hukum lingkungan hidup di Indonesia.' }}">
    <meta name="twitter:image" content="{{ $ogImage ?? asset('img/image.png') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster/dist/MarkerCluster.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster/dist/MarkerCluster.Default.css">
    <link rel="stylesheet"
        href="https://unpkg.com/@raruto/leaflet-gesture-handling@latest/dist/leaflet-gesture-handling.min.css"
        type="text/css">


    <style>
        body {
            font-family: 'Poppins', sans-serif;
            line-height: 24px;
        }
        [x-cloak] { display: none !important; }
    </style>


    <!-- Scripts -->
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
    <main class="flex-grow">
        @yield('content')
        {{ $slot ?? '' }}
    </main>

    {{-- Footer --}}
    @include('front.components.footer')
    @livewireScripts
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet.markercluster/dist/leaflet.markercluster.js"></script>
    <script src="https://unpkg.com/@raruto/leaflet-gesture-handling@latest/dist/leaflet-gesture-handling.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/prunecluster@2.1.0/dist/PruneCluster.js"></script>
    {{-- Alpine sudah dibundel di dalam @livewireScripts (Livewire 3) — memuatnya lagi dari
         CDN membuat dua instance Alpine berjalan bersamaan dan merusak sinkronisasi DOM
         Livewire (wire:ignore, morph), yang bikin widget Turnstile kehilangan referensinya. --}}
    @stack('scripts')

</body>

</html>