@php
    use Illuminate\Support\Str;

    $completeness = [
        'Deskripsi'           => !empty($case->description),
        'Perkembangan'        => !empty($case->perkembangan),
        'Dugaan Permasalahan' => !empty($case->dugaan_permasalahan),
        'Lesson Learning'     => !empty($case->pembelajaran),
        'Status'              => !empty($case->status_narasi),
        'Bukti'               => !empty($case->bukti) && count($case->bukti) > 0,
        'Pelapor'             => !empty($case->pelapor),
        'Pelaku'              => $actors->isNotEmpty(),
        'Lokasi'              => !empty($location['province']),
    ];
    $completeCount = collect($completeness)->filter()->count();
    $completePct   = round(($completeCount / count($completeness)) * 100);
    $daysSinceUpdate = $case->updated_at ? now()->diffInDays($case->updated_at) : null;
    $isStale   = $daysSinceUpdate !== null && $daysSinceUpdate >= 30;
    $docCount  = isset($documents) ? count($documents) : 0;
    $actorCount = $actors->count();
    $buktiCount = is_array($case->bukti) ? count($case->bukti) : 0;

    // Signature: legal lifecycle rail (real ordered process)
    $legalFlow   = ['open', 'investigation', 'prosecution', 'trial', 'executed', 'closed'];
    $flowLabels  = ['open' => 'Terbuka', 'investigation' => 'Investigasi', 'prosecution' => 'Penuntutan', 'trial' => 'Persidangan', 'executed' => 'Putusan', 'closed' => 'Ditutup'];
    $currentIdx  = array_search($case->status_key, $legalFlow);

    // Progress bar steps (sama dengan public case detail)
    $statusSteps = [
        'open' => ['label' => 'Terbuka', 'step' => 1],
        'verified' => ['label' => 'Terverifikasi', 'step' => 2],
        'published' => ['label' => 'Dipublikasikan', 'step' => 3],
        'penyelidikan' => ['label' => 'Penyelidikan', 'step' => 4],
        'investigation' => ['label' => 'Investigasi', 'step' => 4],
        'penyidikan' => ['label' => 'Penyidikan', 'step' => 5],
        'prosecution' => ['label' => 'Penuntutan', 'step' => 6],
        'trial' => ['label' => 'Persidangan', 'step' => 7],
        'vonis' => ['label' => 'Vonis', 'step' => 8],
        'Berkekuatan hukum tetap' => ['label' => 'Berkekuatan Hukum', 'step' => 9],
        'executed' => ['label' => 'Putusan Dijalankan', 'step' => 10],
        'completed' => ['label' => 'Selesai', 'step' => 11],
        'closed' => ['label' => 'Ditutup', 'step' => 12],
        'rejected' => ['label' => 'Ditolak', 'step' => 12],
        'converted' => ['label' => 'Dikonversi', 'step' => 12],
    ];

    $currentKey = $case->status_key ?? '';
    $currentStep = $statusSteps[$currentKey]['step'] ?? 0;

    $visibleSteps = [
        1 => ['label' => 'Terbuka', 'tooltip' => 'Laporan atau kasus baru masuk dan telah didaftarkan ke sistem.'],
        3 => ['label' => 'Dipublikasikan', 'tooltip' => 'Informasi kasus telah dipublikasikan dan dapat diakses oleh publik.'],
        4 => ['label' => 'Penyelidikan', 'tooltip' => 'Penegak hukum sedang mengumpulkan bukti awal untuk menentukan apakah ada tindak pidana.'],
        5 => ['label' => 'Penyidikan', 'tooltip' => 'Bukti sudah cukup. Penyidik resmi mengusut tersangka dan mengumpulkan alat bukti.'],
        6 => ['label' => 'Penuntutan', 'tooltip' => 'Jaksa menyusun surat dakwaan dan mempersiapkan kasus untuk dibawa ke pengadilan.'],
        7 => ['label' => 'Persidangan', 'tooltip' => 'Kasus sedang disidangkan di pengadilan. Hakim memeriksa bukti dan keterangan saksi.'],
        8 => ['label' => 'Vonis', 'tooltip' => 'Hakim telah menjatuhkan putusan bersalah atau tidak bersalah kepada terdakwa.'],
        9 => ['label' => 'Hukum Tetap', 'tooltip' => 'Putusan telah berkekuatan hukum tetap (inkracht) — tidak bisa digugat lagi.'],
        10 => ['label' => 'Dijalankan', 'tooltip' => 'Terpidana sedang menjalani hukuman sesuai putusan pengadilan.'],
        11 => ['label' => 'Selesai', 'tooltip' => 'Seluruh proses hukum telah selesai.'],
    ];

    // Status explainer
    $statusExplainer = [
        'open' => ['icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'color' => 'blue', 'text' => 'Kasus ini baru saja masuk dan sedang menunggu proses lebih lanjut dari penegak hukum.'],
        'investigation' => ['icon' => 'M21 21l-4.35-4.35M17 11A6 6 0 105 11a6 6 0 0012 0z', 'color' => 'yellow', 'text' => 'Penegak hukum sedang mengumpulkan bukti awal untuk menentukan apakah ada tindak pidana.'],
        'prosecution' => ['icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z', 'color' => 'blue', 'text' => 'Jaksa menyusun surat dakwaan dan mempersiapkan kasus untuk dibawa ke pengadilan.'],
        'trial' => ['icon' => 'M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3', 'color' => 'purple', 'text' => 'Kasus sedang disidangkan di pengadilan. Hakim memeriksa bukti dan keterangan saksi.'],
        'executed' => ['icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z', 'color' => 'orange', 'text' => 'Terpidana sedang menjalani hukuman sesuai putusan pengadilan.'],
        'closed' => ['icon' => 'M5 13l4 4L19 7', 'color' => 'green', 'text' => 'Seluruh proses hukum telah selesai. Kasus ditutup.'],
        'rejected' => ['icon' => 'M6 18L18 6M6 6l12 12', 'color' => 'red', 'text' => 'Kasus ini ditolak dan tidak dapat diproses lebih lanjut.'],
    ];
    $explainer = $statusExplainer[$currentKey] ?? null;
    $explainerColor = [
        'blue' => 'bg-blue-50 border-blue-200 text-blue-800',
        'yellow' => 'bg-yellow-50 border-yellow-200 text-yellow-800',
        'orange' => 'bg-orange-50 border-orange-200 text-orange-800',
        'purple' => 'bg-purple-50 border-purple-200 text-purple-800',
        'red' => 'bg-red-50 border-red-200 text-red-800',
        'green' => 'bg-green-50 border-green-200 text-green-800',
        'gray' => 'bg-gray-50 border-gray-200 text-gray-700',
    ];
@endphp

<div class="min-h-screen bg-slate-50">
    <div class="mx-auto max-w-6xl px-4 sm:px-6 py-6 space-y-4">

        {{-- ===== SLIM WARNINGS ===== --}}
        @if ($isStale)
            <div class="flex items-center gap-2 bg-red-50 border border-red-200 rounded-lg px-3 py-2 text-xs text-red-800">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" /></svg>
                <span>Tidak ada aktivitas <strong>{{ $daysSinceUpdate }} hari</strong>. Perbarui data atau status kasus.</span>
            </div>
        @endif
        @if ($case->is_public && $completePct < 70)
            <div class="flex items-center gap-2 bg-orange-50 border border-orange-200 rounded-lg px-3 py-2 text-xs text-orange-800">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 100 20A10 10 0 0012 2z" /></svg>
                <span>Sudah dipublikasikan, kelengkapan baru <strong>{{ $completePct }}%</strong>. Lengkapi data sebelum dilihat publik.</span>
            </div>
        @endif

        {{-- ===== MASTHEAD ===== --}}
        <header class="bg-white border border-slate-200 rounded-xl overflow-visible">
            {{-- identity + status --}}
            <div class="px-5 pt-5 flex items-start justify-between gap-4">
                <div class="min-w-0">
                    <p class="font-mono text-[11px] tracking-widest uppercase text-slate-400">{{ $case->case_number }}</p>
                    <h1 class="mt-1 text-lg sm:text-xl font-semibold text-slate-900 leading-snug break-words">{!! $case->title !!}</h1>
                </div>
                <div class="flex items-center gap-2 flex-shrink-0">
                    <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold
                        @if ($case->status_key === 'investigation') bg-yellow-100 text-yellow-800
                        @elseif($case->status_key === 'prosecution') bg-blue-100 text-blue-800
                        @elseif($case->status_key === 'trial') bg-purple-100 text-purple-800
                        @elseif($case->status_key === 'executed') bg-orange-100 text-orange-800
                        @elseif($case->status_key === 'closed') bg-slate-200 text-slate-700
                        @elseif($case->status_key === 'rejected') bg-red-100 text-red-800
                        @else bg-slate-100 text-slate-700 @endif">
                        {{ $case->status_name }}
                    </span>
                    <span class="px-2.5 py-1 rounded-full text-[11px] font-medium
                        {{ $case->is_public ? 'bg-green-100 text-green-800' : 'bg-slate-100 text-slate-500' }}">
                        {{ $case->is_public ? 'Publik' : 'Draft' }}
                    </span>
                </div>
            </div>

            {{-- SIGNATURE: progress bar (sama dengan public case detail) --}}
            <div class="px-5 py-4 mt-4 border-y border-slate-100 bg-slate-50/60">
                @if ($case->status_key === 'rejected')
                    <div class="flex items-center gap-2 text-xs text-red-700">
                        <span class="w-2.5 h-2.5 rounded-full bg-red-500 ring-4 ring-red-100"></span>
                        <span class="font-semibold tracking-wide uppercase">Kasus Ditolak</span>
                        <span class="text-red-400">— status akhir, tidak ada lanjutan.</span>
                    </div>
                @else
                    {{-- Ringkasan progres --}}
                    @php
                        $totalSteps = 11;
                        $currentLabel = $visibleSteps[$currentStep]['label'] ?? null;
                    @endphp
                    @if ($currentLabel)
                        <p class="text-xs text-slate-500 mb-4">
                            Kasus ini berada pada tahap <strong class="text-slate-700">{{ $currentLabel }}</strong>
                            (tahap {{ $currentStep }} dari {{ $totalSteps }}).
                        </p>
                    @endif

                    {{-- Mobile --}}
                    <div class="flex md:hidden flex-wrap gap-2">
                        @foreach ($visibleSteps as $step => $info)
                            @php $done = $currentStep >= $step; $active = $currentStep === $step; @endphp
                            <span class="px-3 py-1 rounded-full text-xs font-semibold
                                {{ $active ? 'bg-green-600 text-white' : ($done ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-400') }}">
                                {{ $info['label'] }}
                            </span>
                        @endforeach
                    </div>

                    {{-- Desktop with tooltips --}}
                    <div class="hidden md:flex items-start relative">
                        <div class="absolute top-4 left-0 right-0 h-0.5 bg-slate-200 z-0"></div>
                        @foreach ($visibleSteps as $step => $info)
                            @php $done = $currentStep >= $step; $active = $currentStep === $step; @endphp
                            <div class="flex-1 flex flex-col items-center relative z-10 group/step">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center border-2 transition-all cursor-default
                                    {{ $active ? 'bg-green-600 border-green-600 shadow-lg shadow-green-200 ring-4 ring-green-100' : ($done ? 'bg-green-500 border-green-500' : 'bg-white border-slate-300') }}">
                                    @if ($done)
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                        </svg>
                                    @else
                                        <span class="w-2 h-2 rounded-full bg-slate-300"></span>
                                    @endif
                                </div>
                                <p class="mt-2 text-center text-[10px] leading-tight font-medium max-w-[70px]
                                    {{ $active ? 'text-green-700 font-bold' : ($done ? 'text-green-600' : 'text-slate-400') }}">
                                    {{ $info['label'] }}
                                </p>
                                <div class="absolute bottom-full mb-3 left-1/2 -translate-x-1/2 w-48 bg-slate-900 text-white text-xs rounded-lg px-3 py-2 leading-snug
                                    opacity-0 group-hover/step:opacity-100 pointer-events-none transition-opacity duration-200 z-20 shadow-xl text-center">
                                    {{ $info['tooltip'] }}
                                    <div class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-slate-900"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Completeness bar --}}
                    <div class="flex items-center gap-2 mt-4 pt-3 border-t border-slate-200">
                        <span class="text-[10px] font-mono uppercase tracking-widest text-slate-400">Kelengkapan</span>
                        <div class="flex-1 h-1.5 rounded-full bg-slate-200 overflow-hidden">
                            <div class="h-full rounded-full transition-all duration-500
                                {{ $completePct >= 80 ? 'bg-green-500' : ($completePct >= 50 ? 'bg-yellow-400' : 'bg-red-400') }}"
                                style="width: {{ $completePct }}%"></div>
                        </div>
                        <span class="text-[11px] font-bold tabular-nums {{ $completePct >= 80 ? 'text-green-600' : ($completePct >= 50 ? 'text-yellow-600' : 'text-red-500') }}">{{ $completePct }}%</span>
                    </div>
                @endif
            </div>

            {{-- facts strip --}}
            <div class="px-5 py-3 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-x-4 gap-y-2.5 text-xs">
                <div><p class="font-mono text-[9px] tracking-widest uppercase text-slate-400 mb-0.5">Tanggal</p><p class="text-slate-700 truncate">{{ $case->event_date ?? '—' }}</p></div>
                <div class="col-span-2"><p class="font-mono text-[9px] tracking-widest uppercase text-slate-400 mb-0.5">Lokasi</p><p class="text-slate-700 truncate">{{ implode(', ', array_filter([$location['village'] ?? null, $location['district'] ?? null, $location['province'] ?? null])) ?: '—' }}</p></div>
                <div><p class="font-mono text-[9px] tracking-widest uppercase text-slate-400 mb-0.5">Pelapor</p><p class="text-slate-700 truncate">{{ $case->pelapor ?? '—' }}</p></div>
                <div><p class="font-mono text-[9px] tracking-widest uppercase text-slate-400 mb-0.5">Terlapor</p><p class="text-slate-700 truncate">{{ $case->terlapor ?? '—' }}</p></div>
                <div><p class="font-mono text-[9px] tracking-widest uppercase text-slate-400 mb-0.5">Arsip</p><p class="text-slate-700"><span class="tabular-nums">{{ $docCount }}</span> dok · <span class="tabular-nums">{{ $actorCount }}</span> aktor · <span class="tabular-nums">{{ $buktiCount }}</span> bukti</p></div>
            </div>

            {{-- actions --}}
            <div class="px-5 py-3 border-t border-slate-100 flex flex-wrap items-center justify-end gap-2">
                @can('case.publish')
                    <button wire:click="publishCases"
                        class="px-3 py-1.5 rounded-lg text-xs font-medium text-white transition-colors {{ $case->is_public ? 'bg-red-600 hover:bg-red-700' : 'bg-green-600 hover:bg-green-700' }}">
                        {{ $case->is_public ? 'Batalkan Publikasi' : 'Publikasikan' }}
                    </button>
                @endcan
                @can('case.change-status')
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="px-3 py-1.5 rounded-lg text-xs font-medium text-white bg-yellow-500 hover:bg-yellow-600 transition-colors inline-flex items-center gap-1">
                            Ubah Status <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                        </button>
                        <div x-show="open" x-cloak @click.away="open = false" class="absolute right-0 mt-1 w-48 bg-white border border-slate-200 rounded-lg shadow-lg z-50 overflow-hidden">
                            @foreach ($availableStatuses as $name => $label)
                                <button wire:click="changeStatus('{{ $name }}')" @click="open = false"
                                    onclick="return confirm('Ubah status menjadi {{ $label }}?')"
                                    class="block w-full text-left px-3 py-2 text-xs hover:bg-slate-50 transition-colors border-b border-slate-50 last:border-0 {{ $name === $case->status_key ? 'font-semibold text-blue-600' : 'text-slate-700' }}">
                                    {{ $label }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endcan
                @can('case.edit')
                    <a href="{{ route('admin.cases.edit', $case->id) }}" class="px-3 py-1.5 rounded-lg text-xs font-medium text-white bg-slate-900 hover:bg-slate-700 transition-colors">Edit</a>
                @endcan
            </div>
        </header>

        {{-- ===== TABS ===== --}}
        <nav class="bg-white border border-slate-200 rounded-xl px-2 flex gap-1 overflow-x-auto">
            @foreach (['overview' => 'Ringkasan', 'handling' => 'Penanganan', 'timeline' => 'Linimasa'] as $tab => $label)
                <button wire:click="setTab('{{ $tab }}')"
                    class="relative py-2.5 px-3 text-xs font-medium whitespace-nowrap transition-colors
                        {{ $activeTab === $tab ? 'text-blue-600' : 'text-slate-500 hover:text-slate-800' }}">
                    {{ $label }}
                    @if ($activeTab === $tab)<span class="absolute inset-x-2 -bottom-px h-0.5 bg-blue-600 rounded-full"></span>@endif
                </button>
            @endforeach
        </nav>

        {{-- ===== CONTENT ===== --}}
        <div class="bg-white border border-slate-200 rounded-xl p-4 sm:p-5">

            {{-- OVERVIEW --}}
            @if ($activeTab === 'overview')
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                    {{-- LEFT: narrative --}}
                    <div class="lg:col-span-2 divide-y divide-slate-100">
                        @php
                            $desc = method_exists($case, 'getTranslation')
                                ? ($case->getTranslation('description', 'id', false) ?: $case->getTranslation('description', 'en', false))
                                : $case->description;
                            $perkembangan = $case->perkembangan ?? '';
                            if ($perkembangan && preg_match('/^\[/', $perkembangan)) {
                                $decoded = json_decode($perkembangan, true);
                                if (is_array($decoded)) $perkembangan = collect($decoded)->pluck('notes')->filter()->implode("\n");
                            }
                            $pembelajaran = method_exists($case, 'getTranslation')
                                ? ($case->getTranslation('pembelajaran', 'id', false) ?: $case->getTranslation('pembelajaran', 'en', false))
                                : $case->pembelajaran;
                            $dugaan = method_exists($case, 'getTranslation')
                                ? ($case->getTranslation('dugaan_permasalahan', 'id', false) ?: $case->getTranslation('dugaan_permasalahan', 'en', false))
                                : $case->dugaan_permasalahan;
                            $statusNarasi = method_exists($case, 'getTranslation')
                                ? ($case->getTranslation('status_narasi', 'id', false) ?: $case->getTranslation('status_narasi', 'en', false))
                                : $case->status_narasi;
                        @endphp

                        @foreach ([
                            'Kronologi' => $desc,
                            'Perkembangan' => $perkembangan,
                            'Dugaan Permasalahan' => $dugaan,
                            'Lesson Learning' => $pembelajaran,
                            'Status' => $statusNarasi,
                        ] as $label => $body)
                            <section class="py-4 first:pt-0">
                                <h2 class="text-[10px] font-semibold tracking-widest uppercase text-slate-400 mb-2">{{ $label }}</h2>
                                @if ($body)
                                    <div class="prose prose-sm prose-slate max-w-none text-slate-700">{!! $body !!}</div>
                                @else
                                    <p class="text-xs text-slate-300 italic">Belum diisi.</p>
                                @endif
                            </section>
                        @endforeach
                    </div>

                    {{-- RIGHT: ledger --}}
                    <div class="space-y-4">

                        {{-- kelengkapan chips --}}
                        <div class="border border-slate-200 rounded-xl p-4">
                            <h3 class="text-[10px] font-semibold tracking-widest uppercase text-slate-400 mb-3">Kelengkapan</h3>
                            <div class="flex flex-wrap gap-1.5">
                                @foreach ($completeness as $field => $filled)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-medium
                                        {{ $filled ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-500' }}">
                                        @if ($filled)
                                            <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                        @else
                                            <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                        @endif
                                        {{ $field }}
                                    </span>
                                @endforeach
                            </div>
                        </div>

                        {{-- pihak --}}
                        <div class="border border-slate-200 rounded-xl p-4 space-y-3 text-xs">
                            <h3 class="text-[10px] font-semibold tracking-widest uppercase text-slate-400">Pihak Terlibat</h3>
                            <div class="space-y-2">
                                <div><p class="text-slate-400">Pelapor</p><p class="text-slate-800 break-words">{{ $case->pelapor ?? '—' }}</p></div>
                                <div><p class="text-slate-400">Terlapor</p><p class="text-slate-800 break-words">{{ $case->terlapor ?? '—' }}</p></div>
                                <div><p class="text-slate-400">Instansi Terkait</p><p class="text-slate-800 break-words">{{ $case->instansi ?? '—' }}</p></div>
                                <div>
                                    <p class="text-slate-400 mb-1">Pelaku</p>
                                    @if ($actors->isEmpty())
                                        <p class="text-slate-300 italic">Belum ada pelaku.</p>
                                    @else
                                        <ul class="space-y-1">
                                            @foreach ($actors as $actor)
                                                <li class="flex items-start gap-1.5"><span class="w-1 h-1 rounded-full bg-slate-400 flex-shrink-0 mt-1.5"></span><span class="text-slate-700 break-words">{{ ucfirst($actor->type) }} — {{ $actor->name }}</span></li>
                                            @endforeach
                                        </ul>
                                    @endif
                                    @can('case.actor.manage')
                                        <button class="mt-2 px-2.5 py-1 bg-slate-900 text-white rounded-md text-[10px] font-medium hover:bg-slate-700 transition-colors" x-on:click="$dispatch('open-actor-modal', { caseId: {{ $case->id }} })">+ Tambah Pelaku</button>
                                    @endcan
                                </div>
                            </div>
                        </div>

                        {{-- bukti --}}
                        <div class="border border-slate-200 rounded-xl p-4">
                            <h3 class="text-[10px] font-semibold tracking-widest uppercase text-slate-400 mb-3">Bukti <span class="ml-1 text-slate-300 tabular-nums">{{ $buktiCount }}</span></h3>
                            @if ($buktiCount)
                                <div class="flex flex-wrap gap-2">
                                    @foreach ($case->bukti as $i => $ev)
                                        @php
                                            $path = 'storage/' . $ev;
                                            $ext = strtolower(pathinfo($ev, PATHINFO_EXTENSION));
                                            $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                                            $isPdf = $ext === 'pdf';
                                            $label = $isImage ? 'Gambar ' . ($i + 1) : strtoupper($ext) . ' File';
                                            $icon = $isImage ? '🖼️' : ($isPdf ? '📄' : '📎');
                                        @endphp
                                        @if ($isImage)
                                            <a href="{{ asset($path) }}" target="_blank" class="group relative w-12 h-12 rounded-lg overflow-hidden border border-slate-200" title="{{ $label }}">
                                                <img src="{{ asset($path) }}" class="w-full h-full object-cover group-hover:scale-110 transition duration-200">
                                            </a>
                                        @else
                                            <a href="{{ asset($path) }}" target="{{ $isPdf ? '_blank' : '_self' }}" {{ !$isPdf ? 'download' : '' }} class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg border border-slate-200 bg-slate-50 hover:bg-slate-100 transition text-[11px] text-slate-700">
                                                <span>{{ $icon }}</span><span>{{ $label }}</span>
                                            </a>
                                        @endif
                                    @endforeach
                                </div>
                            @else
                                <p class="text-xs text-slate-300 italic">Tidak ada bukti dilampirkan.</p>
                            @endif
                        </div>

                        {{-- catatan internal --}}
                        <div class="border border-amber-200 bg-amber-50/60 rounded-xl p-4 text-xs">
                            <h3 class="text-[10px] font-semibold tracking-widest uppercase text-amber-700 mb-2">Catatan Internal</h3>
                            @if ($case->internal_notes ?? false)
                                <p class="text-slate-700 leading-relaxed break-words">{{ $case->internal_notes }}</p>
                            @else
                                <p class="text-amber-600 italic">Belum ada catatan.</p>
                            @endif
                            @can('case.edit')
                                <button class="mt-2 text-[10px] text-amber-700 underline hover:text-amber-900" x-on:click="$dispatch('open-notes-modal', { caseId: {{ $case->id }} })">+ Tambah / Edit Catatan</button>
                            @endcan
                        </div>
                    </div>
                </div>
            @endif

            {{-- HANDLING --}}
            @if ($activeTab === 'handling')
                <div class="space-y-5">
                    @can('case.task.create')
                        <section><livewire:cases.case-actions :case-id="$case->id" /></section>
                    @endcan
                    <section>
                        <h2 class="text-[10px] font-semibold tracking-widest uppercase text-slate-400 mb-3">Sumber & Diskusi</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <h3 class="text-xs font-medium text-slate-700">Dokumen <span class="ml-1 text-slate-400 tabular-nums">{{ $docCount }}</span></h3>
                                    @can('case.document.upload')
                                        <button class="px-2.5 py-1 bg-slate-900 text-white rounded-md text-[10px] font-medium hover:bg-slate-700 transition-colors" x-on:click="$dispatch('open-upload-document-modal', { caseId: {{ $case->id }} })">+ Unggah</button>
                                    @endcan
                                </div>
                                <div class="space-y-2">
                                    @forelse ($documents as $doc)
                                        <div class="p-2.5 border border-slate-200 rounded-lg flex items-center justify-between gap-3">
                                            <div class="min-w-0">
                                                <p class="text-xs font-medium truncate">{{ $doc->title ?? 'Dokumen' }}</p>
                                                <p class="text-[10px] text-slate-400">{{ $doc->mime }}</p>
                                            </div>
                                            <div class="flex gap-2 flex-shrink-0">
                                                @can('case.document.view')
                                                    <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank" class="text-[11px] text-blue-600 hover:underline">Buka</a>
                                                @endcan
                                                <button class="text-[11px] text-slate-500 hover:underline" wire:click="$dispatch('open-edit-document-modal', { docId: {{ $doc->id }} })">Edit</button>
                                            </div>
                                        </div>
                                    @empty
                                        <p class="text-xs text-slate-300 italic">Belum ada dokumen.</p>
                                    @endforelse
                                </div>
                            </div>
                            <div>
                                <h3 class="text-xs font-medium text-slate-700 mb-2">Diskusi Tim</h3>
                                @livewire('cases.case-discussion', ['caseId' => $case->id])
                            </div>
                        </div>
                    </section>
                </div>
            @endif

            {{-- TIMELINE --}}
            @if ($activeTab === 'timeline')
                <div class="space-y-4">
                    <div class="flex items-center justify-between gap-3">
                        <h2 class="text-[10px] font-semibold tracking-widest uppercase text-slate-400">Log Aktivitas & Linimasa</h2>
                        @can('case.timeline.add')
                            <button class="px-3 py-1.5 bg-slate-900 text-white rounded-lg text-xs font-medium hover:bg-slate-700 transition-colors" x-on:click="$dispatch('open-upload-timeline-modal', { caseId: {{ $case->id }} })">+ Tambah Timeline</button>
                        @endcan
                    </div>
                    <div class="border-l-2 border-slate-200 pl-4 sm:pl-5 space-y-4">
                        @forelse ($timelines as $log)
                            <div class="relative">
                                <span class="absolute -left-[19px] sm:-left-[23px] top-1.5 w-2.5 h-2.5 rounded-full bg-slate-400 border-2 border-white ring-2 ring-slate-200"></span>
                                <div class="bg-slate-50 border border-slate-100 rounded-lg p-3">
                                    <p class="text-sm text-slate-800 leading-relaxed break-words">{{ $log->notes }}</p>
                                    <div class="flex items-center justify-between gap-1 mt-2">
                                        <p class="text-[10px] font-mono text-slate-400">{{ \Carbon\Carbon::parse($log->created_at)->format('d M Y, H:i') }}</p>
                                        <button class="text-[10px] text-slate-400 hover:text-slate-700 hover:underline" wire:click="$dispatch('open-edit-timeline-modal', { timelineId: {{ $log->id }} })">Edit</button>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <p class="text-xs text-slate-300 italic pl-2">Belum ada entri linimasa.</p>
                        @endforelse
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- MODALS --}}
    @livewire('cases.task-requirement-case')
    @livewire('cases.upload-document-case')
    @livewire('cases.actor-cases')
    @livewire('cases.case-timeline')

    {{-- SUCCESS TOAST --}}
    @if (session('success'))
        <div x-data="{ show: true }" x-show="show" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-4" x-init="setTimeout(() => show = false, 3000)" class="fixed bottom-4 right-4 sm:bottom-6 sm:right-6 bg-green-600 text-white px-4 py-2.5 rounded-xl shadow-lg text-sm font-medium flex items-center gap-2 max-w-xs">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
            <span class="break-words">{{ session('success') }}</span>
        </div>
    @endif
</div>