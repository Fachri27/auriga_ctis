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
    <div class="text-lg font-bold text-gray-900 leading-tight mb-1 tracking-tight">
        {{ $case->case_number ?? 'No. Kasus' }}
    </div>

    {{-- Title --}}
    @php
        $_vtrans = $case->translations->where('locale', app()->getLocale())->first();
    @endphp
    @if ($_vtrans?->title)
    <p class="text-sm text-gray-600 mb-2 leading-snug">
        {{ strip_tags($_vtrans->title) }}
    </p>
    @endif

    {{-- Meta --}}
    <div class="grid grid-cols-2 gap-x-4 gap-y-2 py-3 border-t border-b border-gray-100 mb-3">
        <div>
            <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-0.5">Kategori</p>
            <p class="text-xs text-gray-800">
                @php
                    $locale = app()->getLocale();
                @endphp
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
    <p class="text-sm text-gray-500 leading-relaxed flex-1">
        @php
            $locale = app()->getLocale();
            $trans = $case->translations->where('locale', $locale)->first();
        @endphp
        {!! Str::limit(strip_tags($trans?->description ?? ($case->description ?? '—')), 180) !!}
    </p>

    {{-- Footer --}}
    <div class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between">
        <a href="{{ route('public.verify.case', $case->case_number) }}"
            class="text-xs font-bold uppercase tracking-widest text-[#032A36] hover:text-[#264c16] transition-colors after:content-['_→']">
            Lihat Detail
        </a>
    </div>

</div>