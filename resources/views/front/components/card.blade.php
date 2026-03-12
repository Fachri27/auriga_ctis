@php
$limit = $limit ?? null;
$offset = $offset ?? 0;

$cases = $limit
? $cases->skip($offset)->take($limit)
: $cases->skip($offset);
@endphp
<style>
    .corner-triangle {
        width: 0;
        height: 0;
        border-top: 20px solid transparent;
        border-right: 20px solid #505153;
        /* warna abu kecil */
        position: absolute;
        bottom: 0;
        right: 0;
    }
</style>

<div class="max-w-7xl mx-auto mt-10 px-4 mb-10 poppins-regular">
    <h2 class="text-slate-500 text-sm font-semibold tracking-wider mb-3">{{ __('messages.kasus') }}</h2>

    <div class="max-w-7xl mx-auto mt-5">
        <div id="case-list" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @forelse($kasus as $case)
            <div
                class="group bg-white border border-gray-200 border-t-4 border-t-gray-900 rounded-sm p-5 flex flex-col hover:shadow-[4px_4px_0_#111] hover:-translate-x-0.5 hover:-translate-y-0.5 transition-all duration-200">

                {{-- Badges --}}
                <div class="flex items-center gap-2 mb-3">
                    <span class="px-2 py-0.5 text-xs font-bold tracking-widest uppercase bg-gray-900 text-white">
                        ✓ Terverifikasi
                    </span>
                    @if($case->published_at)
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
                            $catTrans = $case->category?->translations->where('locale', $locale)->first();
                            @endphp
                            {{-- hasil dari category_ids --}}
                            {{ $categories->whereIn('id', $case->category_ids ?? [])->map(function($cat) use ($locale) {
                                $t = $cat->translations->firstWhere('locale', $locale)
                                ?? $cat->translations->first();
                                return $t ? $t->name : 'Kategori';
                            })->implode(', ') }}
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
                    {!! Str::limit(strip_tags($trans?->description ?? $case->description ?? '—'), 180) !!}
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
</div>