<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Auriga</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    @stack('styles')

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            line-height: 24px;
        }
    </style>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>

<body
    class="flex flex-col min-h-screen bg-white font-sans"
    x-data="{ toasts: [] }"
    x-init="
        Livewire.on('notify', (event) => {
            toasts.push({ message: event.message, type: event.type ?? 'success', show: true });
            setTimeout(() => { toasts.shift(); }, 5000);
        });
        Livewire.on('notify-error', (event) => {
            toasts.push({ message: event.message, type: 'error', show: true });
            setTimeout(() => { toasts.shift(); }, 8000);
        });
    "
>
    {{-- Toast Container --}}
    <div class="fixed top-4 right-4 z-[99999] space-y-2" style="pointer-events:none">
        <template x-for="(t, i) in toasts" :key="i">
            <div
                x-show="t.show"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="translate-x-80 opacity-0"
                x-transition:enter-end="translate-x-0 opacity-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="translate-x-0 opacity-100"
                x-transition:leave-end="translate-x-80 opacity-0"
                :class="t.type === 'error' ? 'bg-red-600' : 'bg-green-600'"
                class="px-5 py-3 rounded-lg text-white shadow-xl flex items-center gap-3 min-w-[320px] max-w-md pointer-events-auto"
            >
                <template x-if="t.type === 'error'">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </template>
                <template x-if="t.type !== 'error'">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </template>
                <span x-text="t.message" class="text-sm font-medium"></span>
                <button @click="t.show = false; setTimeout(() => toasts.splice(toasts.indexOf(t), 1), 300)" class="ml-auto text-white/60 hover:text-white flex-shrink-0">&times;</button>
            </div>
        </template>
    </div>

    {{-- TOPBAR --}}
    @include('layouts.partials.internal-topbar')

    {{-- PAGE CONTENT --}}
    <main class="flex-grow">
        @yield('content')
        {{ $slot ?? '' }}
    </main>

    {{-- Leaflet (required by case-form, case-modal, report-form map pickers) --}}
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    @livewireScripts
    <script src="/js/tinymce/tinymce.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/tom-select/dist/js/tom-select.complete.min.js"></script>

    @stack('scripts')
</body>

</html>
