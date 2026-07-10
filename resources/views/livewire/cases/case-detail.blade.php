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
        <header class="bg-white border border-slate-200 rounded-xl overflow-hidden">
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

            {{-- SIGNATURE: lifecycle rail --}}
            <div class="px-5 py-4 mt-4 border-y border-slate-100 bg-slate-50/60">
                @if ($case->status_key === 'rejected')
                    <div class="flex items-center gap-2 text-xs text-red-700">
                        <span class="w-2.5 h-2.5 rounded-full bg-red-500 ring-4 ring-red-100"></span>
                        <span class="font-semibold tracking-wide uppercase">Kasus Ditolak</span>
                        <span class="text-red-400">— status akhir, tidak ada lanjutan.</span>
                    </div>
                @else
                    <div class="flex items-center">
                        @foreach ($legalFlow as $i => $key)
                            @php $done = is_int($currentIdx) && $i < $currentIdx; $current = $i === $currentIdx; @endphp
                            <div class="flex flex-col items-center gap-1.5 z-10">
                                <span class="w-2.5 h-2.5 rounded-full ring-4 ring-slate-50
                                    {{ $current ? 'bg-blue-600 ring-blue-100' : ($done ? 'bg-slate-900' : 'bg-slate-300') }}"></span>
                                <span class="text-[9px] font-medium tracking-wide hidden sm:block
                                    {{ $current ? 'text-slate-900' : ($done ? 'text-slate-500' : 'text-slate-300') }}">{{ $flowLabels[$key] }}</span>
                            </div>
                            @if (!$loop->last)
                                <div class="flex-1 h-px mx-1 {{ is_int($currentIdx) && $i < $currentIdx ? 'bg-slate-900' : 'bg-slate-200' }}"></div>
                            @endif
                        @endforeach
                        {{-- completeness, tucked at the end of the rail row --}}
                        <div class="flex items-center gap-2 pl-4 ml-2 border-l border-slate-200">
                            <div class="w-20 sm:w-28 h-1.5 rounded-full bg-slate-200 overflow-hidden">
                                <div class="h-full rounded-full transition-all duration-500
                                    {{ $completePct >= 80 ? 'bg-green-500' : ($completePct >= 50 ? 'bg-yellow-400' : 'bg-red-400') }}"
                                    style="width: {{ $completePct }}%"></div>
                            </div>
                            <span class="text-[11px] font-bold tabular-nums {{ $completePct >= 80 ? 'text-green-600' : ($completePct >= 50 ? 'text-yellow-600' : 'text-red-500') }}">{{ $completePct }}%</span>
                        </div>
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