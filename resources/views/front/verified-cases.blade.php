@extends('layouts.main')

@section('content')
    <div class="max-w-7xl mx-auto px-4 py-10 mt-20 poppins-regular">
        <div class="mb-8">
            <h1 class="text-3xl font-black text-gray-900 mb-2">Kasus Terverifikasi & Dipublikasikan</h1>
            <p class="text-gray-600">Daftar lengkap kasus yang telah diverifikasi dan dipublikasikan untuk transparansi
                publik.</p>
        </div>

        <div id="case-list" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @forelse($cases as $case)
                <div
                    class="group bg-white border border-gray-200 border-t-4 border-t-gray-900 rounded-sm p-5 flex flex-col hover:shadow-[4px_4px_0_#111] hover:-translate-x-0.5 hover:-translate-y-0.5 transition-all duration-200">

                    {{-- Badges --}}
                    <div class="flex items-center gap-2 mb-3">
                        <span class="px-2 py-0.5 text-xs font-bold tracking-widest uppercase bg-gray-900 text-white">
                            ✓ Terverifikasi
                        </span>
                        @if ($case->published_at)
                            <span
                                class="px-2 py-0.5 text-xs font-bold tracking-widest uppercase border border-[#032A36] text-[#032A36]">
                                Dipublikasikan
                            </span>
                        @endif
                    </div>

                    {{-- Case Number --}}
                    <div class="text-xl font-black text-gray-900 leading-tight mb-3 tracking-tight">
                        {{ $case->case_number ?? 'No. Kasus' }}
                    </div>

                    {{-- Meta --}}
                    <div class="grid grid-cols-2 gap-x-4 gap-y-2 py-3 border-t border-b border-gray-100 mb-3">
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-0.5">Kategori</p>
                            <p class="text-xs text-gray-800">
                                @php
                                    $locale = app()->getLocale();
                                @endphp
                                {{-- hasil dari category_ids --}}
                                @if (isset($categories))
                                    {{ $categories->whereIn('id', $case->category_ids ?? [])->map(function ($cat) use ($locale) {
                                            $t = $cat->translations->firstWhere('locale', $locale) ?? $cat->translations->first();
                                            return $t ? $t->name : 'Kategori';
                                        })->implode(', ') }}
                                @else
                                    {{ $case->category?->translations->firstWhere('locale', $locale)?->name ?? 'Kategori' }}
                                @endif
                            </p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-0.5">Status</p>
                            <p class="text-xs text-gray-800">{{ $case->current_status_label }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-0.5">Tanggal Kejadian
                            </p>
                            <p class="text-xs text-gray-800">
                                {{ $case->event_date ? date('d M Y', strtotime($case->event_date)) : '—' }}
                            </p>
                        </div>
                    </div>

                    {{-- Excerpt --}}
                    <p class="text-sm text-gray-500 leading-relaxed italic flex-1">
                        @php
                            $locale = app()->getLocale();
                            $trans = $case->translations->where('locale', $locale)->first();
                        @endphp
                        {!! Str::limit(strip_tags($trans?->description ?? ($case->description ?? '—')), 180) !!}
                    </p>

                    {{-- Footer --}}
                    <div class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between">
                        <a href="{{ route('public.verify.case', $case->case_number) }}"
                            class="text-xs font-bold uppercase tracking-widest text-[#032A36] hover:text-red-900 transition-colors after:content-['_→']">
                            Lihat Detail
                        </a>
                    </div>

                </div>
            @empty
                <div class="col-span-3 text-center text-gray-400 italic py-16 text-base">
                    Belum ada kasus terverifikasi &amp; dipublikasikan.
                </div>
            @endforelse
        </div>

        <div id="loading-spinner" class="w-full hidden justify-center py-8">
            <div class="w-8 h-8 border-2 border-gray-200 border-t-gray-900 rounded-full animate-spin"></div>
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
