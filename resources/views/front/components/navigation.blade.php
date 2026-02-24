<nav x-data="{ scrolled: false, open: false, navOpen: false }" x-init="
        window.addEventListener('scroll', () => {
            scrolled = window.scrollY > 10;
        });
    " :class="scrolled ? 'border-transparent' : 'border-white'"
    class="border-b bg-[#032A36] fixed top-0 left-0 z-[9999] w-full transition-colors duration-300 poppins-regular">
    <div class="max-w-7xl mx-auto px-4 h-25 flex items-center justify-between">

        <!-- LEFT: Hamburger (mobile) + Logo -->
        <div class="flex items-center space-x-3 cursor-pointer">

            <!-- HAMBUGER (LEFT SIDE) -->
            <button @click="open = !open" class="lg:hidden text-white text-2xl">
                ☰
            </button>

            <!-- LOGO -->
            <div class="flex items-center space-x-2">
                <a href="{{ route('dashboard-user') }}">
                    <img src="/img/image.png" class="h-20" alt="Logo">
                </a>
            </div>
        </div>

        <!-- MIDDLE MENU (DESKTOP) -->
        <div
            class="hidden lg:flex items-center gap-8 text-white text-xs uppercase tracking-wide ml-55 leading-1.5 cursor-pointer">
            <a href="{{ route('about-user', ['locale' => app()->getLocale()]) }}" class="hover:text-gray-300">About</a>

            <!-- Public dashboard filters -->
            <a href="{{ route('public.dashboard', ['locale' => app()->getLocale(), 'filter' => 'active']) }}"
                class="hover:text-gray-300 {{ request('filter') === 'active' ? 'font-semibold' : '' }}">Dashboard</a>

            <a href="{{ route('report.form', ['locale' => app()->getLocale()]) }}" class="hover:text-gray-300">Report a
                Case</a>
            <a href="{{ route('public.artikel.list', ['locale' => app()->getLocale()]) }}" class="hover:text-gray-300">Artikel</a>
            <a href="{{ route('front.verified-cases', ['locale' => app()->getLocale()]) }}" class="hover:text-gray-300">Verified Case</a>
            {{-- <a href="#" class="hover:text-gray-300">Documentation</a> --}}
        </div>

        <!-- RIGHT SIDE -->
        <div class="flex items-center space-x-6 text-white text-sm cursor-pointer">
            <!-- Only show this on mobile -->
            <div class="lg:hidden md:flex justify-end items-center max-w-7xl mx-auto px-5 py-2 text-sm">
                <div class="flex space-x-1 text-gray-400">
                    <a href="{{ route(Route::currentRouteName(), array_merge(Route::current()->parameters(), ['locale' => 'en'])) }}"
                        class="hover:text-green-900 {{ app()->getLocale() === 'en' ? 'font-bold text-white' : '' }}">EN</a>
                    <span>|</span>
                    <a href="{{ route(Route::currentRouteName(), array_merge(Route::current()->parameters(), ['locale' => 'id'])) }}"
                        class="hover:text-green-900 {{ app()->getLocale() === 'id' ? 'font-bold text-white' : '' }}">ID</a>
                </div>
            </div>

            <!-- Desktop version -->
            <div class="hidden lg:flex items-center space-x-6 cursor-pointer leading-1.5">
                <div class="h-18 border-r border-white/30"></div>

                <div x-data="{ open:false }" class="relative">
                    <!-- Button -->
                    <button @click="open = !open"
                        class="flex items-center gap-2 text-sm cursor-pointer text-white hover:text-gray-300 transition">

                        <!-- Current Language -->
                        <span class="uppercase font-semibold tracking-wide">
                            {{ app()->getLocale() === 'id' ? 'Indonesia' : 'English' }}
                        </span>

                        <!-- Arrow -->
                        <svg class="w-4 h-4 transition-transform duration-300" :class="open ? 'rotate-180' : ''"
                            fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06L10 14.59 5.23 8.27z" />
                        </svg>
                    </button>

                    <!-- Dropdown -->
                    <div x-show="open" @click.outside="open = false"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                        class="absolute left-0 mt-2 w-44 bg-white rounded-lg shadow-lg border border-gray-100 z-50 py-2"
                        style="display: none !important">
                        <ul class="text-sm">

                            <!-- Indonesia -->
                            <li>
                                <a href="{{ route(Route::currentRouteName(), array_merge(Route::current()->parameters(), ['locale' => 'id'])) }}"
                                    class="flex items-center gap-2 px-4 py-2 hover:bg-gray-50 text-gray-700 font-medium transition">
                                    Indonesia
                                </a>
                            </li>

                            <!-- English -->
                            <li>
                                <a href="{{ route(Route::currentRouteName(), array_merge(Route::current()->parameters(), ['locale' => 'en'])) }}"
                                    class="flex items-center gap-2 px-4 py-2 hover:bg-gray-50 text-gray-700 transition">
                                    English
                                </a>
                            </li>

                        </ul>
                    </div>
                </div>

                @guest
                <a href="{{ route('login') }}" class="hover:text-gray-300">Login</a>
                <a href="{{ route('register') }}" class="hover:text-gray-300">Sign Up</a>
                @else
                <div x-data="{ userOpen:false }" class="relative">
                    <button @click="userOpen = !userOpen"
                        class="flex items-center gap-2 text-sm cursor-pointer text-white hover:text-gray-300 transition">
                        <span class="font-semibold">{{ auth()->user()->name }}</span>
                        <svg class="w-4 h-4 transition-transform duration-300" :class="userOpen ? 'rotate-180' : ''"
                            fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06L10 14.59 5.23 8.27z" />
                        </svg>
                    </button>

                    <div x-show="userOpen" @click.outside="userOpen = false"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                        class="absolute right-0 mt-2 w-44 bg-white rounded-lg shadow-lg border border-gray-100 z-50 py-2"
                        style="display: none !important">
                        <ul class="text-sm">
                            @role('admin|cso')
                            <li>
                                <a href="{{ route('dashboard') }}"
                                    class="flex items-center px-4 py-2 hover:bg-gray-50 text-gray-700">Dashboard</a>
                            </li>
                            @endrole
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit"
                                        class="w-full text-left px-4 py-2 hover:bg-gray-50 text-gray-700">Logout</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
                @endguest
            </div>
        </div>
    </div>

    <!-- MOBILE MENU (opens below logo) -->
    <div x-show="open" class="lg:hidden bg-[#032A36] text-white text-sm px-6 pb-4 space-y-4"
        style="display: none !important;">
        <a href="{{ route('about-user', ['locale' => app()->getLocale()]) }}" class="block">About</a>
        <a href="{{ route('public.dashboard', ['locale' => app()->getLocale(), 'filter' => 'active']) }}"
            class="block">Under Investigation</a>
        <a href="{{ route('public.dashboard', ['locale' => app()->getLocale(), 'filter' => 'published']) }}"
            class="block">Published Cases</a>
        <a href="{{ route('public.dashboard', ['locale' => app()->getLocale(), 'filter' => 'closed']) }}"
            class="block">Closed Cases</a>
        <a href="{{ route('report.form', ['locale' => app()->getLocale()]) }}" class="block">Report a Case</a>
        <a href="#" class="block">Verified Case</a>
        {{-- <a href="#" class="block">Documentation</a> --}}

        <div class="pt-3 border-t border-white/10">
            @guest
            <a href="{{ route('login') }}" class="block pt-2">Login</a>
            <a href="{{ route('register') }}" class="block pt-2">Sign Up</a>
            @else
            <div class="pt-2">
                <div class="font-semibold">{{ auth()->user()->name }}</div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="block pt-2 text-left">Logout</button>
                </form>
            </div>
            @endguest
        </div>
    </div>
</nav>