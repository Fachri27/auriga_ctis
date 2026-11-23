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

    // Akhir paragraf/baris diganti spasi dulu agar kalimat antarparagraf tidak menempel ("miliar.Kasus").
    $excerpt = trim(preg_replace('/\s+/', ' ', html_entity_decode(strip_tags(
        preg_replace('/<\/(p|div|li|h[1-6])>|<br\s*\/?>/i', ' ', (string) ($_vtrans?->description ?? ''))
    ))));
@endphp

<article class="group bg-white border border-[#E2E6DA] flex flex-col hover:border-[#0B1E07] hover:-translate-y-0.5 transition-all duration-200">
    {{-- Header --}}
    <div class="px-5 py-3 border-b border-[#E2E6DA] flex items-center justify-between gap-2">
        <span class="font-data text-[11px] font-semibold text-gray-900 truncate">{{ $case->case_number ?? '—' }}</span>
        <span class="shrink-0 font-data text-[9px] tracking-[0.16em] uppercase px-2 py-1 bg-[#0B1E07] text-[#9BDB4D]">✓ {{ __('Terverifikasi') }}</span>
    </div>

    {{-- Body --}}
    <div class="p-5 flex flex-col flex-1">
        @if ($_vtrans?->title)
            <h3 class="font-display text-base font-bold text-gray-900 leading-snug mb-3">
                {{ Str::limit(strip_tags($_vtrans->title), 110) }}
            </h3>
        @endif

        {{-- Deskripsi maksimal 3 baris. flex-1 di pembungkus, bukan di paragrafnya: paragraf yang diregangkan
             menampilkan baris ke-4. Line clamp ditulis inline karena kelas line-clamp-3 belum ada di CSS hasil build. --}}
        <div class="flex-1">
            <p class="text-sm text-gray-500 leading-relaxed"
                style="display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden">
                {{ $excerpt !== '' ? $excerpt : '—' }}
            </p>
        </div>

        <dl class="mt-4 pt-4 border-t border-[#E2E6DA] grid grid-cols-2 gap-x-4 gap-y-3">
            <div>
                <dt class="font-data text-[9px] tracking-[0.16em] uppercase text-gray-400 mb-1">{{ __('Kategori') }}</dt>
                <dd class="text-xs text-gray-800">{{ $catNames ?: '—' }}</dd>
            </div>
            <div>
                <dt class="font-data text-[9px] tracking-[0.16em] uppercase text-gray-400 mb-1">{{ __('Status') }}</dt>
                <dd class="text-xs text-gray-800">{{ $case->current_status_label ? __($case->current_status_label) : '—' }}</dd>
            </div>
            <div>
                <dt class="font-data text-[9px] tracking-[0.16em] uppercase text-gray-400 mb-1">{{ __('Tanggal Kejadian') }}</dt>
                <dd class="font-data text-xs text-gray-800">{{ $case->event_date ? date('d M Y', strtotime($case->event_date)) : '—' }}</dd>
            </div>
        </dl>
    </div>

    {{-- Footer CTA --}}
    <a href="{{ $case->publicUrl($locale) }}"
        class="px-5 py-3 border-t border-[#E2E6DA] font-data text-[10px] font-semibold uppercase tracking-[0.18em] text-[#2F6C14] group-hover:bg-[#0B1E07] group-hover:text-[#9BDB4D] transition-colors">
        {{ __('Lihat Detail Kasus →') }}
    </a>
</article>
