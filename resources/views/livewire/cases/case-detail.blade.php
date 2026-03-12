<div class="bg-gray-100 min-h-screen">
    <div class="px-4 sm:px-6 lg:mx-10 py-6">

        @php
        $completeness = [
        'Deskripsi' => !empty($case->description),
        'Perkembangan' => !empty($case->perkembangan),
        'Pembelajaran' => !empty($case->pembelajaran),
        'Bukti' => !empty($case->bukti) && count($case->bukti) > 0,
        'Pelapor' => !empty($case->pelapor),
        'Pelaku' => $actors->isNotEmpty(),
        'Lokasi' => !empty($location['province']),
        ];
        $completeCount = collect($completeness)->filter()->count();
        $totalCount = count($completeness);
        $completePct = round(($completeCount / $totalCount) * 100);
        $daysSinceUpdate = $case->updated_at ? now()->diffInDays($case->updated_at) : null;
        $isStale = $daysSinceUpdate !== null && $daysSinceUpdate >= 30;
        $docCount = isset($documents) ? count($documents) : 0;
        $actorCount = $actors->count();
        @endphp

        {{-- WARNING: Tidak aktif --}}
        @if($isStale)
        <div
            class="flex items-start gap-3 bg-red-50 border border-red-200 rounded-xl px-4 py-3 mb-4 text-sm text-red-800">
            <svg class="w-4 h-4 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
            </svg>
            <span><strong>Perhatian:</strong> Tidak ada aktivitas selama <strong>{{ $daysSinceUpdate }} hari</strong>.
                Segera perbarui data atau status kasus.</span>
        </div>
        @endif

        {{-- WARNING: Publik tapi belum lengkap --}}
        @if($case->is_public && $completePct < 70) <div
            class="flex items-start gap-3 bg-orange-50 border border-orange-200 rounded-xl px-4 py-3 mb-4 text-sm text-orange-800">
            <svg class="w-4 h-4 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 100 20A10 10 0 0012 2z" />
            </svg>
            <span><strong>Kasus ini sudah dipublikasikan</strong> namun kelengkapan data baru <strong>{{ $completePct
                    }}%</strong>. Lengkapi data sebelum dilihat publik.</span>
    </div>
    @endif

    {{-- ================= HEADER ================= --}}
    <div class="bg-white rounded-xl shadow p-4 sm:p-6 mb-4">

        {{-- Mobile: stack, Desktop: side by side --}}
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-4">

            {{-- LEFT --}}
            <div class="flex-1 min-w-0">
                <h1 class="text-xl sm:text-2xl font-bold text-gray-900 break-words leading-snug">
                    {!! $case->title !!}
                </h1>
                <p class="text-sm text-gray-400 mt-1 mb-3">{{ $case->case_number }}</p>

                {{-- Badges --}}
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="px-3 py-1 rounded-full text-xs font-medium
                            @if($case->status_key === 'investigation') bg-yellow-100 text-yellow-800
                            @elseif($case->status_key === 'prosecution') bg-blue-100 text-blue-800
                            @elseif($case->status_key === 'trial') bg-purple-100 text-purple-800
                            @elseif($case->status_key === 'executed') bg-orange-100 text-orange-800
                            @elseif($case->status_key === 'closed') bg-gray-200 text-gray-700
                            @elseif($case->status_key === 'rejected') bg-red-100 text-red-800
                            @else bg-gray-100 text-gray-700 @endif">
                        {{ $case->status_name }}
                    </span>

                    @if($statusGroup !== 'unknown')
                    <span class="px-3 py-1 rounded-full text-xs font-medium
                            @if($statusGroup === 'working') bg-yellow-50 text-yellow-900 border border-yellow-300
                            @elseif($statusGroup === 'decision') bg-blue-50 text-blue-900 border border-blue-300
                            @elseif($statusGroup === 'final') bg-gray-50 text-gray-900 border border-gray-300
                            @elseif($statusGroup === 'review') bg-indigo-50 text-indigo-900 border border-indigo-300
                            @else bg-gray-50 text-gray-700 @endif">
                        {{ ucfirst($statusGroup) }}
                    </span>
                    @endif

                    @if($case->is_public)
                    <span class="px-3 py-1 rounded-full bg-green-100 text-green-800 text-xs font-medium">✅
                        Dipublikasikan</span>
                    @else
                    <span class="px-3 py-1 rounded-full bg-gray-100 text-gray-500 text-xs font-medium">🔒 Tidak
                        Publik</span>
                    @endif
                </div>

                {{-- Progress kelengkapan --}}
                <div class="mt-4 max-w-xs sm:max-w-sm">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-xs text-gray-500 font-medium">Kelengkapan Data</span>
                        <span
                            class="text-xs font-bold {{ $completePct >= 80 ? 'text-green-600' : ($completePct >= 50 ? 'text-yellow-600' : 'text-red-500') }}">
                            {{ $completePct }}%
                        </span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-2">
                        <div class="h-2 rounded-full transition-all duration-500
                                {{ $completePct >= 80 ? 'bg-green-500' : ($completePct >= 50 ? 'bg-yellow-400' : 'bg-red-400') }}"
                            style="width: {{ $completePct }}%">
                        </div>
                    </div>
                </div>

                {{-- Meta --}}
                <div class="mt-3 flex flex-wrap gap-x-4 gap-y-1 text-xs text-gray-400">
                    <span>📅 Dibuat: <strong class="text-gray-600">{{ optional($case->created_at)->format('d M Y, H:i')
                            }}</strong></span>
                    <span>✏️ Diperbarui: <strong class="text-gray-600">{{ optional($case->updated_at)->format('d M Y,
                            H:i') }}</strong></span>
                    @if($case->updatedBy ?? false)
                    <span>👤 Oleh: <strong class="text-gray-600">{{ $case->updatedBy->name }}</strong></span>
                    @endif
                </div>
            </div>

            {{-- RIGHT: Action buttons — full width on mobile --}}
            <div
                class="flex flex-row sm:flex-col md:flex-row items-stretch sm:items-end gap-2 flex-wrap sm:flex-shrink-0">
                @can('case.publish')
                <button wire:click="publishCases"
                    class="flex-1 sm:flex-none px-4 py-2 text-center {{ $case->is_public ? 'bg-red-600 hover:bg-red-700' : 'bg-green-600 hover:bg-green-700' }} text-white rounded-lg text-sm font-medium transition-colors">
                    {{ $case->is_public ? '🔒 Batalkan Publikasi' : '📢 Publikasikan' }}
                </button>
                @endcan

                @can('case.change-status')
                <div class="relative flex-1 sm:flex-none" x-data="{ open: false }">
                    <button @click="open = !open"
                        class="w-full px-4 py-2 bg-yellow-500 text-white rounded-lg text-sm font-medium hover:bg-yellow-600 transition-colors">
                        Ubah Status ▾
                    </button>
                    <div x-show="open" x-cloak @click.away="open = false"
                        class="absolute right-0 mt-2 w-52 bg-white border rounded-xl shadow-lg z-50 overflow-hidden">
                        @foreach($availableStatuses as $name => $label)
                        <button wire:click="changeStatus('{{ $name }}')" @click="open = false"
                            onclick="return confirm('Ubah status menjadi {{ $label }}?')"
                            class="block w-full text-left px-4 py-2.5 text-sm hover:bg-gray-50 transition-colors border-b border-gray-50 last:border-0">
                            {{ $label }}
                        </button>
                        @endforeach
                    </div>
                </div>
                @endcan

                @can('case.edit')
                <a href="{{ route('admin.cases.edit', $case->id) }}"
                    class="flex-1 sm:flex-none px-4 py-2 text-center bg-gray-800 text-white rounded-lg text-sm font-medium hover:bg-gray-900 transition-colors">
                    ✏️ Edit
                </a>
                @endcan
            </div>

        </div>
    </div>

    {{-- ===== STATISTIK CEPAT ===== --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 mb-4">
        <div class="bg-white rounded-xl p-3 sm:p-4 shadow-sm border border-gray-100 flex items-center gap-3">
            <div class="w-9 h-9 rounded-lg bg-blue-50 flex items-center justify-center text-blue-600 flex-shrink-0">📄
            </div>
            <div>
                <p class="text-xs text-gray-400">Dokumen</p>
                <p class="text-lg font-bold text-gray-800">{{ $docCount }}</p>
            </div>
        </div>
        <div class="bg-white rounded-xl p-3 sm:p-4 shadow-sm border border-gray-100 flex items-center gap-3">
            <div class="w-9 h-9 rounded-lg bg-purple-50 flex items-center justify-center text-purple-600 flex-shrink-0">
                👤</div>
            <div>
                <p class="text-xs text-gray-400">Aktor</p>
                <p class="text-lg font-bold text-gray-800">{{ $actorCount }}</p>
            </div>
        </div>
        <div
            class="col-span-2 sm:col-span-1 bg-white rounded-xl p-3 sm:p-4 shadow-sm border border-gray-100 flex items-center gap-3">
            <div class="w-9 h-9 rounded-lg bg-orange-50 flex items-center justify-center text-orange-600 flex-shrink-0">
                🖼️</div>
            <div>
                <p class="text-xs text-gray-400">Bukti</p>
                <p class="text-lg font-bold text-gray-800">{{ is_array($case->bukti) ? count($case->bukti) : 0 }}</p>
            </div>
        </div>
    </div>

    {{-- INFO / HELP --}}
    <div class="bg-yellow-50 border border-yellow-100 rounded-xl p-4 mb-4 text-sm text-yellow-900">
        <strong>Petunjuk singkat:</strong>
        <ul class="list-disc ml-5 mt-1 space-y-0.5">
            <li><strong>Ubah Status</strong> — perubahan legal status kasus (hanya tindakan resmi, butuh izin khusus).
            </li>
            <li><strong>Publikasikan</strong> — membuat kasus dapat dilihat publik; <em>tidak</em> mengubah status
                hukum.</li>
            <li><strong>Tugas</strong> — item kerja internal; menyelesaikan tugas tidak mengubah status kasus.</li>
        </ul>
    </div>

    {{-- ================= TABS ================= --}}
    <div class="bg-white rounded-t-xl shadow-sm border-b overflow-x-auto">
        <div class="flex gap-1 px-4 min-w-max sm:min-w-0">
            @foreach([
            'overview' => '📋 Ringkasan',
            'handling' => '🔧 Penanganan',
            'timeline' => '🕐 Linimasa',
            ] as $tab => $label)
            <button wire:click="setTab('{{ $tab }}')"
                class="relative py-4 px-3 text-sm font-medium whitespace-nowrap transition-colors
                        {{ $activeTab === $tab ? 'text-black border-b-2 border-black' : 'text-gray-400 hover:text-gray-700' }}">
                {{ $label }}
            </button>
            @endforeach
        </div>
    </div>

    {{-- ================= CONTENT ================= --}}
    <div class="bg-white rounded-b-xl shadow-sm p-4 sm:p-6">

        {{-- ===== OVERVIEW ===== --}}
        @if($activeTab === 'overview')

        {{-- Mobile: stack, Desktop: 2/3 + 1/3 grid --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- KIRI: Konten utama --}}
            <div class="lg:col-span-2 space-y-4">

                {{-- Checklist kelengkapan --}}
                <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                    <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-3">Checklist Kelengkapan
                    </h3>
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-2">
                        @foreach($completeness as $field => $filled)
                        <div
                            class="flex items-center gap-1.5 text-sm {{ $filled ? 'text-green-700' : 'text-red-400' }}">
                            @if($filled)
                            <svg class="w-4 h-4 flex-shrink-0 text-green-500" fill="none" stroke="currentColor"
                                stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                            @else
                            <svg class="w-4 h-4 flex-shrink-0 text-red-400" fill="none" stroke="currentColor"
                                stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            @endif
                            <span class="font-medium">{{ $field }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Description --}}
                <section class="bg-white border rounded-xl p-4">
                    <div class="flex items-center justify-between mb-2">
                        <h2 class="font-bold text-base">Kronologi / Deskripsi</h2>
                        {{-- @can('case.edit')
                        <a href="{{ route('admin.cases.edit', $case->id) }}#description"
                            class="text-xs text-blue-600 hover:underline flex-shrink-0 ml-2">✏️ Edit</a>
                        @endcan --}}
                    </div>
                    @php
                    if (method_exists($case, 'getTranslation')) {
                    $desc = $case->getTranslation('description', 'id', false);
                    if (!$desc) {
                    $desc = $case->getTranslation('description', 'en', false);
                    }
                    } else {
                    $desc = $case->description;
                    }
                    @endphp
                    @if($desc)
                    <div class="prose prose-sm prose-gray max-w-none text-gray-700">{!! $desc !!}</div>
                    @endif
                </section>

                {{-- Perkembangan --}}
                <section class="bg-white border rounded-xl p-4">
                    <div class="flex items-center justify-between mb-2">
                        <h2 class="font-bold text-base">Perkembangan</h2>
                        {{-- @can('case.edit')
                        <a href="{{ route('admin.cases.edit', $case->id) }}#perkembangan"
                            class="text-xs text-blue-600 hover:underline flex-shrink-0 ml-2">✏️ Edit</a>
                        @endcan --}}
                    </div>
                    @php
                    if (method_exists($case, 'getTranslation')) {
                    $perkembangan = $case->getTranslation('perkembangan', 'id', false);
                    if (!$perkembangan) {
                    $perkembangan = $case->getTranslation('perkembangan', 'en', false);
                    }
                    } else {
                    $perkembangan = $case->perkembangan;
                    }
                    @endphp
                    @if($perkembangan)
                    <div class="prose prose-sm prose-gray max-w-none text-gray-700">{!! $perkembangan !!}</div>
                    @endif
                </section>

                {{-- Pembelajaran --}}
                <section class="bg-white border rounded-xl p-4">
                    <div class="flex items-center justify-between mb-2">
                        <h2 class="font-bold text-base">Pembelajaran</h2>
                        {{-- @can('case.edit')
                        <a href="{{ route('admin.cases.edit', $case->id) }}#pembelajaran"
                            class="text-xs text-blue-600 hover:underline flex-shrink-0 ml-2">✏️ Edit</a>
                        @endcan --}}
                    </div>
                    @php
                    if (method_exists($case, 'getTranslation')) {
                    $pembelajaran = $case->getTranslation('pembelajaran', 'id', false);
                    if (!$pembelajaran) {
                    $pembelajaran = $case->getTranslation('pembelajaran', 'en', false);
                    }
                    } else {
                    $pembelajaran = $case->pembelajaran;
                    }
                    @endphp
                    @if($pembelajaran)
                    <div class="prose prose-sm prose-gray max-w-none text-gray-700">{!! $pembelajaran !!}</div>
                    @endif
                </section>

                {{-- Bukti / Evidence --}}
                <div class="bg-white border rounded-xl p-4">
                    <h2 class="font-semibold text-sm text-gray-500 uppercase tracking-wide mb-3">
                        Bukti / Evidence
                        @if($case->bukti && count($case->bukti))
                        <span class="ml-2 text-xs font-medium bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full">{{
                            count($case->bukti) }}</span>
                        @endif
                    </h2>
                    @if($case->bukti && count($case->bukti))
                    <div class="flex flex-wrap gap-2">
                        @foreach($case->bukti as $i => $ev)
                        @php
                        $path = 'storage/' . $ev;
                        $ext = strtolower(pathinfo($ev, PATHINFO_EXTENSION));
                        $isImage = in_array($ext, ['jpg','jpeg','png','gif','webp']);
                        $isPdf = $ext === 'pdf';
                        $label = $isImage ? 'Gambar ' . ($i + 1) : strtoupper($ext) . ' File';
                        $icon = $isImage ? '🖼️' : ($isPdf ? '📄' : '📎');
                        @endphp
                        @if($isImage)
                        <a href="{{ asset($path) }}" target="_blank"
                            class="group relative w-14 h-14 rounded-lg overflow-hidden border border-gray-200 flex-shrink-0"
                            title="{{ $label }}">
                            <img src="{{ asset($path) }}"
                                class="w-full h-full object-cover group-hover:scale-110 transition duration-200">
                            <div class="absolute inset-0 bg-black/0 group-hover:bg-black/20 transition"></div>
                        </a>
                        @else
                        <a href="{{ asset($path) }}" target="{{ $isPdf ? '_blank' : '_self' }}" {{ !$isPdf ? 'download'
                            : '' }}
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-gray-200 bg-gray-50 hover:bg-gray-100 transition text-sm text-gray-700 flex-shrink-0">
                            <span>{{ $icon }}</span><span>{{ $label }}</span>
                        </a>
                        @endif
                        @endforeach
                    </div>
                    @else
                    <p class="text-sm text-gray-400 italic">Tidak ada bukti yang dilampirkan.</p>
                    @endif
                </div>

            </div>

            {{-- KANAN: Sidebar — full width on mobile, 1/3 on lg --}}
            <div class="space-y-4">

                {{-- Detail Kasus --}}
                <div class="bg-gray-50 rounded-xl p-4 border border-gray-100 space-y-3 text-sm">
                    <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-widest">Detail Kasus</h3>
                    <div>
                        <p class="text-gray-400 text-xs">Tanggal Kejadian</p>
                        <p class="font-medium text-gray-800">{{ $case->event_date ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-400 text-xs">Lokasi</p>
                        @if($location['village'] || $location['district'] || $location['province'])
                        <p class="text-gray-800 leading-snug break-words">
                            {{ implode(', ', array_filter([$location['village'], $location['district'],
                            $location['province']])) }}
                        </p>
                        @else
                        <p class="text-gray-400 italic">Lokasi tidak ditemukan</p>
                        @endif
                    </div>
                </div>

                {{-- Pihak Terlibat --}}
                <div class="bg-gray-50 rounded-xl p-4 border border-gray-100 space-y-3 text-sm">
                    <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-widest">Pihak Terlibat</h3>
                    <div>
                        <p class="text-gray-400 text-xs">Pelapor</p>
                        <p class="font-medium text-gray-800 break-words">{{ $case->pelapor ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-400 text-xs">Terlapor</p>
                        <p class="font-medium text-gray-800 break-words">{{ $case->terlapor ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-400 text-xs mb-1">Pelaku</p>
                        @if($actors->isEmpty())
                        <p class="text-gray-400 italic text-xs">Belum ada pelaku</p>
                        @else
                        <ul class="space-y-1">
                            @foreach($actors as $actor)
                            <li class="flex items-start gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-gray-400 flex-shrink-0 mt-1.5"></span>
                                <span class="text-gray-700 break-words">{{ ucfirst($actor->type) }} — {{ $actor->name
                                    }}</span>
                            </li>
                            @endforeach
                        </ul>
                        @endif
                        @can('case.actor.manage')
                        <button
                            class="mt-2 px-3 py-1 bg-gray-900 text-white rounded-lg text-xs hover:bg-gray-700 transition-colors"
                            x-on:click="$dispatch('open-actor-modal', { caseId: {{ $case->id }} })">
                            + Tambah Pelaku
                        </button>
                        @endcan
                    </div>
                </div>

                {{-- Internal Notes --}}
                <div class="bg-amber-50 rounded-xl p-4 border border-amber-100 text-sm">
                    <h3 class="text-xs font-semibold text-amber-700 uppercase tracking-widest mb-2">📝 Catatan Internal
                    </h3>
                    @if($case->internal_notes ?? false)
                    <p class="text-gray-700 text-sm leading-relaxed break-words">{{ $case->internal_notes }}</p>
                    @else
                    <p class="text-amber-600 italic text-xs">Belum ada catatan internal.</p>
                    @endif
                    @can('case.edit')
                    <button class="mt-2 text-xs text-amber-700 underline hover:text-amber-900"
                        x-on:click="$dispatch('open-notes-modal', { caseId: {{ $case->id }} })">
                        + Tambah / Edit Catatan
                    </button>
                    @endcan
                </div>

            </div>
        </div>
        @endif

        {{-- ===== HANDLING ===== --}}
        @if($activeTab === 'handling')
        <div class="space-y-6">

            @can('case.task.create')
            <section>
                <livewire:cases.case-actions :case-id="$case->id" />
            </section>
            @endcan

            <section>
                <h2 class="font-semibold text-lg mb-4">Sumber & Diskusi</h2>

                {{-- Mobile: stack, Desktop: side by side --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    {{-- Dokumen --}}
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="font-medium text-sm text-gray-700">
                                Dokumen
                                <span class="ml-1 bg-gray-100 text-gray-500 text-xs px-2 py-0.5 rounded-full">{{
                                    $docCount }}</span>
                            </h3>
                            @can('case.document.upload')
                            <button
                                class="px-3 py-1 bg-black text-white rounded-lg text-xs hover:bg-gray-800 transition-colors"
                                x-on:click="$dispatch('open-upload-document-modal', { caseId: {{ $case->id }} })">
                                + Unggah
                            </button>
                            @endcan
                        </div>
                        <div class="space-y-2">
                            @forelse($documents as $doc)
                            <div
                                class="p-3 border rounded-xl bg-white shadow-sm flex items-center justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="font-medium text-sm truncate">{{ $doc->title ?? 'Dokumen' }}</p>
                                    <p class="text-xs text-gray-400">{{ $doc->mime }}</p>
                                </div>
                                <div class="flex gap-2 flex-shrink-0">
                                    @can('case.document.view')
                                    <a href="{{ asset('storage/'.$doc->file_path) }}" target="_blank"
                                        class="text-blue-600 text-xs hover:underline">Buka</a>
                                    @endcan
                                    <button class="text-xs text-gray-500 hover:underline"
                                        wire:click="$dispatch('open-edit-document-modal', { docId: {{ $doc->id }} })">Edit</button>
                                </div>
                            </div>
                            @empty
                            <p class="text-sm text-gray-400 italic">Belum ada dokumen.</p>
                            @endforelse
                        </div>
                    </div>

                    {{-- Diskusi --}}
                    <div>
                        <h3 class="font-medium text-sm text-gray-700 mb-3">Diskusi Tim</h3>
                        @livewire('cases.case-discussion', ['caseId' => $case->id])
                    </div>

                </div>
            </section>
        </div>
        @endif

        {{-- ===== TIMELINE ===== --}}
        @if($activeTab === 'timeline')
        <div class="space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-2">
                <h2 class="font-semibold text-base">Log Aktivitas & Linimasa</h2>
                @can('case.timeline.add')
                <button
                    class="w-full sm:w-auto px-4 py-2 bg-black text-white rounded-lg text-sm hover:bg-gray-800 transition-colors text-center"
                    x-on:click="$dispatch('open-upload-timeline-modal', { caseId: {{ $case->id }} })">
                    + Tambah Timeline
                </button>
                @endcan
            </div>

            <div class="border-l-2 border-gray-200 pl-4 sm:pl-5 space-y-4">
                @forelse($timelines as $log)
                <div class="relative">
                    <span
                        class="absolute -left-[19px] sm:-left-[23px] top-1.5 w-3 h-3 rounded-full bg-gray-400 border-2 border-white ring-2 ring-gray-200"></span>
                    <div class="bg-gray-50 border border-gray-100 rounded-xl p-3 sm:p-4">
                        <p class="text-sm text-gray-800 leading-relaxed break-words">{{ $log->notes }}</p>
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1 mt-2">
                            <p class="text-xs text-gray-400">{{ \Carbon\Carbon::parse($log->created_at)->format('d M Y,
                                H:i') }}</p>
                            <button
                                class="text-xs text-gray-400 hover:text-gray-700 hover:underline transition-colors self-start sm:self-auto"
                                wire:click="$dispatch('open-edit-timeline-modal', { timelineId: {{ $log->id }} })">
                                Edit
                            </button>
                        </div>
                    </div>
                </div>
                @empty
                <p class="text-sm text-gray-400 italic pl-2">Belum ada entri linimasa.</p>
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
@if(session('success'))
<div x-data="{ show: true }" x-show="show" x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100 translate-y-0"
    x-transition:leave-end="opacity-0 translate-y-4" x-init="setTimeout(() => show = false, 3000)"
    class="fixed bottom-4 right-4 sm:bottom-6 sm:right-6 bg-green-600 text-white px-4 sm:px-5 py-3 rounded-xl shadow-lg text-sm font-medium flex items-center gap-2 max-w-xs sm:max-w-sm">
    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
    </svg>
    <span class="break-words">{{ session('success') }}</span>
</div>
@endif
</div>