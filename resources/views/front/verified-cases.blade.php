@extends('layouts.main')

@php
    $pageTitle = 'Kasus Terverifikasi — Auriga CTIS';
    $pageDescription = 'Daftar kasus hukum lingkungan yang telah diverifikasi dan dipublikasikan. Lacak perkembangan dari penyelidikan hingga putusan pengadilan.';
@endphp

@section('content')
    <div class="max-w-7xl mx-auto px-4 py-10 mt-16 poppins-regular">
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-900 mb-2">Kasus Terverifikasi & Dipublikasikan</h1>
            <p class="text-gray-600">Daftar lengkap kasus yang telah diverifikasi dan dipublikasikan untuk transparansi
                publik.</p>
        </div>

        <div id="case-list" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @forelse($cases as $case)
                @include('front.verified-case-card')
            @empty
                <div class="col-span-3 text-center text-gray-400 py-16 text-base">
                    Belum ada kasus terverifikasi &amp; dipublikasikan.
                </div>
            @endforelse
        </div>

        @if ($cases->hasMorePages())
        <div id="load-more-sentinel" class="w-full h-1"></div>
        <div id="loading-spinner" class="w-full flex justify-center py-8">
            <div class="w-8 h-8 border-2 border-gray-200 border-t-gray-900 rounded-full animate-spin"></div>
        </div>
        @endif
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
