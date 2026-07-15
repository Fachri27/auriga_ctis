{{-- INTERNAL COMMAND BAR --}}
@php
    $env = app()->environment();
    $envLabel = strtoupper($env);
    $navItems = [
        ['route' => 'dashboard',        'match' => ['dashboard'],       'label' => 'Dashboard'],
        ['route' => 'case.index',       'match' => ['case.index'],      'label' => 'Cases'],
        ['route' => 'categoris.index', 'match' => ['categoris.*'],     'label' => 'Categories'],
        ['route' => 'statuse.index',   'match' => ['statuse.*'],       'label' => 'Statuses'],
        ['route' => 'artikel.index',    'match' => ['artikel.*'],      'label' => 'Artikels'],
        ['route' => 'subscription.index','match' => ['subscription.*'], 'label' => 'Subscriptions'],
        ['route' => 'about.edit',       'match' => ['about.edit'],     'label' => 'About'],
        ['route' => 'charts.dashboard', 'match' => ['charts.dashboard'],'label' => 'Charts'],
    ];
    $isActive = function (array $matches) {
        foreach ($matches as $m) {
            if (str_contains($m, '.*') ? request()->routeIs($m) : request()->routeIs($m)) return true;
        }
        return false;
    };
@endphp

<header class="console-grid sticky top-0 z-[1000] text-white border-b border-black/30">
    <div class="flex items-stretch h-16">
        {{-- Brand / system mark --}}
        <div class="flex items-center gap-3 px-5 border-r border-white/10 shrink-0">
            <div class="w-9 h-9 rounded-lg grid place-items-center" style="background:linear-gradient(135deg,var(--leaf),var(--leaf-deep));">
                <svg class="w-5 h-5" fill="none" stroke="#0B1E07" stroke-width="2.2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3l8 4.5v6c0 4.5-3.2 7.2-8 8.5-4.8-1.3-8-4-8-8.5v-6L12 3z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4"/>
                </svg>
            </div>
            <div class="leading-tight">
                <div class="text-[13px] font-semibold tracking-wide">green<span style="color:var(--leaf)">defender</span></div>
                <div class="cms-eyebrow on-ink" style="letter-spacing:.2em;">Case Console</div>
            </div>
        </div>

        {{-- Primary nav --}}
        <nav class="flex items-center gap-1 px-3">
            {{-- Link cluster: boleh scroll horizontal, tapi dropdown HARUS di luar wrapper
                 ini. Sebab CSS melarang overflow-x:auto + overflow-y:visible — bila
                 axis-x di-scroll, axis-y dipaksa auto, sehingga dropdown yang turun ke
                 bawah akan terpotong (ketiban). --}}
            <div class="flex items-center gap-1 overflow-x-auto" style="scrollbar-width:none;">
                @foreach ($navItems as $item)
                    @php $active = $isActive($item['match']); @endphp
                    <a href="{{ route($item['route']) }}"
                        class="relative px-3.5 h-9 flex items-center text-[13px] font-medium rounded-lg transition-colors whitespace-nowrap {{ $active ? 'text-white' : 'text-white/60 hover:text-white hover:bg-white/5' }}"
                        style="{{ $active ? 'background:rgba(155,219,77,0.14);' : '' }}">
                        {{ $item['label'] }}
                        @if($active)
                            <span class="absolute left-3 right-3 -bottom-px h-[2px] rounded-full" style="background:var(--leaf);"></span>
                        @endif
                    </a>
                @endforeach
            </div>

            {{-- Users dropdown — ditempatkan di luar wrapper overflow-x agar tidak ter-clip --}}
            <div x-data="{ open: false }" class="relative shrink-0">
                <button @click="open = !open"
                    class="px-3.5 h-9 flex items-center gap-1.5 text-[13px] font-medium rounded-lg transition-colors text-white/60 hover:text-white hover:bg-white/5 {{ request()->routeIs(['user.*','permission']) ? 'text-white' : '' }}"
                    style="{{ request()->routeIs(['user.*','permission']) ? 'background:rgba(155,219,77,0.14);' : '' }}">
                    Users
                    <svg class="w-3.5 h-3.5 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="open" @click.away="open = false" x-transition
                    class="absolute left-0 mt-2 w-52 rounded-xl border border-white/10 shadow-2xl py-1.5 z-[1100] console-grid"
                    style="display:none;">
                    <a href="{{ route('user.index') }}" class="block px-4 py-2 text-sm text-white/80 hover:text-white hover:bg-white/10">All User</a>
                    <a href="{{ route('permission', ['locale' => app()->getLocale()]) }}" class="block px-4 py-2 text-sm text-white/80 hover:text-white hover:bg-white/10">Kelola Permission</a>
                </div>
            </div>
        </nav>

        {{-- Right cluster: status + env + notifications + user --}}
        <div class="ml-auto flex items-center gap-2 px-4">
            {{-- live status chip --}}
            <div class="hidden lg:flex items-center gap-2 px-3 h-9 rounded-lg border border-white/10" style="background:rgba(255,255,255,0.04);">
                <span class="relative flex w-1.5 h-1.5">
                    <span class="absolute inline-flex w-full h-full rounded-full opacity-70 animate-ping" style="background:var(--leaf);"></span>
                    <span class="relative inline-flex w-1.5 h-1.5 rounded-full" style="background:var(--leaf);"></span>
                </span>
                <span class="cms-eyebrow on-ink" style="letter-spacing:.18em;">Operational</span>
            </div>

            {{-- env badge --}}
            <span class="hidden sm:inline-flex items-center px-2 h-6 rounded text-[10px] font-mono-c font-semibold tracking-wider"
                style="background:rgba(255,255,255,0.06); color: {{ in_array($env,['production']) ? 'var(--leaf)' : '#F6E8C8' }}; border:1px solid rgba(255,255,255,0.12);">
                {{ $envLabel }}
            </span>

            {{-- notifications --}}
            <button class="relative p-2 h-9 w-9 grid place-items-center rounded-lg text-white/70 hover:text-white hover:bg-white/5 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                <span class="absolute top-2 right-2 block h-2 w-2 rounded-full" style="background:#ff7a7a;box-shadow:0 0 0 2px var(--ink);"></span>
            </button>

            {{-- user --}}
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open" class="flex items-center gap-2.5 h-9 pl-1.5 pr-2.5 rounded-lg hover:bg-white/5 transition-colors">
                    <div class="w-7 h-7 rounded-lg grid place-items-center text-[12px] font-semibold"
                        style="background:var(--leaf);color:var(--ink);">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <div class="hidden md:block text-left leading-tight">
                        <div class="text-[12px] font-medium text-white">{{ auth()->user()->name }}</div>
                        <div class="cms-eyebrow on-ink" style="letter-spacing:.14em;font-size:9px;">{{ auth()->user()->role ?? 'staff' }}</div>
                    </div>
                    <svg class="w-3.5 h-3.5 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                </button>

                <div x-show="open" @click.outside="open = false" x-transition
                    class="absolute right-0 mt-2 w-60 rounded-xl shadow-2xl border border-white/10 py-2 z-[1100] console-grid"
                    style="display:none;">
                    <div class="px-4 py-3 border-b border-white/10">
                        <div class="text-sm font-medium text-white">{{ auth()->user()->name }}</div>
                        <div class="text-xs text-white/50 mt-0.5 font-mono-c">{{ auth()->user()->email }}</div>
                    </div>
                    <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-white/80 hover:text-white hover:bg-white/10">Profile</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full flex items-center px-4 py-2 text-sm hover:bg-white/10" style="color:#ff9b9b;">
                            <svg class="w-4 h-4 mr-2.5 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>