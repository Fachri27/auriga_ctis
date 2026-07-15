<div class="max-w-7xl mx-auto px-6 py-6 space-y-4 cms-rise" style="animation-delay:.04s">

    {{-- ================= HEADER ================= --}}
    <div class="cms-panel-head">
        <div>
            <div class="cms-eyebrow">LAPORAN</div>
            <h1 class="text-xl font-semibold text-[color:var(--ink)] tracking-tight mt-0.5">
                Laporan <span class="font-mono-c">#{{ $report->report_code }}</span>
            </h1>
            <div class="cms-panel-sub">Dibuat {{ $report->created_at->format('d M Y, H:i') }}</div>
        </div>

        {{-- STATUS BADGE --}}
        @if($report->status?->key === 'open')
            <x-internal.badge variant="warn">{{ $report->status?->name ?? '-' }}</x-internal.badge>
        @elseif($report->status?->key === 'verified')
            <x-internal.badge variant="ok">{{ $report->status?->name ?? '-' }}</x-internal.badge>
        @elseif($report->status?->key === 'rejected')
            <x-internal.badge variant="danger">{{ $report->status?->name ?? '-' }}</x-internal.badge>
        @else
            <x-internal.badge variant="default">{{ $report->status?->name ?? '-' }}</x-internal.badge>
        @endif
    </div>

    {{-- ================= GRID LAYOUT ================= --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

        {{-- ================= LEFT CONTENT ================= --}}
        <div class="lg:col-span-2 space-y-4">

            {{-- REPORT DESCRIPTION --}}
            <div class="cms-panel cms-rise" style="animation-delay:.10s">
                <div class="cms-panel-head">
                    <div>
                        <div class="cms-eyebrow">RINGKASAN</div>
                        <div class="cms-panel-title">Deskripsi Laporan</div>
                    </div>
                </div>
                <div class="cms-panel-body" style="padding:16px 20px">
                    <p class="text-sm text-[color:var(--ink-2)] leading-relaxed whitespace-pre-line">
                        {{ strip_tags($report->translations->where('locale','id')->first()->description ?? 'Tidak ada deskripsi.') }}
                    </p>
                </div>
            </div>

            {{-- EVIDENCE --}}
            <div class="cms-panel cms-rise" style="animation-delay:.16s">
                <div class="cms-panel-head">
                    <div>
                        <div class="cms-eyebrow">LAMPIRAN</div>
                        <div class="cms-panel-title">Bukti / Evidence</div>
                    </div>
                </div>
                <div class="cms-panel-body" style="padding:16px 20px">
                    @if ($report->evidence && count($report->evidence))
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                        @foreach ($report->evidence as $ev)
                        @php
                        $path = 'storage/' . $ev;
                        $ext = strtolower(pathinfo($ev, PATHINFO_EXTENSION));
                        $isImage = in_array($ext, ['jpg','jpeg','png','gif','webp']);
            $icon = $isImage ? 'image' : ($ext === 'pdf' ? 'pdf' : 'file');
                        @endphp

                        @if ($isImage)
                        <a href="{{ asset($path) }}" target="_blank"
                            class="group relative border border-[color:var(--hairline)] rounded-lg overflow-hidden">
                            <img src="{{ asset($path) }}"
                                class="h-40 w-full object-cover group-hover:scale-105 transition">
                        </a>

                        @elseif ($ext === 'pdf')
                        <a href="{{ asset($path) }}" target="_blank"
                            class="flex flex-col items-center justify-center h-40 border border-[color:var(--hairline)] rounded-lg bg-[color:var(--paper)] hover:bg-[color:var(--paper-2)]">
                            <svg class="w-10 h-10 text-[color:var(--muted)]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                            <span class="text-sm mt-2 text-[color:var(--ink-2)]">View PDF</span>
                        </a>

                        @else
                        <a href="{{ asset($path) }}" download
                            class="flex flex-col items-center justify-center h-40 border border-[color:var(--hairline)] rounded-lg bg-[color:var(--paper)] hover:bg-[color:var(--paper-2)]">
                            <svg class="w-10 h-10 text-[color:var(--muted)]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 01-6.364-6.364l10.94-10.94A3 3 0 1119.5 7.372L8.552 18.32m.009-.01l-.01.01m5.699-9.941l-7.81 7.81a1.5 1.5 0 002.112 2.13"/></svg>
                            <span class="text-sm mt-2 text-[color:var(--ink-2)]">{{ strtoupper($ext) }} File</span>
                        </a>
                        @endif
                        @endforeach
                    </div>
                    @else
                    <p class="text-sm text-[color:var(--muted)] py-8 text-center">
                        Tidak ada bukti yang dilampirkan.
                    </p>
                    @endif
                </div>
            </div>

        </div>

        {{-- ================= RIGHT SIDEBAR ================= --}}
        <div class="space-y-4">

            {{-- REPORTER INFO --}}
            <div class="cms-panel cms-rise" style="animation-delay:.10s">
                <div class="cms-panel-head">
                    <div>
                        <div class="cms-eyebrow">PELAPOR</div>
                        <div class="cms-panel-title">Informasi Pelapor</div>
                    </div>
                </div>
                <div class="cms-panel-body" style="padding:16px 20px">
                    <dl class="space-y-3 text-sm">
                        <div>
                            <dt class="text-xs font-medium text-[color:var(--muted)] mb-1">Nama</dt>
                            <dd class="font-medium text-[color:var(--ink)]">{{ $report->nama_lengkap }}</dd>
                        </div>

                        <div>
                            <dt class="text-xs font-medium text-[color:var(--muted)] mb-1">NIK</dt>
                            <dd class="font-medium text-[color:var(--ink)] font-mono-c">{{ $report->nik }}</dd>
                        </div>

                        <div>
                            <dt class="text-xs font-medium text-[color:var(--muted)] mb-1">Jenis Kelamin</dt>
                            <dd class="font-medium text-[color:var(--ink)]">{{ $report->jenis_kelamin }}</dd>
                        </div>

                        <div>
                            <dt class="text-xs font-medium text-[color:var(--muted)] mb-1">No HP</dt>
                            <dd class="font-medium text-[color:var(--ink)] font-mono-c">{{ $report->no_hp }}</dd>
                        </div>

                        <div>
                            <dt class="text-xs font-medium text-[color:var(--muted)] mb-1">Email</dt>
                            <dd class="font-medium text-[color:var(--ink)] break-all">{{ $report->email }}</dd>
                        </div>

                        <div>
                            <dt class="text-xs font-medium text-[color:var(--muted)] mb-1">Pekerjaan</dt>
                            <dd class="font-medium text-[color:var(--ink)]">{{ $report->pekerjaan }}</dd>
                        </div>

                        <div>
                            <dt class="text-xs font-medium text-[color:var(--muted)] mb-1">Alamat</dt>
                            <dd class="font-medium text-[color:var(--ink)]">{{ $report->alamat }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            {{-- ACTION PANEL --}}
            <div class="cms-panel cms-rise" style="animation-delay:.16s">
                <div class="cms-panel-head">
                    <div>
                        <div class="cms-eyebrow">AKSI</div>
                        <div class="cms-panel-title">Tindakan</div>
                    </div>
                </div>
                <div class="cms-panel-body" style="padding:16px 20px">
                    <div class="space-y-3">
                        @if ($report->status?->key === 'open')

                        @can('report.verify', $report)
                        <button wire:click="verify"
                            title="Periksa bukti singkat dan klik untuk memberi tanda laporan terverifikasi"
                            class="cms-btn cms-btn-leaf w-full justify-center">
                            Verifikasi Laporan
                        </button>
                        @else
                        <button disabled title="Anda tidak memiliki izin untuk memverifikasi laporan"
                            class="cms-btn cms-btn-leaf w-full justify-center opacity-60 cursor-not-allowed">
                            Verifikasi Laporan
                        </button>
                        @endcan

                        @can('report.reject', $report)
                        <button wire:click="rejected" title="Tandai laporan sebagai ditolak (beri alasan di timeline)"
                            class="cms-btn cms-btn-danger w-full justify-center">
                            Tolak Laporan
                        </button>
                        @else
                        <button disabled title="Anda tidak memiliki izin untuk menolak laporan"
                            class="cms-btn cms-btn-danger w-full justify-center opacity-60 cursor-not-allowed">
                            Tolak Laporan
                        </button>
                        @endcan

                        @endif

                        @if ($report->status?->key === 'verified')
                        <button wire:click="convertToCase"
                            title="Buat case dari laporan ini dan generate tugas sesuai kategori"
                            class="cms-btn cms-btn-primary w-full justify-center">
                            Buat Case
                        </button>

                        <p class="text-xs text-[color:var(--muted)] mt-2">Jika dikonfirmasi: sistem akan membuat case dan
                            men-generate tugas otomatis berdasarkan template kategori.</p>
                        @endif
                    </div>

                    @if (session('success'))
                    <p class="mt-4 text-sm font-medium text-[color:var(--ok)]">
                        {{ session('success') }}
                    </p>
                    @endif
                </div>
            </div>

        </div>
    </div>

</div>