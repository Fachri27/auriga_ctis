@extends('layouts.main')

@php
    $trans = $case->translations->firstWhere('locale', app()->getLocale()) ?? $case->translations->first();
    $pageTitle = strip_tags($trans?->title ?? __('Detail Kasus')) . ' — greendefender';
    // Lokasi sesuai teks dokumen/CMS; bila kosong, nama wilayah dari titik peta (reverse geocoding).
    $placeText = $case->lokasi
        ?: collect([$location['village'] ?? null, $location['district'] ?? null, $location['province'] ?? null])->filter()->join(', ');
    $pageDescription = __('Kasus #:number — :title. Status: :status. Lokasi: :location. Lacak perkembangan kasus hukum lingkungan ini.', [
        'number' => $case->case_number ?? '',
        'title' => strip_tags($trans?->title ?? ''),
        'status' => __($case->status->name ?? '-'),
        'location' => $case->lokasi ?: ($location['province'] ?? '-'),
    ]);
    $ogTitle = $pageTitle;
    $ogDescription = $pageDescription;
    // Gambar pratinjau tautan & data terstruktur: hanya foto sampul yang sudah dipilih tim.
    // Berkas Bukti tidak pernah dipakai di sini karena bisa memuat data sensitif (wajah, identitas, dokumen).
    $ogImage = $case->cover_url ?? asset('img/image.png');
    $coverCaption = $trans?->cover_caption ?: $case->translations->firstWhere('locale', 'id')?->cover_caption;
    // Tombol bahasa di menu langsung menuju alamat kasus versi bahasa itu (judulnya ikut bahasa).
    $localeUrls = ['id' => $case->publicUrl('id'), 'en' => $case->publicUrl('en')];
    $ogType = 'article';
@endphp

@section('structured-data')
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "NewsArticle",
    "headline": {!! json_encode(strip_tags($trans?->title ?? __('Detail Kasus'))) !!},
    "description": {!! json_encode($pageDescription) !!},
    "image": "{{ $ogImage }}",
    "datePublished": "{{ optional($case->published_at)->toIso8601String() }}",
    "dateModified": "{{ optional($case->updated_at)->toIso8601String() }}",
    "author": {
        "@@type": "Organization",
        "name": "greendefender",
        "url": "{{ url('/') }}"
    },
    "publisher": {
        "@@type": "Organization",
        "name": "greendefender",
        "logo": {
            "@@type": "ImageObject",
            "url": "{{ asset('img/image.png') }}"
        }
    },
    "mainEntityOfPage": {
        "@@type": "WebPage",
        "@@id": "{{ url()->current() }}"
    },
    "articleSection": {!! json_encode(__('Hukum Lingkungan')) !!},
    "keywords": "{{ __('kasus lingkungan') }}, {{ __('hukum lingkungan') }}, {{ $case->case_number ?? '' }}, {{ $location['province'] ?? '' }}"
}
</script>
@endsection

@section('content')

<style>
.case-summary img,
.case-summary video,
.case-summary iframe {
    max-width: 100%;
    height: auto;
}
.case-summary {
    word-break: break-word;
    overflow-wrap: break-word;
}

/* monochrome map tiles */
#map .leaflet-tile,
#mapFull .leaflet-tile { filter: grayscale(1); }

/* collapsible long sections */
.collapsible.clamped {
    max-height: 26rem;
    overflow: hidden;
    position: relative;
}
.collapsible.clamped::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 5rem;
    background: linear-gradient(to top, #fff, transparent);
    pointer-events: none;
}

/* Custom prose styling for TinyMCE content */
.prose-content p { margin-bottom: 1rem; }
.prose-content p:last-child { margin-bottom: 0; }
.prose-content h1,
.prose-content h2,
.prose-content h3,
.prose-content h4 {
    font-weight: 700;
    margin-top: 1.5rem;
    margin-bottom: 0.75rem;
}
.prose-content h1 { font-size: 1.875rem; }
.prose-content h2 { font-size: 1.5rem; }
.prose-content h3 { font-size: 1.25rem; }
.prose-content ul,
.prose-content ol {
    margin: 1rem 0;
    padding-left: 1.5rem;
}
.prose-content ul { list-style-type: disc; }
.prose-content ol { list-style-type: decimal; }
.prose-content li { margin-bottom: 0.5rem; }
.prose-content a { color: #111827; text-decoration: underline; }
.prose-content strong { font-weight: 700; }
.prose-content em { font-style: italic; }
.prose-content img { max-width: 100%; height: auto; }
</style>

    <div class="bg-white text-gray-900 mt-16">

        @php
            $trans = $case->translations->firstWhere('locale', app()->getLocale()) ?? $case->translations->first();

            $categoryTrans =
                $case->category?->translations->firstWhere('locale', app()->getLocale()) ??
                $case->category?->translations->first();

            $noTransMsg = '<em class="text-gray-400">' . e(__('Konten tidak tersedia dalam bahasa ini')) . '</em>';

            // Tahapan kasus mengikuti kualifikasinya (pidana / perdata / administrasi), lihat App\Services\CaseStages
            // Key dinormalkan karena ada data lama berbentuk "Berkekuatan hukum tetap".
            $currentKey = \Illuminate\Support\Str::slug($case->status?->key ?? '');
            // Keterangan custom per tahapan hanya dari bahasa halaman ini; yang kosong memakai keterangan standar.
            $stageNotes = $case->translations->firstWhere('locale', app()->getLocale())?->keterangan_tahapan ?? [];
            ['steps' => $visibleSteps, 'current' => $currentStep] = \App\Services\CaseStages::for($case->kualifikasi, $currentKey, $stageNotes);

            // Penjelasan status dalam bahasa sederhana
            $statusExplainer = [
                'open' => __('Kasus ini baru saja masuk dan sedang menunggu proses lebih lanjut dari penegak hukum.'),
                'verified' => __('Laporan kasus ini sudah diverifikasi kebenarannya oleh tim kami.'),
                'published' => __('Kasus ini sudah dipublikasikan agar masyarakat dapat mengikuti perkembangannya.'),
                'penyelidikan' => __('Penegak hukum sedang mengumpulkan bukti awal. Belum ada tersangka resmi saat ini.'),
                'investigation' => __('Penegak hukum sedang mengumpulkan bukti awal. Belum ada tersangka resmi saat ini.'),
                'penyidikan' => __('Sudah ada tersangka. Penyidik resmi sedang mengumpulkan alat bukti untuk pelimpahan ke jaksa.'),
                'prosecution' => __('Jaksa sedang menyusun surat dakwaan. Kasus ini akan segera masuk ke pengadilan.'),
                'trial' => __('Sidang sedang berlangsung di pengadilan. Hakim, jaksa, dan pengacara memeriksa fakta-fakta kasus.'),
                'gugatan' => __('Gugatan perdata sudah didaftarkan ke pengadilan dan menunggu proses persidangan.'),
                'pembuktian' => __('Persidangan memasuki tahap pembuktian. Para pihak mengajukan bukti, saksi, dan ahli.'),
                'sanksi' => __('Pemerintah telah menjatuhkan sanksi administratif kepada pihak yang bertanggung jawab.'),
                'vonis' => __('Hakim telah menjatuhkan putusan. Terdakwa dinyatakan bersalah atau bebas.'),
                'berkekuatan-hukum-tetap' => __('Putusan sudah final dan tidak bisa digugat lagi. Proses banding atau kasasi telah selesai.'),
                'executed' => __('Terpidana sedang menjalani hukuman — baik penjara, denda, maupun hukuman lainnya.'),
                'completed' => __('Seluruh proses hukum pada kasus ini telah selesai.'),
                'closed' => __('Kasus ini telah ditutup.'),
                'rejected' => __('Kasus ini ditolak karena tidak memenuhi syarat atau tidak cukup bukti.'),
                'converted' => __('Status kasus ini telah dikonversi ke proses atau jalur hukum lain.'),
            ];
            if ($case->kualifikasi === 'perdata') {
                $statusExplainer['vonis'] = __('Hakim telah menjatuhkan putusan atas gugatan ini.');
            }
            $explainer = $statusExplainer[$currentKey] ?? null;

            // Kualifikasi kasus (jalur hukum yang ditempuh), lihat CaseModel::KUALIFIKASI
            $kualifikasiExplainer = [
                'pidana' => __('Ditangani lewat jalur pidana: penyelidikan, penyidikan, penuntutan, hingga putusan pengadilan.'),
                'perdata' => __('Ditangani lewat gugatan perdata, misalnya tuntutan ganti rugi atau pemulihan lingkungan.'),
                'administrasi' => __('Ditangani lewat sanksi administratif dari pemerintah, misalnya teguran, paksaan pemerintah, atau pencabutan izin.'),
            ][$case->kualifikasi ?? ''] ?? null;

            $kategori = $categories
                ->map(function ($cat) {
                    $t = $cat->translations->firstWhere('locale', app()->getLocale()) ?? $cat->translations->first();
                    return $t?->name ?? '-';
                })
                ->join(', ');

            // Perkembangan: bisa berupa JSON timeline atau HTML bebas
            $perkembangan = $trans->perkembangan ?? null;
            $perkembanganArr = null;
            if (is_string($perkembangan)) {
                $json = json_decode($perkembangan, true);
                if (is_array($json)) {
                    $perkembanganArr = $json;
                }
            }
        @endphp

        {{-- ===================== HEADER ===================== --}}
        <header class="border-b border-gray-900">
            <div class="max-w-6xl mx-auto px-6 py-10">
                <div class="flex items-center gap-3 flex-wrap text-[11px] tracking-widest uppercase text-gray-500">
                    <span>{{ __('Kasus :number', ['number' => $case->case_number]) }}</span>
                    @if ($categoryTrans?->name)
                        <span class="text-gray-300">/</span>
                        <span>{{ $categoryTrans->name }}</span>
                    @endif
                </div>
                <h1 class="mt-3 text-3xl md:text-5xl font-bold leading-tight tracking-tight max-w-4xl">
                    {!! $trans?->title ?? $noTransMsg !!}
                </h1>
                <div class="mt-5 flex items-center gap-3 flex-wrap text-sm">
                    <span class="px-2.5 py-1 bg-gray-900 text-white text-xs font-semibold">{{ __($case->status->name ?? 'Status') }}</span>
                    @if ($explainer)
                        <p class="text-gray-600">{{ $explainer }}</p>
                    @endif
                </div>
            </div>
        </header>

        {{-- ===================== DOSSIER ===================== --}}
        <div class="max-w-6xl mx-auto px-6 grid grid-cols-1 lg:grid-cols-[320px_1fr]">

            {{-- ========== SIDEBAR ========== --}}
            <aside class="py-10 lg:pr-10 lg:border-r lg:border-gray-200">
                <div class="lg:sticky lg:top-24 space-y-8">

                    {{-- kualifikasi --}}
                    <section>
                        <h2 class="text-[11px] font-semibold uppercase tracking-widest text-gray-400 mb-4">{{ __('Kualifikasi Kasus') }}</h2>
                        @if ($case->kualifikasi_label)
                            <span class="inline-block px-2.5 py-1 bg-gray-900 text-white text-xs font-semibold">{{ $case->kualifikasi_label }}</span>
                            @if ($kualifikasiExplainer)
                                <p class="mt-2 text-xs text-gray-500 leading-relaxed">{{ $kualifikasiExplainer }}</p>
                            @endif
                        @else
                            <p class="text-xs text-gray-400 italic">{{ __('Belum ditentukan') }}</p>
                        @endif
                    </section>

                    {{-- tahapan: vertical rail --}}
                    <section>
                        <h2 class="text-[11px] font-semibold uppercase tracking-widest text-gray-400 mb-4">{{ __('Tahapan Kasus') }}</h2>
                        <ol class="relative border-l border-gray-200 ml-[5px] space-y-4">
                            @foreach ($visibleSteps as $step => $info)
                                @php
                                    $active = $currentStep === $step;
                                    $reached = $currentStep >= $step;
                                @endphp
                                <li class="relative pl-5">
                                    <span class="absolute -left-[5.5px] top-1 w-2.5 h-2.5 rounded-full
                                        {{ $active ? 'bg-gray-900 ring-4 ring-gray-200' : ($reached ? 'bg-gray-900' : 'bg-white border border-gray-300') }}"></span>
                                    <p class="text-xs leading-snug {{ $active ? 'font-bold' : ($reached ? 'font-medium text-gray-700' : 'text-gray-400') }}">
                                        {{ $info['label'] }}
                                    </p>
                                    {{-- keterangan tiap tahapan --}}
                                    <p class="mt-1 text-xs leading-relaxed {{ $active ? 'text-gray-600' : ($reached ? 'text-gray-500' : 'text-gray-400') }}">{!! nl2br(e($info['desc'])) !!}</p>
                                </li>
                            @endforeach
                        </ol>
                    </section>

                    {{-- fakta --}}
                    <section>
                        <h2 class="text-[11px] font-semibold uppercase tracking-widest text-gray-400 mb-4">{{ __('Fakta Kasus') }}</h2>
                        <dl class="text-xs divide-y divide-gray-100 border-y border-gray-100">
                            @foreach ([
                                __('Nomor Kasus') => $case->case_number,
                                __('Tanggal Kejadian') => $case->event_date ?? '-',
                                __('Kategori') => $kategori ?: '-',
                                ...($case->lokasi ? [__('Lokasi') => $case->lokasi] : [
                                    __('Provinsi') => $location['province'] ?? '-',
                                    __('Kab/Kota') => $location['district'] ?? '-',
                                    __('Desa/Kelurahan') => $location['village'] ?? '-',
                                ]),
                                __('Dipublikasikan') => optional($case->published_at)->format('d M Y') ?? '-',
                                __('Diperbarui') => optional($case->updated_at)->format('d M Y, H:i') . ' WIB',
                            ] as $label => $value)
                                <div class="py-2 flex justify-between gap-4">
                                    <dt class="text-gray-400 flex-shrink-0">{{ $label }}</dt>
                                    <dd class="font-medium text-right break-words">{{ $value }}</dd>
                                </div>
                            @endforeach
                        </dl>
                    </section>

                    {{-- peta (teks lokasi tetap tampil walau kasus belum punya titik peta) --}}
                    @if (($case->latitude && $case->longitude) || $case->lokasi)
                        <section>
                            <h2 class="text-[11px] font-semibold uppercase tracking-widest text-gray-400 mb-4">{{ __('Lokasi') }}</h2>
                            @if ($case->latitude && $case->longitude)
                            <div class="relative">
                                <div id="map" class="h-52 w-full border border-gray-200"></div>
                                <button onclick="openMapModal()" aria-label="{{ __('Perbesar peta') }}" title="{{ __('Perbesar peta') }}"
                                    class="absolute top-2 right-2 z-[1000] w-8 h-8 bg-white border border-gray-300 text-gray-600 hover:border-gray-900 hover:text-gray-900 transition-colors flex items-center justify-center cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
                                    </svg>
                                </button>
                            </div>
                            @endif
                            <p class="{{ $case->latitude && $case->longitude ? 'mt-2 text-xs text-gray-500' : 'text-sm text-gray-700' }}">
                                {{ $placeText ?: __('Lokasi tidak diketahui') }}
                            </p>
                        </section>
                    @endif

                    {{-- bagikan --}}
                    <section>
                        <h2 class="text-[11px] font-semibold uppercase tracking-widest text-gray-400 mb-4">{{ __('Bagikan') }}</h2>
                        @include('front.components.share-buttons', ['url' => $case->publicUrl(), 'title' => $trans?->title ?? __('Detail Kasus')])
                    </section>

                    {{-- ikuti --}}
                    @if (! in_array($case->status?->key, ['completed', 'closed']))
                        <section class="border border-gray-900 p-5">
                            <h2 class="flex items-center gap-2 text-[11px] font-semibold uppercase tracking-widest text-gray-500">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/><path d="M4 2C2.8 3.7 2 5.7 2 8"/><path d="M22 8c0-2.3-.8-4.3-2-6"/></svg>
                                {{ __('Kawal Kasus Ini') }}
                            </h2>
                            <p class="mt-3 text-base font-bold leading-snug text-gray-900">{{ __('Jangan biarkan kasus ini senyap.') }}</p>
                            <p class="mt-2 mb-4 text-sm text-gray-500 leading-relaxed">
                                {{ __('Kami kabari lewat email setiap kali status kasus ini berubah, dari penyidikan hingga putusan.') }}
                            </p>
                            @livewire('case-subscribe-form', ['caseId' => $case->id])
                        </section>
                    @endif
                </div>
            </aside>

            {{-- ========== DOCUMENT ========== --}}
            <main class="py-10 lg:pl-10 min-w-0">
                <article class="max-w-3xl space-y-12">

                    {{-- Foto sampul --}}
                    @if ($case->cover_url)
                        <figure>
                            <img src="{{ $case->cover_url }}" alt="{{ $coverCaption ?: strip_tags($trans?->title ?? $case->case_number) }}"
                                class="w-full aspect-video object-cover border border-gray-200">
                            @if ($coverCaption || $case->cover_credit)
                                <figcaption class="mt-2 text-xs text-gray-500 leading-relaxed">
                                    {{ $coverCaption }}
                                    @if ($case->cover_credit)
                                        <span class="text-gray-400">{{ $coverCaption ? '· ' : '' }}{{ __('Foto: :credit', ['credit' => $case->cover_credit]) }}</span>
                                    @endif
                                </figcaption>
                            @endif
                        </figure>
                    @endif

                    {{-- Kronologi --}}
                    <section id="kronologi">
                        <h2 class="text-[11px] font-semibold uppercase tracking-widest text-gray-400 pb-3 border-b border-gray-900 mb-5">{{ __('Kronologi') }}</h2>
                        @if ($trans?->description)
                            <div class="collapsible prose prose-gray max-w-none case-summary">{!! $trans->description !!}</div>
                        @else
                            <p class="text-sm text-gray-400 italic">{{ __('Kronologi belum tersedia.') }}</p>
                        @endif
                    </section>

                    {{-- Perkembangan --}}
                    <section id="perkembangan">
                        <h2 class="text-[11px] font-semibold uppercase tracking-widest text-gray-400 pb-3 border-b border-gray-900 mb-5">{{ __('Perkembangan') }}</h2>
                        @if ($perkembanganArr)
                            <ol class="collapsible relative border-l border-gray-200 ml-[5px] space-y-8">
                                @forelse ($perkembanganArr as $entry)
                                    <li class="relative pl-6">
                                        <span class="absolute -left-[5.5px] top-1.5 w-2.5 h-2.5 rounded-full bg-gray-900"></span>
                                        @if (!empty($entry['created_at']))
                                            <p class="text-[11px] text-gray-400 mb-1">
                                                {{ \Carbon\Carbon::parse($entry['created_at'])->format('d M Y H:i') }}
                                            </p>
                                        @endif
                                        <h3 class="font-semibold text-base text-gray-900">{{ $entry['title'] ?? __('Perkembangan Kasus') }}</h3>
                                        <p class="text-base text-gray-700 mt-1 leading-7">{{ $entry['notes'] ?? '' }}</p>
                                    </li>
                                @empty
                                    <p class="text-sm text-gray-400 italic">{{ __('Belum ada perkembangan.') }}</p>
                                @endforelse
                            </ol>
                        @elseif(!empty($perkembangan))
                            <div class="collapsible prose prose-gray max-w-none case-summary">{!! $perkembangan !!}</div>
                        @else
                            <p class="text-sm text-gray-400 italic">{{ __('Belum ada perkembangan.') }}</p>
                        @endif
                    </section>

                    {{-- Dugaan Permasalahan --}}
                    {{-- <section id="permasalahan">
                        <h2 class="text-[11px] font-semibold uppercase tracking-widest text-gray-400 pb-3 border-b border-gray-900 mb-5">Dugaan Permasalahan</h2>
                        @if ($trans?->dugaan_permasalahan)
                            <div class="collapsible prose prose-gray max-w-none case-summary">{!! $trans->dugaan_permasalahan !!}</div>
                        @else
                            <p class="text-sm text-gray-400 italic">Dugaan permasalahan belum tersedia.</p>
                        @endif
                    </section> --}}

                    {{-- Pembelajaran --}}
                    <section id="pembelajaran">
                        <h2 class="text-[11px] font-semibold uppercase tracking-widest text-gray-400 pb-3 border-b border-gray-900 mb-5">Lesson Learning</h2>
                        @if ($trans?->pembelajaran)
                            <div class="collapsible prose prose-gray max-w-none case-summary">{!! $trans->pembelajaran !!}</div>
                        @else
                            <p class="text-sm text-gray-400 italic">{{ __('Pembelajaran belum tersedia.') }}</p>
                        @endif
                    </section>

                    {{-- Status narasi --}}
                    <section id="status">
                        <h2 class="text-[11px] font-semibold uppercase tracking-widest text-gray-400 pb-3 border-b border-gray-900 mb-5">{{ __('Status') }}</h2>
                        @if ($case->status_narasi)
                            <div class="collapsible prose prose-gray max-w-none case-summary">{!! $case->status_narasi !!}</div>
                        @else
                            <p class="text-sm text-gray-400 italic">{{ __('Status belum tersedia.') }}</p>
                        @endif
                    </section>
                </article>
            </main>
        </div>

        {{-- ===================== TERKAIT ===================== --}}
        @php
            $hasArtikels = isset($artikels) && count($artikels);
            $hasRelated = isset($relatedCases) && $relatedCases->count();
        @endphp
        @if ($hasArtikels || $hasRelated)
            <div class="border-t border-gray-900 bg-[#F5F7F1]">
                <div class="max-w-6xl mx-auto px-6 py-12 md:py-16">

                    {{-- artikel terkait: kartu yang sama dengan halaman Artikel --}}
                    @if ($hasArtikels)
                        <section>
                            <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3 mb-6">
                                <div>
                                    <p class="font-data uppercase tracking-[0.2em] text-xs text-[#264c16] mb-2">{{ __('Bacaan terkait') }}</p>
                                    <h2 class="font-display text-2xl md:text-3xl font-bold text-[#0B1E07]">{{ __('Artikel Terkait') }}</h2>
                                </div>
                                <a href="{{ route('public.artikel.list', ['locale' => app()->getLocale()]) }}"
                                    class="font-data text-[10px] uppercase tracking-widest text-[#264c16] hover:text-[#0B1E07] transition-colors">
                                    {{ __('Lihat semua artikel →') }}
                                </a>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                                @include('front.components.artikel-cards', ['artikels' => $artikels])
                            </div>
                        </section>
                    @endif

                    {{-- kasus terkait --}}
                    @if ($hasRelated)
                        <section @class(['mt-12' => $hasArtikels])>
                            <h2 class="text-[11px] font-semibold uppercase tracking-widest text-gray-400 mb-2">{{ __('Kasus Terkait') }}</h2>
                            <div class="divide-y divide-gray-200">
                                @foreach ($relatedCases as $related)
                                    @php
                                        $relatedTrans =
                                            $related->translations->firstWhere('locale', app()->getLocale()) ??
                                            $related->translations->first();
                                    @endphp
                                    <a href="{{ route('public.case.detail', ['slug' => $related->slug]) }}" class="group flex items-start gap-4 py-4">
                                        <div class="min-w-0">
                                            <p class="text-[10px] uppercase tracking-widest text-gray-400">
                                                {{ $related->case_number }} · {{ __($related->status->name ?? '-') }} · {{ $related->location['province'] ?? '-' }}
                                            </p>
                                            <h3 class="mt-0.5 text-sm font-bold leading-snug group-hover:underline">
                                                {{ $relatedTrans?->title ?? __('Tanpa judul') }}
                                            </h3>
                                            <p class="mt-1 text-xs text-gray-500">{{ Str::limit(strip_tags($relatedTrans?->summary ?? ''), 100) }}</p>
                                        </div>
                                        <span class="ml-auto text-gray-300 group-hover:text-gray-900 transition-colors flex-shrink-0">→</span>
                                    </a>
                                @endforeach
                            </div>
                        </section>
                    @endif
                </div>
            </div>
        @endif

        {{-- map modal (di level root halaman agar tidak terjebak stacking context sidebar sticky) --}}
        @if ($case->latitude && $case->longitude)
            <div id="mapModal" class="hidden fixed inset-0 z-[99999] bg-black/60 flex items-center justify-center p-4" onclick="if (event.target === this) closeMapModal()">
                <div class="bg-white w-full max-w-3xl flex flex-col border border-gray-900 shadow-2xl">
                    <div class="flex items-center justify-between gap-4 px-4 py-3 border-b border-gray-200">
                        <p class="text-[11px] uppercase tracking-widest text-gray-500 truncate">
                            {{ __('Lokasi Kasus — :place', ['place' => $placeText ?: __('Tidak diketahui')]) }}
                        </p>
                        <button onclick="closeMapModal()" aria-label="{{ __('Tutup peta') }}"
                            class="flex-shrink-0 w-8 h-8 border border-gray-300 text-gray-600 hover:border-gray-900 hover:text-gray-900 transition-colors flex items-center justify-center cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <div id="mapFull" class="h-[60vh]"></div>
                </div>
            </div>
        @endif

    </div>

@endsection

@push('scripts')
    <script>
        @php
            $caseDetailI18n = [
                'readMore' => __('Selengkapnya'),
                'collapse' => __('Tutup'),
            ];
        @endphp
        const CASE_DETAIL_I18N = @json($caseDetailI18n);

        // ===== Map =====
        const caseLat = {{ $case->latitude ?? 'null' }};
        const caseLng = {{ $case->longitude ?? 'null' }};

        document.addEventListener('DOMContentLoaded', function() {
            if (!caseLat || !caseLng || !document.getElementById('map')) return;
            const map = L.map('map', {
                scrollWheelZoom: false
            }).setView([caseLat, caseLng], 12);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
            L.marker([caseLat, caseLng]).addTo(map);
        });

        // ===== Fullscreen map modal =====
        let fullMap = null;
        function openMapModal() {
            const modal = document.getElementById('mapModal');
            if (!modal || !caseLat || !caseLng) return;
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            if (!fullMap) {
                fullMap = L.map('mapFull').setView([caseLat, caseLng], 13);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(fullMap);
                L.marker([caseLat, caseLng]).addTo(fullMap);
            }
            setTimeout(() => fullMap.invalidateSize(), 50);
        }
        function closeMapModal() {
            const modal = document.getElementById('mapModal');
            if (!modal || modal.classList.contains('hidden')) return;
            modal.classList.add('hidden');
            document.body.style.overflow = '';
        }
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') closeMapModal();
        });

        // ===== Collapsible long sections =====
        document.addEventListener('DOMContentLoaded', function() {
            const CLAMP = 26 * 16; // keep in sync with .collapsible.clamped max-height
            document.querySelectorAll('.collapsible').forEach(el => {
                if (el.scrollHeight <= CLAMP + 80) return; // not long enough to bother
                el.classList.add('clamped');
                const btn = document.createElement('button');
                btn.textContent = CASE_DETAIL_I18N.readMore;
                btn.setAttribute('aria-expanded', 'false');
                btn.className = 'mt-4 px-3 py-1.5 border border-gray-300 text-xs font-semibold text-gray-600 hover:border-gray-900 hover:text-gray-900 transition-colors cursor-pointer';
                btn.addEventListener('click', () => {
                    const clamped = el.classList.toggle('clamped');
                    btn.textContent = clamped ? CASE_DETAIL_I18N.readMore : CASE_DETAIL_I18N.collapse;
                    btn.setAttribute('aria-expanded', String(!clamped));
                    if (clamped) el.closest('section')?.scrollIntoView({ block: 'start' });
                });
                el.after(btn);
            });
        });
    </script>
@endpush
