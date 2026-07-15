@php
    $_vtrans = $case->translations->where('locale', app()->getLocale())->first();
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
        @if ($_vtrans?->title)
            <h3 class="font-display text-base font-bold text-gray-900 leading-snug mb-3">
                {{ Str::limit(strip_tags($_vtrans->title), 110) }}
            </h3>
        @endif

        <p class="text-sm text-gray-500 leading-relaxed flex-1">
            {{ Str::limit(strip_tags($_vtrans?->description ?? ($case->description ?? '')), 150) ?: '—' }}
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
