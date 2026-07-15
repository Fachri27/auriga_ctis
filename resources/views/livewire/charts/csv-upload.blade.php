<div class="max-w-3xl mx-auto px-6 py-6 space-y-4">
    <div class="border-b border-[color:var(--hairline)] pb-3 flex items-center justify-between">
        <div>
            <div class="cms-eyebrow">DATA</div>
            <h1 class="text-xl font-semibold text-[color:var(--ink)] tracking-tight mt-0.5">Upload Data Chart</h1>
        </div>
    </div>

    @if (session('success'))
        <div class="cms-pill cms-pill-ok">
            <span class="dot"></span>
            {{ session('success') }}
        </div>
    @endif

    <form wire:submit="import" enctype="multipart/form-data" class="space-y-4">
        <div class="cms-panel">
            <div class="cms-panel-head">
                <div>
                    <div class="cms-panel-title">Form Import</div>
                    <div class="cms-panel-sub">Isi metadata lalu unggah file CSV</div>
                </div>
            </div>
            <div class="cms-panel-body" style="padding:16px 20px">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-[color:var(--muted)] mb-1.5">Dataset</label>
                        <input type="text" wire:model="dataset"
                            class="cms-input w-full">
                        @error('dataset') <span class="text-xs text-[color:var(--danger)] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-[color:var(--muted)] mb-1.5">Tahun (opsional, isi jika data spesifik tahun)</label>
                        <input type="number" wire:model="year" min="2000" max="2099" placeholder="cth: 2024"
                            class="cms-input w-full">
                        @error('year') <span class="text-xs text-[color:var(--danger)] mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="mt-4">
                    <label class="block text-xs font-medium text-[color:var(--muted)] mb-1.5">File CSV</label>
                    <input type="file" wire:model="file" accept=".csv"
                        class="block w-full text-sm text-[color:var(--muted)] file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-[color:var(--paper-2)] file:text-[color:var(--ink)] hover:file:bg-[color:var(--hairline)]">
                    @error('file') <span class="text-xs text-[color:var(--danger)] mt-1 block">{{ $message }}</span> @enderror
                    <div wire:loading wire:target="file" class="text-xs text-[color:var(--muted)] mt-1.5">Loading preview...</div>
                </div>

                @if ($preview)
                    <div class="mt-4 cms-panel" style="border-color:var(--hairline)">
                        <div class="cms-panel-head">
                            <div class="cms-panel-title text-sm">Preview ({{ $preview['total'] }} baris)</div>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="cms-table">
                                <thead>
                                    <tr>
                                        @foreach ($preview['header'] as $col)
                                            <th>{{ $col }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($preview['rows'] as $row)
                                        <tr>
                                            @foreach ($row as $cell)
                                                <td>{{ $cell }}</td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="cms-btn cms-btn-leaf">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            Import ke Database
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </form>

    <div class="cms-panel cms-rise" style="animation-delay:.10s">
        <div class="cms-panel-head">
            <div>
                <div class="cms-panel-title">Dataset Tersimpan</div>
                <div class="cms-panel-sub">Daftar dataset yang sudah terimport</div>
            </div>
        </div>
        <div class="cms-panel-body" style="padding:0">
            <div class="overflow-x-auto">
                <table class="cms-table">
                    <thead>
                        <tr>
                            <th>Dataset</th>
                            <th>Baris</th>
                            <th>Tahun</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($datasets as $ds)
                            <tr wire:key="ds-{{ $ds->dataset }}">
                                <td class="font-medium">{{ $ds->dataset }}</td>
                                <td class="num">{{ number_format($ds->total) }}</td>
                                <td>{{ $ds->years > 0 ? $ds->years . ' tahun' : '-' }}</td>
                                <td class="text-right">
                                    <div class="inline-flex gap-2">
                                        <button wire:click="editDataset('{{ $ds->dataset }}')"
                                            class="cms-btn cms-btn-ghost" style="padding:4px 10px">
                                            Edit
                                        </button>
                                        <button wire:confirm="Hapus dataset '{{ $ds->dataset }}'?"
                                            wire:click="deleteDataset('{{ $ds->dataset }}')"
                                            class="cms-btn cms-btn-danger" style="padding:4px 10px">
                                            Hapus
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-8 text-sm text-[color:var(--muted)]">Belum ada data</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>