{{-- INTERNAL NAVBAR --}}
<header class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-6 sticky top-0 z-10">
    {{-- Left: Logo / Title --}}
    <div class="flex items-center gap-4">
        <h1 class="text-lg font-semibold text-gray-900">CTIS Internal System</h1>

        {{-- Environment Badge --}}
        @php
        $env = app()->environment();
        $envColors = [
        'local' => 'bg-gray-100 text-gray-700',
        'development' => 'bg-yellow-100 text-yellow-700',
        'staging' => 'bg-blue-100 text-blue-700',
        'production' => 'bg-green-100 text-green-700'
        ];
        $envColor = $envColors[$env] ?? $envColors['local'];
        @endphp
        <span class="px-2 py-1 text-xs font-medium rounded {{ $envColor }}">
            {{ strtoupper($env) }}
        </span>
    </div>

    {{-- Center: Navigation Menu --}}
    <nav class="flex-1 flex items-center justify-center space-x-3">
        <a href="{{ route('dashboard') }}"
            class="px-3 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('dashboard') ? 'bg-blue-600 text-white' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
            Dashboard
        </a>
        <a href="{{ route('case.index') }}"
            class="px-3 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('case.index') ? 'bg-blue-600 text-white' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
            All Cases
        </a>
        <a href="{{ route('reports.index') }}"
            class="px-3 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('reports.index') ? 'bg-blue-600 text-white' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
            Laporan Masuk
        </a>
        <a href="{{ route('categoris.index') }}"
            class="px-3 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('categoris.*') ? 'bg-blue-600 text-white' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
            Categories
        </a>
        <a href="{{ route('statuse.index') }}"
            class="px-3 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('statuse.*') ? 'bg-blue-600 text-white' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
            Statuses
        </a>
        {{-- <a href="{{ route('process.index') }}"
            class="px-3 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('process.*') ? 'bg-blue-600 text-white' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
            Processes
        </a>
        <a href="{{ route('task.index') }}"
            class="px-3 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('task.*') ? 'bg-blue-600 text-white' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
            Tasks
        </a> --}}
        <a href="{{ route('artikel.index') }}"
            class="px-3 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('artikel.*') ? 'bg-blue-600 text-white' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
            Artikels
        </a>

        <div x-data="{ open: false }" class="relative px-3 py-2 text-sm font-medium rounded-lg transition-colors">
            <button @click="open = !open" class="flex items-center hover:text-green-800 cursor-pointer text-nowrap">
                Users
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 ml-1" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>
            <div x-show="open" @click.away="open = false" x-transition
                class="absolute left-0 mt-2 w-40 bg-white border border-gray-200 rounded-lg shadow-lg z-50"
                style="display: none !important">
                <a href="{{ route('user.index') }}"
                    class="block px-4 py-2 text-sm hover:bg-gray-100">All User</a>
                <a href="{{ route('permission', ['locale' => app()->getLocale()]) }}"
                    class="block px-4 py-2 text-sm hover:bg-gray-100">Kelola Permission</a>
            </div>
        </div>
    </nav>

    {{-- Right: Notifications & User Info --}}
    <div class="flex items-center gap-4">
        {{-- Notifications --}}
        <button class="relative p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9">
                </path>
            </svg>
            <span class="absolute top-1 right-1 block h-2 w-2 rounded-full bg-red-500 ring-2 ring-white"></span>
        </button>

        {{-- User Dropdown --}}
        <div x-data="{ open: false }" class="relative">
            <button @click="open = !open"
                class="flex items-center gap-3 p-2 hover:bg-gray-100 rounded-lg transition-colors">
                <div
                    class="w-8 h-8 bg-slate-700 rounded-full flex items-center justify-center text-white text-sm font-medium">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <div class="hidden md:block text-left">
                    <div class="text-sm font-medium text-gray-900">{{ auth()->user()->name }}</div>
                </div>
                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>

            {{-- Dropdown Menu --}}
            <div x-show="open" @click.outside="open = false" x-transition:enter="transition ease-out duration-100"
                x-transition:enter-start="transform opacity-0 scale-95"
                x-transition:enter-end="transform opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-75"
                x-transition:leave-start="transform opacity-100 scale-100"
                x-transition:leave-end="transform opacity-0 scale-95"
                class="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-lg border border-gray-200 py-2 z-50"
                style="display: none;">
                <div class="px-4 py-3 border-b border-gray-200">
                    <div class="text-sm font-medium text-gray-900">{{ auth()->user()->name }}</div>
                    <div class="text-xs text-gray-500 mt-1">{{ auth()->user()->email }}</div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                        class="w-full flex items-center px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
                        <svg class="w-5 h-5 mr-3 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                            </path>
                        </svg>
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>