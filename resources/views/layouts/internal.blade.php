<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>greendefender — Console</title>

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    @stack('styles')

    <style>
        :root {
            --ink: #0B1E07;
            --ink-2: #143009;
            --brand: #264c16;
            --leaf: #9BDB4D;
            --leaf-deep: #2F6C14;
            --paper: #F5F7F1;
            --paper-2: #ECEFE6;
            --hairline: #E2E6DA;
            --hairline-2: #D2D8C6;
            --surface: #FFFFFF;
            --muted: #6b7268;
            --muted-2: #8a9082;

            /* semantic */
            --ok: #2F6C14;
            --ok-soft: #E6F2D4;
            --warn: #B5761A;
            --warn-soft: #F6E8C8;
            --danger: #B23A3A;
            --danger-soft: #F4D9D9;
            --info: #2F6C14;
            --info-soft: #E6F2D4;
        }

        body {
            font-family: 'Poppins', sans-serif;
            line-height: 24px;
            color: var(--ink);
            background-color: var(--paper);
            background-image:
                radial-gradient(1200px 600px at 100% -10%, rgba(155,219,77,0.05), transparent 60%),
                repeating-linear-gradient(0deg, transparent, transparent 39px, rgba(11,30,7,0.018) 40px),
                repeating-linear-gradient(90deg, transparent, transparent 39px, rgba(11,30,7,0.018) 40px);
            background-size: auto, 40px 40px, 40px 40px;
            background-attachment: fixed;
        }

        .font-mono-c { font-family: 'JetBrains Mono', ui-monospace, monospace; }
        [x-cloak] { display: none !important; }

        /* ===== Brand console-grid (topbar / hero strips) ===== */
        .console-grid {
            background-color: var(--ink);
            background-image:
                repeating-linear-gradient(0deg, transparent, transparent 55px, rgba(255,255,255,0.035) 56px),
                repeating-linear-gradient(90deg, transparent, transparent 55px, rgba(255,255,255,0.035) 56px);
            background-size: 56px 56px;
        }

        /* ===== Eyebrow (signature leaf label) ===== */
        .cms-eyebrow {
            font-family: 'JetBrains Mono', monospace;
            text-transform: uppercase;
            letter-spacing: 0.22em;
            font-size: 10px;
            font-weight: 600;
            color: var(--leaf-deep);
        }
        .cms-eyebrow.on-ink { color: var(--leaf); }

        /* ===== Section / panel ===== */
        .cms-panel {
            background: var(--surface);
            border: 1px solid var(--hairline);
            border-radius: 14px;
            overflow: hidden;
        }
        .cms-panel-head {
            padding: 18px 22px;
            border-bottom: 1px solid var(--hairline);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }
        .cms-panel-title { font-size: 15px; font-weight: 600; color: var(--ink); letter-spacing: -0.01em; }
        .cms-panel-sub  { font-size: 12px; color: var(--muted); margin-top: 2px; }
        .cms-panel-body { padding: 22px; }

        /* ===== Stat tile ===== */
        .cms-tile {
            position: relative;
            background: var(--surface);
            border: 1px solid var(--hairline);
            border-radius: 14px;
            padding: 20px 22px 22px;
            overflow: hidden;
            transition: border-color .2s ease, transform .2s ease;
        }
        .cms-tile:hover { border-color: var(--hairline-2); transform: translateY(-1px); }
        .cms-tile::before {
            content: "";
            position: absolute; left: 0; top: 0; bottom: 0; width: 3px;
            background: var(--leaf);
            opacity: .9;
        }
        .cms-tile-meta { font-family: 'JetBrains Mono', monospace; text-transform: uppercase; letter-spacing: .18em; font-size: 10px; color: var(--muted-2); }
        .cms-tile-num  { font-size: 34px; font-weight: 700; color: var(--ink); letter-spacing: -0.02em; line-height: 1.05; margin-top: 10px; }
        .cms-tile-foot { font-size: 11px; color: var(--muted); margin-top: 6px; }
        .cms-tile-glyph {
            position: absolute; top: 18px; right: 18px;
            width: 38px; height: 38px; border-radius: 10px;
            background: var(--paper-2);
            border: 1px solid var(--hairline);
            display: grid; place-items: center; color: var(--leaf-deep);
        }
        .cms-tile.is-warn::before { background: var(--warn); }
        .cms-tile.is-ok::before   { background: var(--ok); }
        .cms-tile.is-muted::before{ background: var(--hairline-2); }
        .cms-tile.is-warn .cms-tile-glyph { color: var(--warn); }
        .cms-tile.is-ok .cms-tile-glyph   { color: var(--ok); }
        .cms-tile.is-muted .cms-tile-glyph{ color: var(--muted-2); }

        /* ===== Buttons ===== */
        .cms-btn {
            display: inline-flex; align-items: center; gap: 8px;
            font-size: 11px; font-weight: 600; letter-spacing: 0.12em; text-transform: uppercase;
            padding: 9px 14px; border-radius: 9px; transition: all .18s ease; white-space: nowrap;
        }
        .cms-btn-primary { background: var(--ink); color: #fff; border: 1px solid var(--ink); }
        .cms-btn-primary:hover { background: var(--leaf-deep); border-color: var(--leaf-deep); }
        .cms-btn-leaf { background: var(--leaf-deep); color: #fff; border: 1px solid var(--leaf-deep); }
        .cms-btn-leaf:hover { background: var(--ink); border-color: var(--ink); }
        .cms-btn-ghost { background: transparent; color: var(--ink); border: 1px solid var(--hairline-2); }
        .cms-btn-ghost:hover { border-color: var(--ink); background: var(--paper-2); }
        .cms-btn-danger { background: transparent; color: var(--danger); border: 1px solid var(--danger); }
        .cms-btn-danger:hover { background: var(--danger); color: #fff; }

        /* ===== Pills / status ===== */
        .cms-pill {
            display: inline-flex; align-items: center; gap: 6px;
            font-size: 11px; font-weight: 600; padding: 3px 9px; border-radius: 999px;
            border: 1px solid transparent; line-height: 1.4;
        }
        .cms-pill .dot { width: 6px; height: 6px; border-radius: 999px; background: currentColor; flex-shrink: 0; }
        .cms-pill-default { color: var(--muted); background: var(--paper-2); border-color: var(--hairline); }
        .cms-pill-ok   { color: var(--ok);   background: var(--ok-soft);   border-color: #CFE6A6; }
        .cms-pill-warn { color: var(--warn); background: var(--warn-soft); border-color: #EAD6A0; }
        .cms-pill-danger{ color: var(--danger); background: var(--danger-soft); border-color: #E9B8B8; }
        .cms-pill-info { color: var(--info); background: var(--info-soft); border-color: #CFE6A6; }
        .cms-pill-on-ink { color: var(--leaf); background: rgba(155,219,77,0.12); border-color: rgba(155,219,77,0.35); }

        /* ===== Tables ===== */
        .cms-table { width: 100%; border-collapse: collapse; font-size: 13px; }
        .cms-table thead th {
            font-family: 'JetBrains Mono', monospace;
            text-transform: uppercase; letter-spacing: 0.14em; font-size: 10px; font-weight: 600;
            color: var(--muted-2); text-align: left; padding: 13px 22px;
            border-bottom: 1px solid var(--hairline); background: var(--paper);
        }
        .cms-table tbody td { padding: 14px 22px; border-bottom: 1px solid var(--hairline); color: var(--ink); }
        .cms-table tbody tr:last-child td { border-bottom: 0; }
        .cms-table tbody tr { transition: background .15s ease; }
        .cms-table tbody tr:hover { background: var(--paper); }
        .cms-table .num { font-family: 'JetBrains Mono', monospace; font-weight: 600; }
        .cms-table .link { color: var(--leaf-deep); font-weight: 600; }
        .cms-table .link:hover { color: var(--ink); }

        /* ===== Inputs (opt-in lift; existing CMS forms keep their Tailwind styling) ===== */
        .cms-input {
            font-family: 'Poppins', sans-serif; font-size: 13px;
            color: var(--ink); background: var(--surface);
            border: 1px solid var(--hairline-2); border-radius: 9px;
            padding: 9px 12px; transition: border-color .15s ease, box-shadow .15s ease; width: 100%;
        }
        .cms-input:focus {
            outline: none; border-color: var(--leaf-deep);
            box-shadow: 0 0 0 3px rgba(155,219,77,0.18);
        }
        /* keep Leaflet + TinyMCE + TomSelect controls untouched */
        .leaflet-container, .leaflet-control input, .ts-control, .ts-wrapper, .tox-tinymce, .tox-tinymce * { box-shadow: none !important; }
        /* TomSelect dropdown dirender ke <body> (dropdownParent) — pastikan tampil di atas topbar/modals */
        .ts-dropdown { z-index: 9999 !important; }

        /* ===== Reveal animation ===== */
        @keyframes cms-rise { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: none; } }
        .cms-rise { animation: cms-rise .5s cubic-bezier(.2,.7,.2,1) both; }

        /* Leaflet popup restyle to match */
        .leaflet-popup-content-wrapper { border-radius: 10px; border: 1px solid var(--hairline); box-shadow: 0 8px 30px rgba(11,30,7,0.12); }
        .leaflet-popup-content { margin: 12px 14px; font-family: 'Poppins', sans-serif; }
    </style>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>

<body
    class="flex flex-col min-h-screen font-sans"
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
                :class="t.type === 'error' ? 'cms-pill-danger' : 'cms-pill-ok'"
                class="px-5 py-3 rounded-xl text-sm font-medium shadow-xl flex items-center gap-3 min-w-[320px] max-w-md pointer-events-auto"
                :style="t.type === 'error' ? 'background:var(--ink);color:#fff;border-color:var(--danger)' : 'background:var(--ink);color:#fff;border-color:var(--leaf-deep)'"
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