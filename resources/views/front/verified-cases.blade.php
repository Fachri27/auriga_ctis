@extends('layouts.main')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-10 mt-20">
    <h1 class="text-2xl font-bold mb-6">Kasus Terverifikasi & Dipublikasikan</h1>
    <div id="case-list" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($cases as $case)
        <div class="bg-white rounded-xl shadow p-5 border border-gray-100 flex flex-col">
            <div class="mb-2 flex items-center gap-2">
                <span
                    class="inline-block px-2 py-1 text-xs rounded bg-green-100 text-green-700 font-semibold">Verified</span>
                @if($case->published_at)
                <span
                    class="inline-block px-2 py-1 text-xs rounded bg-blue-100 text-blue-700 font-semibold">Published</span>
                @endif
            </div>
            <div class="font-bold text-lg text-gray-800 mb-1">{{ $case->case_number ?? 'No. Kasus' }}</div>
            <div class="text-sm text-gray-600 mb-2">
                Kategori:
                @php
                $locale = app()->getLocale();
                $catTrans = $case->category?->translations->where('locale', $locale)->first();
                @endphp
                {{ $catTrans?->name ?? $case->category?->name ?? '-' }}
            </div>
            <div class="text-sm text-gray-600 mb-2">Status: {{ $case->current_status_label }}</div>
            <div class="text-sm text-gray-600 mb-2">Tanggal Kejadian: {{ $case->event_date ? date('d M Y',
                strtotime($case->event_date)) : '-' }}</div>
            <div class="mt-auto pt-3">
                <a href="{{ route('public.verify.case', $case->case_number) }}" class="text-blue-600 hover:underline text-sm font-semibold">Lihat Detail</a>
            </div>
        </div>
        @empty
        <div class="col-span-3 text-center text-gray-500 py-10">Belum ada kasus terverifikasi & dipublikasikan.</div>
        @endforelse
    </div>
    <div id="loading-spinner" class="w-full flex justify-center py-6 hidden">
        <div class="animate-spin rounded-full h-10 w-10 border-t-2 border-b-2 border-blue-500"></div>
    </div>
</div>

@push('scripts')
<script>
    // Spinner hanya muncul jika kasus banyak (>12)
let loading = false;
const caseCount = {{ count($cases) }};
window.addEventListener('scroll', function() {
    const spinner = document.getElementById('loading-spinner');
    if (caseCount <= 12) {
        spinner.classList.add('hidden');
        return;
    }
    if ((window.innerHeight + window.scrollY) >= document.body.offsetHeight - 200 && !loading) {
        loading = true;
        spinner.classList.remove('hidden');
        setTimeout(() => {
            spinner.classList.add('hidden');
            loading = false;
        }, 1200);
    }
});
</script>
@endpush
@endsection