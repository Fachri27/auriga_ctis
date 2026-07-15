@php
    $navLinks = [
        ['label' => __('messages.about'), 'href' => route('about-user', ['locale' => app()->getLocale()]), 'active' => request()->routeIs('about-user')],
        ['label' => 'Dashboard', 'href' => route('public.dashboard', ['locale' => app()->getLocale(), 'filter' => 'active']), 'active' => request()->routeIs('public.dashboard')],
        ['label' => __('messages.artikel'), 'href' => route('public.artikel.list', ['locale' => app()->getLocale()]), 'active' => request()->routeIs('public.artikel.*')],
        ['label' => __('messages.verified_cases'), 'href' => route('front.verified-cases', ['locale' => app()->getLocale()]), 'active' => request()->routeIs('front.verified-cases')],
    ];
    $localeUrl = fn($loc) => route(Route::currentRouteName(), array_merge(Route::current()->parameters(), ['locale' => $loc]));
@endphp

<nav x-data="{ open: false }"
    class="fixed top-0 left-0 z-[9999] w-full bg-[#0B1E07] border-b border-white/10 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between gap-4">

        {{-- LEFT: hamburger (mobile) + logo --}}
        <div class="flex items-center gap-3">
            <button @click="open = !open" class="lg:hidden p-1 -ml-1" aria-label="Menu" :aria-expanded="open">
                <svg x-show="!open" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
                <svg x-show="open" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M18 6L6 18"/>
                </svg>
            </button>
            <a href="{{ route('dashboard-user', ['locale' => app()->getLocale()]) }}" class="flex items-center gap-3">
                <img src="/img/image.png" class="h-8 sm:h-9 w-auto object-contain" alt="Auriga CTIS">
                <span class="hidden md:flex items-center gap-3">
                    <span class="h-6 border-r border-white/20"></span>
                    <span class="font-data text-[10px] tracking-[0.24em] uppercase text-white/60 leading-tight">Case Tracking<br>Information System</span>
                </span>
            </a>
        </div>

        {{-- CENTER: links (desktop) --}}
        <div class="hidden lg:flex items-center gap-8 font-data text-[11px] uppercase tracking-[0.2em]">
            @foreach ($navLinks as $link)
                <a href="{{ $link['href'] }}"
                    class="flex items-center gap-2 py-1 transition-colors {{ $link['active'] ? 'text-[#9BDB4D]' : 'text-white/75 hover:text-white' }}">
                    @if ($link['active'])<span class="w-1.5 h-1.5 rounded-full bg-[#9BDB4D]"></span>@endif
                    {{ $link['label'] }}
                </a>
            @endforeach
        </div>

        {{-- RIGHT: locale toggle --}}
        <div class="flex items-center gap-2 font-data text-[11px] tracking-[0.2em] uppercase">
            <a href="{{ $localeUrl('id') }}"
                class="{{ app()->getLocale() === 'id' ? 'text-[#9BDB4D] font-semibold' : 'text-white/50 hover:text-white' }}">ID</a>
            <span class="text-white/25">/</span>
            <a href="{{ $localeUrl('en') }}"
                class="{{ app()->getLocale() === 'en' ? 'text-[#9BDB4D] font-semibold' : 'text-white/50 hover:text-white' }}">EN</a>
        </div>
    </div>

    {{-- MOBILE MENU --}}
    <div x-show="open" x-cloak @click.outside="open = false"
        class="lg:hidden bg-[#0B1E07] border-t border-white/10 px-4 sm:px-6 py-3 font-data text-[11px] uppercase tracking-[0.2em]">
        @foreach ($navLinks as $link)
            <a href="{{ $link['href'] }}"
                class="flex items-center gap-2 py-3 border-b border-white/5 last:border-0 {{ $link['active'] ? 'text-[#9BDB4D]' : 'text-white/75' }}">
                @if ($link['active'])<span class="w-1.5 h-1.5 rounded-full bg-[#9BDB4D]"></span>@endif
                {{ $link['label'] }}
            </a>
        @endforeach
    </div>
</nav>
