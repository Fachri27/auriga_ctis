@php
    $limit = $limit ?? null;
    $offset = $offset ?? 0;

    // Use $kasus variable from controller
    $displayCases = $limit ? $kasus->skip($offset)->take($limit) : $kasus->skip($offset);
@endphp

{{-- ponytail: pagination/limit/offset logic and existing route() calls kept as-is; card markup synced with index/verified-cases --}}

<div class="max-w-7xl mx-auto mt-10 px-4 mb-10">
    <h2 class="text-[#6b7268] text-sm font-semibold tracking-wider mb-3">{{ __('messages.kasus') }}</h2>

    <div class="max-w-7xl mx-auto mt-5">
        <div id="case-list" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @forelse($displayCases as $case)
                @php
                    $_trans = $case->translations->where('locale', app()->getLocale())->first();
                    $locale = app()->getLocale();
                    $catNames = '';
                    if (isset($categories)) {
                        $catNames = $categories->whereIn('id', $case->category_ids ?? [])->map(function ($cat) use ($locale) {
                            $t = $cat->translations->firstWhere('locale', $locale) ?? $cat->translations->first();
                            return $t ? $t->name : 'Kategori';
                        })->implode(', ');
                    } else {
                        $catNames = $case->category?->translations->firstWhere('locale', $locale)?->name ?? 'Kategori';
                    }
                @endphp

                <article class="group bg-white border border-[#E2E6DA] flex flex-col hover:border-[#0B1E07] hover:-translate-y-0.5 transition-all duration-200">
                    {{-- Header --}}
                    <div class="px-5 py-3 border-b border-[#E2E6DA] flex items-center justify-between gap-2">
                        <span class="font-data text-[11px] font-semibold text-gray-900 truncate">{{ $case->case_number ?? '—' }}</span>
                        <span class="shrink-0 font-data text-[9px] tracking-[0.16em] uppercase px-2 py-1 bg-[#0B1E07] text-[#9BDB4D]">✓ Terverifikasi</span>
                    </div>

                    {{-- Body --}}
                    <div class="p-5 flex flex-col flex-1">
                        @if ($_trans?->title)
                            <h3 class="font-display text-base font-bold text-gray-900 leading-snug mb-3">
                                {{ Str::limit(strip_tags($_trans->title), 110) }}
                            </h3>
                        @endif

                        <p class="text-sm text-gray-500 leading-relaxed flex-1">
                            {{ Str::limit(strip_tags($_trans?->description ?? ($case->description ?? '')), 150) ?: '—' }}
                        </p>

                        <dl class="mt-4 pt-4 border-t border-[#E2E6DA] grid grid-cols-2 gap-x-4 gap-y-3">
                            <div>
                                <dt class="font-data text-[9px] tracking-[0.16em] uppercase text-gray-400 mb-1">Kategori</dt>
                                <dd class="text-xs text-gray-800">{{ $catNames ?: '—' }}</dd>
                            </div>
                            <div>
                                <dt class="font-data text-[9px] tracking-[0.16em] uppercase text-gray-400 mb-1">Status</dt>
                                <dd class="text-xs text-gray-800">{{ $case->current_status_label ?? '—' }}</dd>
                            </div>
                            <div>
                                <dt class="font-data text-[9px] tracking-[0.16em] uppercase text-gray-400 mb-1">Tanggal Kejadian</dt>
                                <dd class="font-data text-xs text-gray-800">{{ $case->event_date ? date('d M Y', strtotime($case->event_date)) : '—' }}</dd>
                            </div>
                        </dl>
                    </div>

                    {{-- Footer CTA --}}
                    <a href="{{ route('public.verify.case', ['locale' => app()->getLocale(), 'caseNumber' => $case->case_number]) }}"
                        class="px-5 py-3 border-t border-[#E2E6DA] font-data text-[10px] font-semibold uppercase tracking-[0.18em] text-[#2F6C14] group-hover:bg-[#0B1E07] group-hover:text-[#9BDB4D] transition-colors">
                        Lihat Detail Kasus →
                    </a>
                </article>
            @empty
                <div class="col-span-1 md:col-span-2 lg:col-span-3 border border-dashed border-[#E2E6DA] bg-white text-center text-[#6b7268] py-16 text-sm">
                    Belum ada kasus terverifikasi &amp; dipublikasikan.
                </div>
            @endforelse
        </div>

        <div id="loading-spinner" class="w-full hidden justify-center py-8">
            <div class="w-8 h-8 border-2 border-[#E2E6DA] border-t-[#0B1E07] rounded-full animate-spin"></div>
        </div>

        {{-- View More Link --}}
        @if (isset($limit) && $kasus->count() > $limit)
            <div class="mt-8 text-center">
                <a href="{{ route('front.verified-cases') }}"
                    class="inline-flex items-center gap-2 px-6 py-3 bg-[#9BDB4D] text-[#0B1E07] text-sm font-data font-bold uppercase tracking-widest hover:bg-[#9BDB4D]/90 transition-colors">
                    Lihat Semua Kasus
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>
        @endif
    </div>
</div>
