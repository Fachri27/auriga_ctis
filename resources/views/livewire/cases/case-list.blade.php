<div>
    <div class="max-w-7xl mx-auto px-6 py-6 space-y-4">

        {{-- SUCCESS TOAST --}}
        @if(session('success'))
        <div class="flex items-center gap-3 p-3 text-sm border rounded-lg"
             style="color:var(--ok);background:var(--ok-soft);border-color:var(--ok)">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
            </svg>
            {{ session('success') }}
        </div>
        @endif

        {{-- ===== HEADER ===== --}}
        <div class="flex items-center justify-between gap-4 border-b border-[color:var(--hairline)] pb-3">
            <div>
                <div class="cms-eyebrow">KASUS</div>
                <h1 class="text-xl font-semibold text-[color:var(--ink)] tracking-tight mt-0.5">Daftar Kasus</h1>
            </div>
            @can('case.create')
            <a href="{{ route('case.create') }}" class="cms-btn cms-btn-leaf">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah Kasus
            </a>
            @endcan
        </div>

        {{-- ===== FILTER & SEARCH ===== --}}
        <div class="cms-panel cms-rise" style="animation-delay:.04s">
            <div class="cms-panel-body" style="padding:16px 20px">
                <div class="flex flex-col gap-3">

                    {{-- Row 1: Search --}}
                    <div class="relative flex-1">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-[color:var(--muted)]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 105 11a6 6 0 0012 0z"/>
                        </svg>
                        <input type="text" wire:model.live="search" placeholder="Cari nomor kasus, judul, pelapor, atau terlapor..."
                            class="cms-input w-full pl-9">
                    </div>

                    {{-- Row 2: Filters --}}
                    <div class="flex flex-col sm:flex-row gap-3">
                        <select wire:model.live="filter" class="cms-input flex-1">
                            <option value="">Semua Status</option>
                            <option value="investigation">Penyidikan</option>
                            <option value="published">Dipublikasikan</option>
                            <option value="closed">Ditutup</option>
                        </select>

                        <select wire:model.live="filterVerif" class="cms-input flex-1">
                            <option value="">Semua Verifikasi</option>
                            <option value="me">Ditugaskan ke Saya</option>
                            <option value="pending">Menunggu Review</option>
                            <option value="rejected">Ditolak</option>
                        </select>

                        @if($search || $filter || $filterVerif)
                        <button wire:click="resetFilters" class="cms-btn cms-btn-danger whitespace-nowrap">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            Reset Filter
                        </button>
                        @endif
                    </div>
                </div>

                {{-- Result count --}}
                <div class="mt-3 text-xs text-[color:var(--muted)]">
                    Menampilkan <strong class="text-[color:var(--ink-2)]">{{ $cases->firstItem() ?? 0 }}–{{ $cases->lastItem() ?? 0 }}</strong>
                    dari <strong class="text-[color:var(--ink-2)]">{{ $cases->total() }}</strong> kasus
                </div>
            </div>
        </div>

        @php
        $statusVariants = [
            'open'          => 'info',
            'investigation' => 'warn',
            'penyelidikan'  => 'warn',
            'penyidikan'    => 'warn',
            'prosecution'   => 'info',
            'trial'         => 'info',
            'vonis'         => 'info',
            'executed'      => 'warn',
            'completed'     => 'ok',
            'closed'        => 'default',
            'rejected'      => 'danger',
        ];
        @endphp

        {{-- ===== TABLE CARD ===== --}}
        <div class="cms-panel cms-rise" style="animation-delay:.10s">

            {{-- ===== DESKTOP TABLE ===== --}}
            <div class="hidden md:block overflow-x-auto">
                <table class="cms-table">
                    <thead>
                        <tr>
                            <th>No. Kasus</th>
                            <th>Status</th>
                            <th>Perkembangan</th>
                            <th>Deskripsi</th>
                            <th>Tanggal Kejadian</th>
                            <th>Visibilitas</th>
                            <th>Verifikasi</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($cases as $c)
                        @php
                        $tr           = $c->translations->firstWhere('locale', 'id') ?? $c->translations->first();
                        $title        = $tr?->title ?? '-';
                        $desc         = $tr?->description ?? '-';
                        $perkembangan = $tr?->perkembangan ?? '-';
                        $statusKey    = $c->status?->key ?? '';
                        $statusVariant = $statusVariants[$statusKey] ?? 'default';
                        $hasDesc     = $desc !== '-' && !empty(strip_tags($desc));
                        if ($perkembangan !== '-' && preg_match('/^\[/', $perkembangan)) {
                            $decoded = json_decode($perkembangan, true);
                            if (is_array($decoded)) {
                                $perkembangan = collect($decoded)->pluck('notes')->filter()->implode("\n");
                            }
                        }
                        $hasPerk     = $perkembangan !== '-' && !empty(strip_tags($perkembangan));
                        @endphp
                        <tr>

                            {{-- No. Kasus --}}
                            <td>
                                <div class="num">{{ $c->case_number }}</div>
                                <div class="text-[11px] text-[color:var(--muted)] mt-0.5 max-w-[160px] truncate" title="{{ $title }}">
                                    {{ $title }}
                                </div>
                            </td>

                            {{-- Status --}}
                            <td>
                                @if($c->status?->name)
                                <x-internal.badge variant="{{ $statusVariant }}">{{ $c->status->name }}</x-internal.badge>
                                @else
                                <span class="text-[color:var(--muted-2)] text-xs italic">—</span>
                                @endif
                            </td>

                            {{-- Perkembangan --}}
                            <td class="max-w-[200px]">
                                @if($hasPerk)
                                <p class="text-xs text-[color:var(--ink-2)] line-clamp-2 leading-relaxed">
                                    {{ Str::limit(strip_tags($perkembangan), 100) }}
                                </p>
                                @else
                                <span class="inline-flex items-center gap-1 text-[10px] font-medium" style="color:var(--warn)">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                                    </svg>
                                    Belum diisi
                                </span>
                                @endif
                            </td>

                            {{-- Deskripsi --}}
                            <td class="max-w-[200px]">
                                @if($hasDesc)
                                <p class="text-xs text-[color:var(--ink-2)] line-clamp-2 leading-relaxed">
                                    {{ Str::limit(strip_tags($desc), 100) }}
                                </p>
                                @else
                                <span class="text-[color:var(--muted-2)] text-xs italic">Belum ada</span>
                                @endif
                            </td>

                            {{-- Tanggal --}}
                            <td class="whitespace-nowrap">
                                <p class="text-xs text-[color:var(--ink)] font-medium">
                                    {{ $c->event_date ? \Carbon\Carbon::parse($c->event_date)->format('d M Y') : '-' }}
                                </p>
                                <p class="text-[10px] text-[color:var(--muted)] mt-0.5">
                                    {{ $c->event_date ? \Carbon\Carbon::parse($c->event_date)->diffForHumans() : '-' }}
                                </p>
                            </td>

                            {{-- Visibilitas --}}
                            <td>
                                @if($c->is_public)
                                <x-internal.badge variant="ok">Publik</x-internal.badge>
                                @else
                                <x-internal.badge variant="default">Privat</x-internal.badge>
                                @endif
                            </td>

                            {{-- Verifikasi --}}
                            <td>
                                @if($c->verified_by)
                                <x-internal.badge variant="info">Terverifikasi</x-internal.badge>
                                <p class="text-[10px] text-[color:var(--muted)] mt-1 max-w-[90px] truncate">
                                    {{ $c->verifiedBy?->name ?? '-' }}
                                </p>
                                @else
                                <x-internal.badge variant="warn">Belum</x-internal.badge>
                                @endif
                            </td>

                            {{-- Aksi --}}
                            <td class="text-right">
                                <div class="flex justify-end items-center gap-3 text-xs">
                                    @can('case.view')
                                    <a href="{{ route('case.detail', $c->id) }}" class="link">Detail</a>
                                    @endcan
                                    @can('case.update')
                                    <a href="{{ route('case.edit', $c->id) }}" class="text-[color:var(--muted)] hover:text-[color:var(--ink)] font-medium transition-colors">Edit</a>
                                    @endcan
                                    <button wire:click="deleteCase({{ $c->id }})"
                                        onclick="return confirm('Hapus kasus {{ $c->case_number }}? Tindakan ini tidak bisa dibatalkan.')"
                                        class="text-[color:var(--danger)] hover:opacity-80 font-medium transition-opacity">
                                        Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-sm text-[color:var(--muted)]">
                                Tidak ada kasus ditemukan. Coba ubah filter atau kata kunci pencarian.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- ===== MOBILE CARD LIST ===== --}}
            <div class="md:hidden divide-y divide-[color:var(--hairline)]">
                @forelse($cases as $c)
                @php
                $tr           = $c->translations->firstWhere('locale', 'id') ?? $c->translations->first();
                $title        = $tr?->title ?? '-';
                $desc         = $tr?->description ?? '-';
                $perkembangan = $tr?->perkembangan ?? '-';
                $statusKey    = $c->status?->key ?? '';
                $statusVariant = $statusVariants[$statusKey] ?? 'default';
                $hasDesc     = $desc !== '-' && !empty(strip_tags($desc));
                if ($perkembangan !== '-' && preg_match('/^\[/', $perkembangan)) {
                    $decoded = json_decode($perkembangan, true);
                    if (is_array($decoded)) {
                        $perkembangan = collect($decoded)->pluck('notes')->filter()->implode("\n");
                    }
                }
                $hasPerk     = $perkembangan !== '-' && !empty(strip_tags($perkembangan));
                @endphp
                <div class="p-4 space-y-2.5">

                    {{-- Top row: nomor + badges --}}
                    <div class="flex items-start justify-between gap-2">
                        <div class="min-w-0">
                            <p class="num text-sm leading-snug">{{ $c->case_number }}</p>
                            @if($title !== '-')
                            <p class="text-xs text-[color:var(--muted)] mt-0.5 leading-snug line-clamp-1">{{ $title }}</p>
                            @endif
                        </div>
                        <div class="flex flex-wrap gap-1 justify-end flex-shrink-0">
                            @if($c->status?->name)
                            <x-internal.badge variant="{{ $statusVariant }}">{{ $c->status?->name ?? '-' }}</x-internal.badge>
                            @endif
                            @if($c->is_public)
                            <x-internal.badge variant="ok">Publik</x-internal.badge>
                            @else
                            <x-internal.badge variant="default">Privat</x-internal.badge>
                            @endif
                        </div>
                    </div>

                    {{-- Perkembangan --}}
                    <div class="rounded-lg px-3 py-2" style="background:var(--paper-2)">
                        <p class="cms-eyebrow mb-1">Perkembangan</p>
                        @if($hasPerk)
                        <p class="text-xs text-[color:var(--ink-2)] leading-relaxed line-clamp-2">
                            {{ Str::limit(strip_tags($perkembangan), 120) }}
                        </p>
                        @else
                        <p class="text-[10px] font-medium inline-flex items-center gap-1" style="color:var(--warn)">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                            Belum diisi
                        </p>
                        @endif
                    </div>

                    {{-- Deskripsi --}}
                    @if($hasDesc)
                    <div class="px-3 py-2 rounded-lg" style="background:var(--paper-2)">
                        <p class="cms-eyebrow mb-1">Deskripsi</p>
                        <p class="text-xs text-[color:var(--ink-2)] leading-relaxed line-clamp-2">
                            {{ Str::limit(strip_tags($desc), 120) }}
                        </p>
                    </div>
                    @endif

                    {{-- Meta row --}}
                    <div class="flex flex-wrap gap-x-4 gap-y-1 text-xs text-[color:var(--muted)]">
                        <span class="inline-flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            {{ $c->event_date ? \Carbon\Carbon::parse($c->event_date)->format('d M Y') : '-' }}
                        </span>
                        @if($c->verified_by)
                        <span class="inline-flex items-center gap-1" style="color:var(--leaf-deep)">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            {{ $c->verifiedBy?->name ?? 'Terverifikasi' }}
                        </span>
                        @else
                        <span class="inline-flex items-center gap-1" style="color:var(--warn)">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Belum diverifikasi
                        </span>
                        @endif
                    </div>

                    {{-- Actions --}}
                    <div class="flex items-center gap-4 pt-2 border-t border-[color:var(--hairline)] text-xs font-medium">
                        @can('case.view')
                        <a href="{{ route('case.detail', $c->id) }}" class="link">Detail</a>
                        @endcan
                        @can('case.update')
                        <a href="{{ route('case.edit', $c->id) }}" class="text-[color:var(--muted)] hover:text-[color:var(--ink)]">Edit</a>
                        @endcan
                        <button wire:click="deleteCase({{ $c->id }})"
                            onclick="return confirm('Hapus kasus {{ $c->case_number }}?')"
                            class="text-[color:var(--danger)] hover:opacity-80 ml-auto">
                            Hapus
                        </button>
                    </div>
                </div>
                @empty
                <div class="py-12 text-center text-sm text-[color:var(--muted)]">
                    <p class="font-medium">Tidak ada kasus ditemukan</p>
                    <p class="text-xs mt-1">Coba ubah filter atau kata kunci</p>
                </div>
                @endforelse
            </div>

            {{-- ===== PAGINATION ===== --}}
            <div class="px-5 py-4 border-t border-[color:var(--hairline)] flex flex-col sm:flex-row items-center justify-between gap-3" style="background:var(--paper-2)">
                <p class="text-xs text-[color:var(--muted)]">
                    Menampilkan
                    <strong class="text-[color:var(--ink-2)]">{{ $cases->firstItem() ?? 0 }}–{{ $cases->lastItem() ?? 0 }}</strong>
                    dari
                    <strong class="text-[color:var(--ink-2)]">{{ $cases->total() }}</strong>
                    kasus
                </p>
                <div class="text-sm">
                    {{ $cases->links() }}
                </div>
            </div>
        </div>

        {{-- MODAL --}}
        <livewire:cases.case-modal />

    </div>
</div>