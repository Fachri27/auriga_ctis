<footer class="mt-auto bg-[#0B1E07] border-t border-white/10 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-14 grid grid-cols-1 lg:grid-cols-12 gap-10">

        {{-- Identitas --}}
        <div class="lg:col-span-8">
            <img src="/img/image.png" alt="Auriga Nusantara" class="h-14 w-auto object-contain">
            <p class="mt-5 max-w-lg text-sm text-white/60 leading-relaxed">
                {{ __('messages.footer') }}
            </p>

            {{-- Kontak & media sosial (config/social.php); ikon garis bergaya Lucide --}}
            @php
                $socials = array_filter([
                    'email' => config('social.email') ? ['label' => 'Email', 'href' => 'mailto:' . config('social.email'),
                        'icon' => '<rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>'] : null,
                    'youtube' => config('social.youtube') ? ['label' => 'YouTube', 'href' => config('social.youtube'),
                        'icon' => '<path d="M2.5 17a24.12 24.12 0 0 1 0-10 2 2 0 0 1 1.4-1.4 49.56 49.56 0 0 1 16.2 0A2 2 0 0 1 21.5 7a24.12 24.12 0 0 1 0 10 2 2 0 0 1-1.4 1.4 49.55 49.55 0 0 1-16.2 0A2 2 0 0 1 2.5 17"/><path d="m10 15 5-3-5-3z"/>'] : null,
                    'instagram' => config('social.instagram') ? ['label' => 'Instagram', 'href' => config('social.instagram'),
                        'icon' => '<rect width="20" height="20" x="2" y="2" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" x2="17.51" y1="6.5" y2="6.5"/>'] : null,
                    'linkedin' => config('social.linkedin') ? ['label' => 'LinkedIn', 'href' => config('social.linkedin'),
                        'icon' => '<path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"/><rect width="4" height="12" x="2" y="9"/><circle cx="4" cy="4" r="2"/>'] : null,
                ]);
            @endphp
            @if ($socials)
                <ul class="mt-6 flex items-center gap-3" aria-label="{{ __('Kontak dan media sosial') }}">
                    @foreach ($socials as $key => $social)
                        <li>
                            <a href="{{ $social['href'] }}" @unless ($key === 'email') target="_blank" rel="noopener" @endunless
                                aria-label="{{ $social['label'] }}" title="{{ $social['label'] }}"
                                class="inline-flex items-center justify-center w-9 h-9 rounded-full border border-white/20 text-white/75 hover:text-[#0B1E07] hover:bg-[#9BDB4D]/90 hover:border-[#9BDB4D] transition-colors">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $social['icon'] !!}</svg>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        {{-- Navigasi --}}
        <div class="lg:col-span-4">
            <p class="font-data text-[10px] tracking-[0.24em] uppercase text-white/40 mb-4">{{ __('Navigasi') }}</p>
            <ul class="space-y-3 font-data text-[11px] uppercase tracking-[0.18em]">
                <li><a href="{{ route('about-user', ['locale' => app()->getLocale()]) }}" class="text-white/75 hover:text-[#9BDB4D] transition-colors">{{ __('messages.about') }}</a></li>
                <li><a href="{{ route('public.dashboard', ['locale' => app()->getLocale(), 'filter' => 'active']) }}" class="text-white/75 hover:text-[#9BDB4D] transition-colors">Dashboard</a></li>
                <li><a href="{{ route('public.artikel.list', ['locale' => app()->getLocale()]) }}" class="text-white/75 hover:text-[#9BDB4D] transition-colors">{{ __('messages.artikel') }}</a></li>
                <li><a href="{{ route('front.verified-cases', ['locale' => app()->getLocale()]) }}" class="text-white/75 hover:text-[#9BDB4D] transition-colors">{{ __('messages.verified_cases') }}</a></li>
                <li><a href="{{ route('front.protocol', ['locale' => app()->getLocale()]) }}" class="text-white/75 hover:text-[#9BDB4D] transition-colors">{{ __('messages.protocol') }}</a></li>
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
