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

<div>
    <div class="max-w-7xl mx-auto px-6 py-6 space-y-4">

        {{-- ===== SLIM WARNINGS ===== --}}
        @if ($isStale)
            <div class="flex items-center gap-2 border rounded-[10px] px-3 py-2 text-xs" style="background:var(--danger-soft);border-color:var(--danger);color:var(--danger)">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" /></svg>
                <span>Tidak ada aktivitas <strong>{{ $daysSinceUpdate }} hari</strong>. Perbarui data atau status kasus.</span>
            </div>
        @endif
        @if ($case->is_public && $completePct < 70)
            <div class="flex items-center gap-2 border rounded-[10px] px-3 py-2 text-xs" style="background:var(--warn-soft);border-color:var(--warn);color:var(--warn)">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 100 20A10 10 0 0012 2z" /></svg>
                <span>Sudah dipublikasikan, kelengkapan baru <strong>{{ $completePct }}%</strong>. Lengkapi data sebelum dilihat publik.</span>
            </div>
        @endif

        {{-- ===== MASTHEAD ===== --}}
        <header class="cms-rise relative z-30" style="animation-delay:.04s;background:var(--surface);border:1px solid var(--hairline);border-radius:14px">
            {{-- identity + status --}}
            <div class="cms-panel-head" style="padding:18px 22px;border-bottom:0">
                <div class="min-w-0">
                    <p class="font-mono-c text-[11px] tracking-widest uppercase text-[color:var(--muted)]">{{ $case->case_number }}</p>
                    <h1 class="mt-1 text-lg sm:text-xl font-semibold text-[color:var(--ink)] leading-snug break-words">{!! $case->title !!}</h1>
                </div>
                <div class="flex items-center gap-2 flex-shrink-0">
                    @php
                        $statusVariant = 'default';
                        if (in_array($case->status_key, ['investigation'])) $statusVariant = 'warn';
                        elseif (in_array($case->status_key, ['prosecution','trial'])) $statusVariant = 'info';
                        elseif (in_array($case->status_key, ['executed'])) $statusVariant = 'warn';
                        elseif (in_array($case->status_key, ['closed'])) $statusVariant = 'default';
                        elseif (in_array($case->status_key, ['rejected'])) $statusVariant = 'danger';
                        elseif (in_array($case->status_key, ['open'])) $statusVariant = 'danger';
                    @endphp
                    <x-internal.badge variant="{{ $statusVariant }}">{{ $case->status_name }}</x-internal.badge>
                    <x-internal.badge variant="{{ $case->is_public ? 'ok' : 'default' }}">{{ $case->is_public ? 'Publik' : 'Draft' }}</x-internal.badge>
                </div>
            </div>

            {{-- SIGNATURE: progress bar (sama dengan public case detail) --}}
            <div class="px-5 py-4 border-y border-[color:var(--hairline)] bg-[color:var(--paper)]">
                @php
                    $terminal = [
                        'rejected'  => ['label' => 'Kasus Ditolak',    'color' => 'danger', 'note' => '— status akhir, tidak ada lanjutan.'],
                        'converted' => ['label' => 'Kasus Dikonversi', 'color' => 'warn',   'note' => '— dialihkan ke kasus lain, bukan lanjutan proses.'],
                        'closed'    => ['label' => 'Kasus Ditutup',    'color' => 'ok',      'note' => '— proses hukum telah diselesaikan.'],
                    ][$case->status_key] ?? null;
                @endphp
                @if ($terminal)
                    <div class="flex items-center gap-2 text-xs" style="color:var(--{{ $terminal['color'] }})">
                        <span class="w-2.5 h-2.5 rounded-full" style="background:var(--{{ $terminal['color'] }});box-shadow:0 0 0 4px var(--{{ $terminal['color'] }}-soft)"></span>
                        <span class="font-semibold tracking-wide uppercase">{{ $terminal['label'] }}</span>
                        <span class="opacity-70">{{ $terminal['note'] }}</span>
                    </div>
                @else
                    {{-- Ringkasan progres --}}
                    @php
                        $totalSteps = 11;
                        $currentLabel = $visibleSteps[$currentStep]['label'] ?? null;
                    @endphp
                    @if ($currentLabel)
                        <p class="text-xs text-[color:var(--muted)] mb-4">
                            Kasus ini berada pada tahap <strong class="text-[color:var(--ink)]">{{ $currentLabel }}</strong>
                            (tahap {{ $currentStep }} dari {{ $totalSteps }}).
                        </p>
                    @endif

                    {{-- Mobile --}}
                    <div class="flex md:hidden flex-wrap gap-2">
                        @foreach ($visibleSteps as $step => $info)
                            @php $done = $currentStep >= $step; $active = $currentStep === $step; @endphp
                            <span class="px-3 py-1 rounded-full text-xs font-semibold"
                                style="{{ $active ? 'background:var(--leaf-deep);color:#fff' : ($done ? 'background:var(--paper-2);color:var(--leaf-deep)' : 'background:var(--surface);color:var(--muted-2)') }}">
                                {{ $info['label'] }}
                            </span>
                        @endforeach
                    </div>

                    {{-- Desktop with tooltips --}}
                    <div class="hidden md:flex items-start relative">
                        <div class="absolute top-4 left-0 right-0 h-0.5 z-0" style="background:var(--hairline)"></div>
                        @foreach ($visibleSteps as $step => $info)
                            @php $done = $currentStep >= $step; $active = $currentStep === $step; @endphp
                            <div class="flex-1 flex flex-col items-center relative z-10 group/step">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center border-2 transition-all cursor-default"
                                    style="{{ $active ? 'background:var(--leaf-deep);border-color:var(--leaf-deep);box-shadow:0 0 0 4px var(--leaf)' : ($done ? 'background:var(--leaf-deep);border-color:var(--leaf-deep)' : 'background:var(--surface);border-color:var(--hairline-2)') }}">
                                    @if ($done)
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                        </svg>
                                    @else
                                        <span class="w-2 h-2 rounded-full" style="background:var(--hairline-2)"></span>
                                    @endif
                                </div>
                                <p class="mt-2 text-center text-[10px] leading-tight font-medium max-w-[70px] {{ $active ? 'font-bold' : '' }}"
                                    style="color:{{ $active ? 'var(--leaf-deep)' : ($done ? 'var(--leaf-deep)' : 'var(--muted-2)') }}">
                                    {{ $info['label'] }}
                                </p>
                                <div class="absolute bottom-full mb-3 left-1/2 -translate-x-1/2 w-48 text-white text-xs rounded-lg px-3 py-2 leading-snug
                                    opacity-0 group-hover/step:opacity-100 pointer-events-none transition-opacity duration-200 z-20 text-center"
                                    style="background:var(--ink)">
                                    {{ $info['tooltip'] }}
                                    <div class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent" style="border-top-color:var(--ink)"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Completeness bar --}}
                    <div class="flex items-center gap-2 mt-4 pt-3 border-t border-[color:var(--hairline)]">
                        <span class="cms-eyebrow">Kelengkapan</span>
                        <div class="flex-1 h-1.5 rounded-full overflow-hidden" style="background:var(--paper-2)">
                            <div class="h-full rounded-full transition-all duration-500"
                                style="width: {{ $completePct }}%;background:{{ $completePct >= 80 ? 'var(--ok)' : ($completePct >= 50 ? 'var(--warn)' : 'var(--danger)') }}"></div>
                        </div>
                        <span class="text-[11px] font-bold tabular-nums font-mono-c" style="color:{{ $completePct >= 80 ? 'var(--ok)' : ($completePct >= 50 ? 'var(--warn)' : 'var(--danger)') }}">{{ $completePct }}%</span>
                    </div>
                @endif
            </div>

            {{-- facts strip --}}
            <div class="px-5 py-3 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-x-4 gap-y-2.5 text-xs">
                <div><p class="font-mono-c text-[9px] tracking-widest uppercase text-[color:var(--muted)] mb-0.5">Tanggal</p><p class="text-[color:var(--ink-2)] truncate">{{ $case->event_date ?? '—' }}</p></div>
                <div class="col-span-2"><p class="font-mono-c text-[9px] tracking-widest uppercase text-[color:var(--muted)] mb-0.5">Lokasi</p><p class="text-[color:var(--ink-2)] truncate">{{ implode(', ', array_filter([$location['village'] ?? null, $location['district'] ?? null, $location['province'] ?? null])) ?: '—' }}</p></div>
                <div><p class="font-mono-c text-[9px] tracking-widest uppercase text-[color:var(--muted)] mb-0.5">Pelapor</p><p class="text-[color:var(--ink-2)] truncate">{{ $case->pelapor ?? '—' }}</p></div>
                <div><p class="font-mono-c text-[9px] tracking-widest uppercase text-[color:var(--muted)] mb-0.5">Terlapor</p><p class="text-[color:var(--ink-2)] truncate">{{ $case->terlapor ?? '—' }}</p></div>
                <div><p class="font-mono-c text-[9px] tracking-widest uppercase text-[color:var(--muted)] mb-0.5">Arsip</p><p class="text-[color:var(--ink-2)]"><span class="tabular-nums">{{ $docCount }}</span> dok · <span class="tabular-nums">{{ $actorCount }}</span> aktor · <span class="tabular-nums">{{ $buktiCount }}</span> bukti</p></div>
            </div>

            {{-- actions --}}
            <div class="px-5 py-3 border-t border-[color:var(--hairline)] flex flex-wrap items-center justify-end gap-2">
                @can('case.publish')
                    <button wire:click="publishCases"
                        class="cms-btn {{ $case->is_public ? 'cms-btn-danger' : 'cms-btn-leaf' }}">
                        @if($case->is_public)
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L18.364 5.636M5.636 18.364l12.728-12.728" /></svg>
                        @else
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                        @endif
                        {{ $case->is_public ? 'Batalkan Publikasi' : 'Publikasikan' }}
                    </button>
                @endcan
                @can('case.change-status')
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="cms-btn cms-btn-ghost inline-flex items-center gap-1">
                            Ubah Status <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                        </button>
                        <div x-show="open" x-cloak @click.away="open = false" class="absolute right-0 mt-1 w-48 rounded-[10px] z-[9999]" style="background:var(--surface);border:1px solid var(--hairline);box-shadow:0 8px 24px rgba(0,0,0,.08)">
                            @foreach ($availableStatuses as $name => $label)
                                <button wire:click="changeStatus('{{ $name }}')" @click="open = false"
                                    onclick="return confirm('Ubah status menjadi {{ $label }}?')"
                                    class="block w-full text-left px-3 py-2 text-xs transition-colors border-b last:border-0 {{ $name === $case->status_key ? 'font-semibold' : '' }}"
                                    style="border-color:var(--hairline);{{ $name === $case->status_key ? 'color:var(--leaf-deep)' : 'color:var(--ink-2)' }}">
                                    {{ $label }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endcan
                @can('case.edit')
                    <a href="{{ route('admin.cases.edit', $case->id) }}" class="cms-btn cms-btn-primary">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                        Edit
                    </a>
                @endcan
            </div>
        </header>

        {{-- ===== TABS ===== --}}
        <nav class="cms-panel px-2 flex gap-1 overflow-x-auto">
            @foreach (['overview' => 'Ringkasan', 'handling' => 'Penanganan', 'timeline' => 'Linimasa'] as $tab => $label)
                <button wire:click="setTab('{{ $tab }}')"
                    class="relative py-2.5 px-3 text-xs font-medium whitespace-nowrap transition-colors"
                    style="color:{{ $activeTab === $tab ? 'var(--leaf-deep)' : 'var(--muted)' }}">
                    {{ $label }}
                    @if ($activeTab === $tab)<span class="absolute inset-x-2 -bottom-px h-0.5 rounded-full" style="background:var(--leaf-deep)"></span>@endif
                </button>
            @endforeach
        </nav>

        {{-- ===== CONTENT ===== --}}
        <div class="cms-panel cms-rise" style="padding:18px 22px;animation-delay:.10s">

            {{-- OVERVIEW --}}
            @if ($activeTab === 'overview')
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

                    {{-- LEFT: narrative --}}
                    <div class="lg:col-span-2 divide-y divide-[color:var(--hairline)]">
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
                                <h2 class="cms-eyebrow mb-2">{{ $label }}</h2>
                                @if ($body)
                                    <div class="prose prose-sm max-w-none text-[color:var(--ink-2)]">{!! $body !!}</div>
                                @else
                                    <p class="text-xs italic" style="color:var(--muted-2)">Belum diisi.</p>
                                @endif
                            </section>
                        @endforeach
                    </div>

                    {{-- RIGHT: ledger --}}
                    <div class="space-y-4">

                        {{-- kelengkapan chips --}}
                        <div class="border border-[color:var(--hairline)] rounded-[12px] p-4">
                            <h3 class="cms-eyebrow mb-3">Kelengkapan</h3>
                            <div class="flex flex-wrap gap-1.5">
                                @foreach ($completeness as $field => $filled)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-medium border"
                                        style="{{ $filled ? 'background:var(--ok-soft);color:var(--leaf-deep);border-color:var(--ok)' : 'background:var(--danger-soft);color:var(--danger);border-color:var(--danger)' }}">
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
                        <div class="border border-[color:var(--hairline)] rounded-[12px] p-4 space-y-3 text-xs">
                            <h3 class="cms-eyebrow">Pihak Terlibat</h3>
                            <div class="space-y-2">
                                <div><p class="text-[color:var(--muted)]">Pelapor</p><p class="text-[color:var(--ink)] break-words">{{ $case->pelapor ?? '—' }}</p></div>
                                <div><p class="text-[color:var(--muted)]">Terlapor</p><p class="text-[color:var(--ink)] break-words">{{ $case->terlapor ?? '—' }}</p></div>
                                <div><p class="text-[color:var(--muted)]">Instansi Terkait</p><p class="text-[color:var(--ink)] break-words">{{ $case->instansi ?? '—' }}</p></div>
                                <div>
                                    <p class="text-[color:var(--muted)] mb-1">Pelaku</p>
                                    @if ($actors->isEmpty())
                                        <p class="italic" style="color:var(--muted-2)">Belum ada pelaku.</p>
                                    @else
                                        <ul class="space-y-1">
                                            @foreach ($actors as $actor)
                                                <li class="flex items-start gap-1.5"><span class="w-1 h-1 rounded-full flex-shrink-0 mt-1.5" style="background:var(--muted)"></span><span class="text-[color:var(--ink-2)] break-words">{{ ucfirst($actor->type) }} — {{ $actor->name }}</span></li>
                                            @endforeach
                                        </ul>
                                    @endif
                                    @can('case.actor.manage')
                                        <button class="cms-btn cms-btn-ghost mt-2" x-on:click="$dispatch('open-actor-modal', { caseId: {{ $case->id }} })">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                                            Tambah Pelaku
                                        </button>
                                    @endcan
                                </div>
                            </div>
                        </div>

                        {{-- bukti --}}
                        <div class="border border-[color:var(--hairline)] rounded-[12px] p-4">
                            <h3 class="cms-eyebrow mb-3">Bukti <span class="ml-1 tabular-nums" style="color:var(--muted-2)">{{ $buktiCount }}</span></h3>
                            @if ($buktiCount)
                                <div class="flex flex-wrap gap-2">
                                    @foreach ($case->bukti as $i => $ev)
                                        @php
                                            $path = 'storage/' . $ev;
                                            $ext = strtolower(pathinfo($ev, PATHINFO_EXTENSION));
                                            $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                                            $isPdf = $ext === 'pdf';
                                            $label = $isImage ? 'Gambar ' . ($i + 1) : strtoupper($ext) . ' File';
                                        @endphp
                                        @if ($isImage)
                                            <a href="{{ asset($path) }}" target="_blank" class="group relative w-12 h-12 rounded-lg overflow-hidden border border-[color:var(--hairline)]" title="{{ $label }}">
                                                <img src="{{ asset($path) }}" class="w-full h-full object-cover group-hover:scale-110 transition duration-200">
                                            </a>
                                        @else
                                            <a href="{{ asset($path) }}" target="{{ $isPdf ? '_blank' : '_self' }}" {{ !$isPdf ? 'download' : '' }} class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg border text-[11px] transition" style="border-color:var(--hairline);background:var(--paper);color:var(--ink-2)">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.415a6 6 0 108.486 8.486L20.5 13" /></svg>
                                                <span>{{ $label }}</span>
                                            </a>
                                        @endif
                                    @endforeach
                                </div>
                            @else
                                <p class="text-xs italic" style="color:var(--muted-2)">Tidak ada bukti dilampirkan.</p>
                            @endif
                        </div>

                        {{-- catatan internal --}}
                        <div class="rounded-[12px] p-4 text-xs border" style="background:var(--warn-soft);border-color:var(--warn)">
                            <h3 class="cms-eyebrow mb-2" style="color:var(--brand)">Catatan Internal</h3>
                            @if ($case->internal_notes ?? false)
                                <p class="text-[color:var(--ink-2)] leading-relaxed break-words">{{ $case->internal_notes }}</p>
                            @else
                                <p class="italic" style="color:var(--brand)">Belum ada catatan.</p>
                            @endif
                            @can('case.edit')
                                <button class="cms-btn cms-btn-ghost mt-2" style="color:var(--brand);border-color:var(--warn)" x-on:click="$dispatch('open-notes-modal', { caseId: {{ $case->id }} })">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                                    Tambah / Edit Catatan
                                </button>
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
                        <h2 class="cms-eyebrow mb-3">Sumber & Diskusi</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <h3 class="text-xs font-medium text-[color:var(--ink)]">Dokumen <span class="ml-1 tabular-nums" style="color:var(--muted)">{{ $docCount }}</span></h3>
                                    @can('case.document.upload')
                                        <button class="cms-btn cms-btn-ghost" x-on:click="$dispatch('open-upload-document-modal', { caseId: {{ $case->id }} })">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                                            Unggah
                                        </button>
                                    @endcan
                                </div>
                                <div class="space-y-2">
                                    @forelse ($documents as $doc)
                                        <div class="p-2.5 border border-[color:var(--hairline)] rounded-[10px] flex items-center justify-between gap-3" style="background:var(--paper)">
                                            <div class="min-w-0">
                                                <p class="text-xs font-medium truncate text-[color:var(--ink)]">{{ $doc->title ?? 'Dokumen' }}</p>
                                                <p class="text-[10px] font-mono-c" style="color:var(--muted)">{{ $doc->mime }}</p>
                                            </div>
                                            <div class="flex gap-2 flex-shrink-0">
                                                @can('case.document.view')
                                                    <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank" class="text-[11px] hover:underline" style="color:var(--leaf-deep)">Buka</a>
                                                @endcan
                                                <button class="text-[11px] hover:underline" style="color:var(--muted)" wire:click="$dispatch('open-edit-document-modal', { docId: {{ $doc->id }} })">Edit</button>
                                            </div>
                                        </div>
                                    @empty
                                        <p class="text-xs italic" style="color:var(--muted-2)">Belum ada dokumen.</p>
                                    @endforelse
                                </div>
                            </div>
                            <div>
                                <h3 class="text-xs font-medium text-[color:var(--ink)] mb-2">Diskusi Tim</h3>
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
                        <h2 class="cms-eyebrow">Log Aktivitas & Linimasa</h2>
                        @can('case.timeline.add')
                            <button class="cms-btn cms-btn-leaf" x-on:click="$dispatch('open-upload-timeline-modal', { caseId: {{ $case->id }} })">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                                Tambah Timeline
                            </button>
                        @endcan
                    </div>
                    <div class="border-l-2 pl-4 sm:pl-5 space-y-4" style="border-color:var(--hairline)">
                        @forelse ($timelines as $log)
                            <div class="relative">
                                <span class="absolute -left-[19px] sm:-left-[23px] top-1.5 w-2.5 h-2.5 rounded-full border-2" style="background:var(--leaf-deep);border-color:var(--surface);box-shadow:0 0 0 2px var(--hairline)"></span>
                                <div class="border rounded-[10px] p-3" style="background:var(--paper);border-color:var(--hairline)">
                                    <p class="text-sm text-[color:var(--ink)] leading-relaxed break-words">{{ $log->notes }}</p>
                                    <div class="flex items-center justify-between gap-1 mt-2">
                                        <p class="text-[10px] font-mono-c" style="color:var(--muted)">{{ \Carbon\Carbon::parse($log->created_at)->format('d M Y, H:i') }}</p>
                                        <button class="text-[10px] hover:underline" style="color:var(--muted)" wire:click="$dispatch('open-edit-timeline-modal', { timelineId: {{ $log->id }} })">Edit</button>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <p class="text-xs italic pl-2" style="color:var(--muted-2)">Belum ada entri linimasa.</p>
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
        <div x-data="{ show: true }" x-show="show" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-4" x-init="setTimeout(() => show = false, 3000)" class="fixed bottom-4 right-4 sm:bottom-6 sm:right-6 px-4 py-2.5 rounded-xl text-sm font-medium flex items-center gap-2 max-w-xs" style="background:var(--ok);color:#fff;box-shadow:0 8px 24px rgba(0,0,0,.12)">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
            <span class="break-words">{{ session('success') }}</span>
        </div>
    @endif
</div>