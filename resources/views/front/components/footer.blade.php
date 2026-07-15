<footer class="mt-auto bg-[#0B1E07] border-t border-white/10 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-14 grid grid-cols-1 lg:grid-cols-12 gap-10">

        {{-- Identitas --}}
        <div class="lg:col-span-8">
            <img src="/img/image.png" alt="Auriga Nusantara" class="h-14 w-auto object-contain">
            <p class="mt-5 max-w-lg text-sm text-white/60 leading-relaxed">
                {{ __('messages.footer') }}
            </p>
        </div>

        {{-- Navigasi --}}
        <div class="lg:col-span-4">
            <p class="font-data text-[10px] tracking-[0.24em] uppercase text-white/40 mb-4">Navigasi</p>
            <ul class="space-y-3 font-data text-[11px] uppercase tracking-[0.18em]">
                <li><a href="{{ route('about-user', ['locale' => app()->getLocale()]) }}" class="text-white/75 hover:text-[#9BDB4D] transition-colors">{{ __('messages.about') }}</a></li>
                <li><a href="{{ route('public.dashboard', ['locale' => app()->getLocale(), 'filter' => 'active']) }}" class="text-white/75 hover:text-[#9BDB4D] transition-colors">Dashboard</a></li>
                <li><a href="{{ route('public.artikel.list', ['locale' => app()->getLocale()]) }}" class="text-white/75 hover:text-[#9BDB4D] transition-colors">{{ __('messages.artikel') }}</a></li>
                <li><a href="{{ route('front.verified-cases', ['locale' => app()->getLocale()]) }}" class="text-white/75 hover:text-[#9BDB4D] transition-colors">{{ __('messages.verified_cases') }}</a></li>
            </ul>
        </div>
    </div>

    {{-- Bottom bar --}}
    <div class="border-t border-white/10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2 font-data text-[10px] tracking-[0.18em] uppercase text-white/40">
            <span>&copy; {{ date('Y') }} Yayasan Auriga Nusantara</span>
            <span>CTIS &mdash; Case Tracking Information System</span>
        </div>
    </div>
</footer>
