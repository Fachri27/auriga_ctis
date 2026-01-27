<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ isset($pageTitle) ? $pageTitle . ' - ' : '' }}CTIS Internal System - {{ config('app.name', 'CTIS') }}
    </title>

    @stack('styles')

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        }
    </style>
</head>

<body class="bg-gray-50 antialiased">
    <div class="flex h-screen overflow-hidden">
        {{-- SIDEBAR --}}
        @include('layouts.partials.internal-sidebar')

        {{-- MAIN CONTENT AREA --}}
        <div class="flex flex-col flex-1 overflow-hidden">
            {{-- TOPBAR --}}
            @include('layouts.partials.internal-topbar')

            {{-- PAGE CONTENT --}}
            <main class="flex-1 overflow-y-auto bg-gray-50">
                <div class="py-6 px-6">
                    {{-- Breadcrumbs --}}
                    @if(isset($breadcrumbs))
                    <nav class="mb-4" aria-label="Breadcrumb">
                        <ol class="flex items-center space-x-2 text-sm text-gray-600">
                            @foreach($breadcrumbs as $breadcrumb)
                            <li>
                                @if(!$loop->last)
                                <a href="{{ $breadcrumb['url'] ?? '#' }}" class="hover:text-gray-900">
                                    {{ $breadcrumb['label'] }}
                                </a>
                                <span class="mx-2">/</span>
                                @else
                                <span class="text-gray-900 font-medium">{{ $breadcrumb['label'] }}</span>
                                @endif
                            </li>
                            @endforeach
                        </ol>
                    </nav>
                    @endif

                    {{-- Page Header --}}
                    @if(isset($pageTitle) || isset($pageSubtitle))
                    <div class="mb-6">
                        <div class="flex items-center justify-between">
                            <div>
                                @if(isset($pageTitle))
                                <h1 class="text-2xl font-semibold text-gray-900">{{ $pageTitle }}</h1>
                                @endif
                                @if(isset($pageSubtitle))
                                <p class="mt-1 text-sm text-gray-600">{{ $pageSubtitle }}</p>
                                @endif
                            </div>
                            {{-- Action Buttons --}}
                            @if(isset($pageActions))
                            <div class="flex items-center gap-3">
                                {{ $pageActions }}
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif

                    {{-- Flash Messages --}}
                    @if(session('success'))
                    <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                            </div>
                        </div>
                    </div>
                    @endif

                    @if(session('error'))
                    <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- Page Content --}}
                    {{ $slot }}
                </div>
            </main>
        </div>
    </div>

    @livewireScripts

    <script>
        // Debug helper: log Livewire presence and show an alert when 'notify' events are dispatched.
        console.log('Livewire present:', typeof Livewire !== 'undefined');

        window.addEventListener('notify', function(e) {
            console.log('Livewire notify event received:', e && e.detail ? e.detail : e);
            try {
                if (e && e.detail && e.detail.message) {
                    // Use alert for now so it's visible during debugging
                    alert(e.detail.message);
                }
            } catch (ex) {
                console.error('Error handling notify event', ex);
            }
        });

        // Click logger: detect clicks on elements that have wire:click and log details
        document.addEventListener('click', function(ev) {
            try {
                var el = ev.target;
                // walk up to find element with wire:click attribute
                while (el && el !== document.body) {
                    if (el.hasAttribute && el.hasAttribute('wire:click')) {
                        console.log('CLICK on element with wire:click', {
                            tag: el.tagName,
                            innerText: el.innerText,
                            wireClick: el.getAttribute('wire:click'),
                            disabled: el.disabled || el.classList.contains('disabled')
                        });
                        break;
                    }
                    el = el.parentNode;
                }
            } catch (ex) {
                console.error('Error in click logger', ex);
            }
        });
    </script>

    @stack('scripts')
    <script src="/js/tinymce/tinymce.min.js"></script>
</body>

</html>