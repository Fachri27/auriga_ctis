{{-- INTERNAL NAVBAR --}}
<nav class="w-full bg-slate-800 text-white flex items-center px-6 py-3 shadow-md">
    {{-- Logo / Brand --}}
    <div class="flex items-center gap-3 mr-6">
        <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                </path>
            </svg>
        </div>
        <div>
            <div class="font-semibold text-sm">CTIS</div>
            <div class="text-xs text-slate-400">Internal System</div>
        </div>
    </div>

    {{-- Navigation Menu --}}
    <div class="flex-1 flex items-center space-x-2">
        {{-- DASHBOARD --}}
        <a href="{{ route('dashboard') }}"
            class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('dashboard') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-700 hover:text-white' }}">
            <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6">
                </path>
            </svg>
            Dashboard
        </a>

        {{-- CASES --}}
        <a href="{{ route('case.index') }}"
            class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('case.index') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-700 hover:text-white' }}">
            All Cases
        </a>
        <a href="{{ route('reports.index') }}"
            class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('reports.index') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-700 hover:text-white' }}">
            Laporan Masuk
        </a>

        {{-- DATA --}}
        <a href="{{ route('categoris.index') }}"
            class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('categoris.*') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-700 hover:text-white' }}">
            Categories
        </a>
        <a href="{{ route('statuse.index') }}"
            class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('statuse.*') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-700 hover:text-white' }}">
            Statuses
        </a>
        <a href="{{ route('process.index') }}"
            class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('process.*') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-700 hover:text-white' }}">
            Processes
        </a>
        <a href="{{ route('task.index') }}"
            class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('task.*') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-700 hover:text-white' }}">
            Tasks
        </a>
        <a href="{{ route('artikel.index') }}"
            class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('artikel.*') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-700 hover:text-white' }}">
            Artikels
        </a>
    </div>

    {{-- SYSTEM --}}
    <div class="flex items-center space-x-2 ml-auto">
        <a href="{{ route('user.index') }}"
            class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors text-slate-300 hover:bg-slate-700 hover:text-white">
            Users
        </a>
        <a href="#"
            class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors text-slate-300 hover:bg-slate-700 hover:text-white">
            Roles & Permissions
        </a>
        <a href="#"
            class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors text-slate-300 hover:bg-slate-700 hover:text-white">
            Audit Logs
        </a>
    </div>
</nav>
