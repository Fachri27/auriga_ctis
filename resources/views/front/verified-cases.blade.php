@extends('layouts.main')

@php
    $pageTitle = __('Kasus Terverifikasi') . ' — greendefender';
    $pageDescription = __('Daftar kasus hukum lingkungan yang telah diverifikasi dan dipublikasikan. Lacak perkembangan dari penyelidikan hingga putusan pengadilan.');
@endphp

@section('content')
    <div id="verified-cases-page" class="mt-16">
        <x-front.page-hero label="Monitoring Console" :title="__('Kasus Terverifikasi & Dipublikasikan')"
            :description="__('Daftar lengkap kasus yang telah diverifikasi dan dipublikasikan untuk transparansi publik.')" />

        {{-- ponytail: infinite-scroll JS retained unchanged to preserve AJAX contract --}}
        <section class="bg-[#F5F7F1] py-12 md:py-16">
            <div class="max-w-7xl mx-auto px-4">
                <div id="case-list" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                    @forelse($cases as $case)
                        @include('front.verified-case-card')
                    @empty
                        <div class="col-span-1 md:col-span-2 lg:col-span-3 text-center text-[#6b7268] py-16 text-base">
                            {{ __('Belum ada kasus terverifikasi & dipublikasikan.') }}
                        </div>
                    @endforelse
                </div>

                @if ($cases->hasMorePages())
                    <div id="load-more-sentinel" class="w-full h-1"></div>
                    <div id="loading-spinner" class="w-full flex justify-center py-8">
                        <div class="w-8 h-8 border-2 border-[#E2E6DA] border-t-[#0B1E07] rounded-full animate-spin"></div>
                    </div>
                @endif
            </div>
        </section>
    </div>

    @push('scripts')
        <script>
            let nextPage = {{ $cases->hasMorePages() ? $cases->currentPage() + 1 : 'null' }};
            let loadingMore = false;
            let scrollFired = false;
            const spinner = document.getElementById('loading-spinner');

            async function loadMore() {
                if (loadingMore || !nextPage) return;
                loadingMore = true;

                try {
                    const res = await fetch('{{ url()->current() }}?page=' + nextPage, {
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    const data = await res.json();
                    document.getElementById('case-list').insertAdjacentHTML('beforeend', data.html);
                    nextPage = data.nextPage;
                    if (!nextPage && spinner) spinner.remove();
                } catch (e) {
                    console.error('Failed to load more cases', e);
                } finally {
                    loadingMore = false;
                }
            }

            const sentinel = document.getElementById('load-more-sentinel');
            if (sentinel) {
                const observer = new IntersectionObserver(function (entries) {
                    if (entries[0].isIntersecting && scrollFired) {
                        loadMore();
                    }
                }, { rootMargin: '300px' });
                observer.observe(sentinel);

                window.addEventListener('scroll', function () {
                    scrollFired = true;
                }, { once: true });
            }
        </script>
    @endpush
@endsection
