<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- @php
    use Illuminate\Support\Str;

    $appName = 'CMS Environmental Defender';
    $locale = app()->getLocale();
    $currentPath = request()->path(); // contoh: id/about
    $pathParts = explode('/', $currentPath);
    $urlSegment = end($pathParts);

    // Format URL segment ke Title
    $urlTitle = Str::of($urlSegment)
    ->replace('-', ' ')
    ->title(); // contoh: apa-itu-pembela-lingkungan => Apa Itu Pembela Lingkungan

    // Gunakan title/deskripsi default
    $pageTitle = $pageTitle ?? "$urlTitle | $appName";
    $pageDescription = $pageDescription ?? "Informasi tentang $urlTitle di $appName.";
    $pageImage = $pageImage ?? asset('images/new3.png');
    $pageType = $pageType ?? 'website';
    $currentUrl = url()->current();
    @endphp --}}

    <!-- 🌐 Basic Meta -->
    <title>Auriga</title>
    {{-- <meta name="description" content="{{ $pageDescription }}">
    <meta name="title" content="{{ $pageType }}">
    <meta itemprop="image" content="{{ $pageImage }}"> --}}

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    {{-- <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;600;700&display=swap" rel="stylesheet"> --}}
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    <style>
        body { font-family: 'Open Sans', sans-serif; }
    </style>


    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>

{{-- <body class="antialiased">
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
<body class="flex flex-col min-h-screen bg-white">
    {{-- Navbar --}}
    @include('front.components.navigation')


    <!-- Page Content -->
    <main class="flex-grow">
        @yield('content')
    </main>

    {{-- Footer --}}
    @include('front.components.footer')
    @livewireScripts
    <script src="/js/tinymce/tinymce.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
</body>

</html>