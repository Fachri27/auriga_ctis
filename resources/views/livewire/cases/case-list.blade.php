<div>
    <div class="px-4 sm:px-6 lg:mx-10 py-6 space-y-5">

        {{-- SUCCESS TOAST --}}
        @if(session('success'))
        <div class="flex items-center gap-3 p-4 text-green-700 bg-green-50 border border-green-200 rounded-xl text-sm">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
            </svg>
            {{ session('success') }}
        </div>
        @endif

        {{-- ===== HEADER ===== --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Daftar Kasus</h1>
                <p class="text-sm text-gray-400 mt-0.5">Kelola dan pantau seluruh kasus yang masuk ke sistem</p>
            </div>
            @can('case.create')
            <a href="{{ route('case.create') }}"
                class="inline-flex items-center gap-2 px-4 py-2.5 bg-gray-900 text-white rounded-xl text-sm font-semibold hover:bg-gray-700 transition-colors self-start sm:self-auto">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah Kasus
            </a>
            @endcan
        </div>

        {{-- ===== FILTER & SEARCH ===== --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
            <div class="flex flex-col gap-3">

                {{-- Row 1: Search + Tambah --}}
                <div class="flex gap-3">
                    <div class="relative flex-1">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 105 11a6 6 0 0012 0z"/>
                        </svg>
                        <input type="text" wire:model.live="search" placeholder="Cari nomor kasus atau judul..."
                            class="w-full pl-9 pr-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-200 focus:border-gray-400 transition-colors">
                    </div>
                </div>

                {{-- Row 2: Filters --}}
                <div class="flex flex-col sm:flex-row gap-3">
                    <select wire:model.live="filter"
                        class="flex-1 px-3 py-2.5 text-sm border border-gray-200 rounded-lg bg-white text-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-200 focus:border-gray-400 transition-colors">
                        <option value="">Semua Status</option>
                        <option value="investigation">Penyidikan</option>
                        <option value="published">Dipublikasikan</option>
                        <option value="closed">Ditutup</option>
                    </select>

                    <select wire:model.live="filterVerif"
                        class="flex-1 px-3 py-2.5 text-sm border border-gray-200 rounded-lg bg-white text-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-200 focus:border-gray-400 transition-colors">
                        <option value="">Semua Verifikasi</option>
                        <option value="me">Ditugaskan ke Saya</option>
                        <option value="pending">Menunggu Review</option>
                        <option value="rejected">Ditolak</option>
                    </select>

                    @if($search || $filter || $filterVerif)
                    <button wire:click="resetFilters"
                        class="px-4 py-2.5 text-sm text-red-500 border border-red-200 rounded-lg hover:bg-red-50 transition-colors whitespace-nowrap">
                        ✕ Reset Filter
                    </button>
                    @endif
                </div>
            </div>

            {{-- Result count --}}
            <div class="mt-3 text-xs text-gray-400">
                Menampilkan <strong class="text-gray-600">{{ $cases->firstItem() ?? 0 }}–{{ $cases->lastItem() ?? 0 }}</strong>
                dari <strong class="text-gray-600">{{ $cases->total() }}</strong> kasus
            </div>
        </div>

        {{-- ===== TABLE CARD ===== --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">

            {{-- ===== DESKTOP TABLE ===== --}}
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider border-b border-gray-100">
                        <tr>
                            <th class="px-5 py-3.5 text-left font-semibold">No. Kasus</th>
                            <th class="px-5 py-3.5 text-left font-semibold">Status</th>
                            <th class="px-5 py-3.5 text-left font-semibold">Perkembangan</th>
                            <th class="px-5 py-3.5 text-left font-semibold">Deskripsi</th>
                            <th class="px-5 py-3.5 text-left font-semibold">Tanggal Kejadian</th>
                            <th class="px-5 py-3.5 text-left font-semibold">Visibilitas</th>
                            <th class="px-5 py-3.5 text-left font-semibold">Verifikasi</th>
                            <th class="px-5 py-3.5 text-right font-semibold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($cases as $c)
                        @php
                        $tr           = $c->translations->firstWhere('locale', 'id') ?? $c->translations->first();
                        $title        = $tr?->title ?? '-';
                        $desc         = $tr?->description ?? '-';
                        $perkembangan = $tr?->perkembangan ?? '-';
                        $statusKey    = $c->status_key ?? '';
                        $statusColors = [
                            'open'          => 'bg-sky-100 text-sky-700',
                            'investigation' => 'bg-yellow-100 text-yellow-700',
                            'penyelidikan'  => 'bg-yellow-100 text-yellow-700',
                            'penyidikan'    => 'bg-orange-100 text-orange-700',
                            'prosecution'   => 'bg-blue-100 text-blue-700',
                            'trial'         => 'bg-purple-100 text-purple-700',
                            'vonis'         => 'bg-indigo-100 text-indigo-700',
                            'executed'      => 'bg-orange-100 text-orange-700',
                            'completed'     => 'bg-green-100 text-green-700',
                            'closed'        => 'bg-gray-100 text-gray-600',
                            'rejected'      => 'bg-red-100 text-red-700',
                        ];
                        $statusClass = $statusColors[$statusKey] ?? 'bg-gray-100 text-gray-600';
                        $hasDesc     = $desc !== '-' && !empty(strip_tags($desc));
                        $hasPerk     = $perkembangan !== '-' && !empty(strip_tags($perkembangan));
                        @endphp
                        <tr class="hover:bg-gray-50/70 transition-colors">

                            {{-- No. Kasus --}}
                            <td class="px-5 py-4">
                                <div class="font-semibold text-gray-900">{{ $c->case_number }}</div>
                                <div class="text-[11px] text-gray-400 mt-0.5 max-w-[160px] truncate" title="{{ $title }}">
                                    {{ $title }}
                                </div>
                            </td>

                            {{-- Status --}}
                            <td class="px-5 py-4">
                                @if($c->status_name)
                                <span class="inline-block px-2.5 py-1 rounded-full text-[10px] font-bold {{ $statusClass }}">
                                    {{ $c->status_name }}
                                </span>
                                @else
                                <span class="text-gray-300 text-xs italic">—</span>
                                @endif
                            </td>

                            {{-- Perkembangan --}}
                            <td class="px-5 py-4 max-w-[200px]">
                                @if($hasPerk)
                                <p class="text-xs text-gray-600 line-clamp-2 leading-relaxed">
                                    {!! Str::limit(strip_tags($perkembangan), 100) !!}
                                </p>
                                @else
                                <span class="inline-flex items-center gap-1 text-[10px] text-orange-500 font-medium">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                                    </svg>
                                    Belum diisi
                                </span>
                                @endif
                            </td>

                            {{-- Deskripsi --}}
                            <td class="px-5 py-4 max-w-[200px]">
                                @if($hasDesc)
                                <p class="text-xs text-gray-600 line-clamp-2 leading-relaxed">
                                    {!! Str::limit(strip_tags($desc), 100) !!}
                                </p>
                                @else
                                <span class="text-gray-300 text-xs italic">Belum ada</span>
                                @endif
                            </td>

                            {{-- Tanggal --}}
                            <td class="px-5 py-4 whitespace-nowrap">
                                <p class="text-xs text-gray-700 font-medium">
                                    {{ \Carbon\Carbon::parse($c->event_date)->format('d M Y') }}
                                </p>
                                <p class="text-[10px] text-gray-400 mt-0.5">
                                    {{ \Carbon\Carbon::parse($c->event_date)->diffForHumans() }}
                                </p>
                            </td>

                            {{-- Visibilitas --}}
                            <td class="px-5 py-4">
                                @if($c->is_public)
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 text-[10px] font-bold rounded-full bg-green-100 text-green-700">
                                    <span class="w-1.5 h-1.5 rounded-full bg-green-500 flex-shrink-0"></span>
                                    Publik
                                </span>
                                @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 text-[10px] font-bold rounded-full bg-gray-100 text-gray-500">
                                    <span class="w-1.5 h-1.5 rounded-full bg-gray-400 flex-shrink-0"></span>
                                    Privat
                                </span>
                                @endif
                            </td>

                            {{-- Verifikasi --}}
                            <td class="px-5 py-4">
                                @if($c->verified_by)
                                <span class="inline-block px-2.5 py-1 text-[10px] font-bold rounded-full bg-blue-100 text-blue-700">
                                    ✓ Terverifikasi
                                </span>
                                <p class="text-[10px] text-gray-400 mt-1 max-w-[90px] truncate">
                                    {{ $c->verifiedBy?->name ?? '-' }}
                                </p>
                                @else
                                <span class="inline-block px-2.5 py-1 text-[10px] font-bold rounded-full bg-orange-100 text-orange-600">
                                    ⏳ Belum
                                </span>
                                @endif
                            </td>

                            {{-- Aksi --}}
                            <td class="px-5 py-4 text-right">
                                <div class="flex justify-end items-center gap-3 text-xs">
                                    @can('case.view')
                                    <a href="{{ route('case.detail', $c->id) }}"
                                        class="text-blue-600 hover:text-blue-800 font-medium hover:underline transition-colors">
                                        Detail
                                    </a>
                                    @endcan
                                    @can('case.update')
                                    <a href="{{ route('case.edit', $c->id) }}"
                                        class="text-gray-600 hover:text-gray-900 font-medium hover:underline transition-colors">
                                        Edit
                                    </a>
                                    @endcan
                                    <button wire:click="deleteCase({{ $c->id }})"
                                        onclick="return confirm('Hapus kasus {{ $c->case_number }}? Tindakan ini tidak bisa dibatalkan.')"
                                        class="text-red-500 hover:text-red-700 font-medium hover:underline transition-colors">
                                        Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="py-16 text-center">
                                <svg class="w-10 h-10 mx-auto mb-3 text-gray-200" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <p class="font-medium text-gray-500 text-sm">Tidak ada kasus ditemukan</p>
                                <p class="text-xs text-gray-400 mt-1">Coba ubah filter atau kata kunci pencarian</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- ===== MOBILE CARD LIST ===== --}}
            <div class="md:hidden divide-y divide-gray-100">
                @forelse($cases as $c)
                @php
                $tr           = $c->translations->firstWhere('locale', 'id') ?? $c->translations->first();
                $title        = $tr?->title ?? '-';
                $desc         = $tr?->description ?? '-';
                $perkembangan = $tr?->perkembangan ?? '-';
                $statusKey    = $c->status_key ?? '';
                $statusColors = [
                    'open'          => 'bg-sky-100 text-sky-700',
                    'investigation' => 'bg-yellow-100 text-yellow-700',
                    'penyelidikan'  => 'bg-yellow-100 text-yellow-700',
                    'penyidikan'    => 'bg-orange-100 text-orange-700',
                    'prosecution'   => 'bg-blue-100 text-blue-700',
                    'trial'         => 'bg-purple-100 text-purple-700',
                    'vonis'         => 'bg-indigo-100 text-indigo-700',
                    'executed'      => 'bg-orange-100 text-orange-700',
                    'completed'     => 'bg-green-100 text-green-700',
                    'closed'        => 'bg-gray-100 text-gray-600',
                    'rejected'      => 'bg-red-100 text-red-700',
                ];
                $statusClass = $statusColors[$statusKey] ?? 'bg-gray-100 text-gray-600';
                $hasDesc     = $desc !== '-' && !empty(strip_tags($desc));
                $hasPerk     = $perkembangan !== '-' && !empty(strip_tags($perkembangan));
                @endphp
                <div class="p-4 space-y-2.5">

                    {{-- Top row: nomor + badges --}}
                    <div class="flex items-start justify-between gap-2">
                        <div class="min-w-0">
                            <p class="font-bold text-gray-900 text-sm leading-snug">{{ $c->case_number }}</p>
                            @if($title !== '-')
                            <p class="text-xs text-gray-500 mt-0.5 leading-snug line-clamp-1">{{ $title }}</p>
                            @endif
                        </div>
                        <div class="flex flex-wrap gap-1 justify-end flex-shrink-0">
                            @if($c->status_name)
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $statusClass }}">
                                {{ $c->status_name }}
                            </span>
                            @endif
                            @if($c->is_public)
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-green-100 text-green-700">Publik</span>
                            @else
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-gray-100 text-gray-500">Privat</span>
                            @endif
                        </div>
                    </div>

                    {{-- Perkembangan --}}
                    <div class="bg-gray-50 rounded-lg px-3 py-2">
                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-1">Perkembangan</p>
                        @if($hasPerk)
                        <p class="text-xs text-gray-600 leading-relaxed line-clamp-2">
                            {!! Str::limit(strip_tags($perkembangan), 120) !!}
                        </p>
                        @else
                        <p class="text-[10px] text-orange-500 font-medium">⚠ Belum diisi</p>
                        @endif
                    </div>

                    {{-- Deskripsi --}}
                    @if($hasDesc)
                    <div class="px-3 py-2 bg-gray-50 rounded-lg">
                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-1">Deskripsi</p>
                        <p class="text-xs text-gray-600 leading-relaxed line-clamp-2">
                            {!! Str::limit(strip_tags($desc), 120) !!}
                        </p>
                    </div>
                    @endif

                    {{-- Meta row --}}
                    <div class="flex flex-wrap gap-x-4 gap-y-1 text-xs text-gray-400">
                        <span>📅 {{ \Carbon\Carbon::parse($c->event_date)->format('d M Y') }}</span>
                        @if($c->verified_by)
                        <span class="text-blue-500">✓ {{ $c->verifiedBy?->name ?? 'Terverifikasi' }}</span>
                        @else
                        <span class="text-orange-400">⏳ Belum diverifikasi</span>
                        @endif
                    </div>

                    {{-- Actions --}}
                    <div class="flex items-center gap-4 pt-2 border-t border-gray-100 text-xs font-medium">
                        @can('case.view')
                        <a href="{{ route('case.detail', $c->id) }}" class="text-blue-600 hover:underline">Detail</a>
                        @endcan
                        @can('case.update')
                        <a href="{{ route('case.edit', $c->id) }}" class="text-gray-600 hover:underline">Edit</a>
                        @endcan
                        <button wire:click="deleteCase({{ $c->id }})"
                            onclick="return confirm('Hapus kasus {{ $c->case_number }}?')"
                            class="text-red-500 hover:underline ml-auto">
                            Hapus
                        </button>
                    </div>
                </div>
                @empty
                <div class="py-16 text-center text-sm text-gray-400">
                    <p class="font-medium">Tidak ada kasus ditemukan</p>
                    <p class="text-xs mt-1">Coba ubah filter atau kata kunci</p>
                </div>
                @endforelse
            </div>

            {{-- ===== PAGINATION ===== --}}
            <div class="px-5 py-4 border-t border-gray-100 bg-gray-50 flex flex-col sm:flex-row items-center justify-between gap-3">
                <p class="text-xs text-gray-400">
                    Menampilkan
                    <strong class="text-gray-600">{{ $cases->firstItem() ?? 0 }}–{{ $cases->lastItem() ?? 0 }}</strong>
                    dari
                    <strong class="text-gray-600">{{ $cases->total() }}</strong>
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