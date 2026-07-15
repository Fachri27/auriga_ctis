<div class="cms-rise">
    <div x-data="{ open: false }" class="relative">

        <div class="flex justify-between items-center mb-4 pb-3 border-b border-[color:var(--hairline)]">
            <div>
                <div class="cms-eyebrow">Penanganan</div>
                <h2 class="text-base font-semibold text-[color:var(--ink)] tracking-tight mt-0.5">Tugas</h2>
            </div>
            <button @click="open = true" class="cms-btn cms-btn-leaf">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                Tambah Tugas
            </button>
        </div>

        <!-- MODAL -->
        <div x-show="open" x-cloak class="fixed inset-0 flex items-center justify-center bg-black/50 z-50">

            <div @click.away="open = false" class="cms-panel w-full max-w-lg relative z-50">
                <div class="cms-panel-head">
                    <div>
                        <div class="cms-eyebrow">Tugas</div>
                        <h3 class="cms-panel-title">Tambah Tugas Manual</h3>
                    </div>
                    <button @click="open = false" class="cms-btn cms-btn-ghost">Tutup</button>
                </div>

                <!-- FORM -->
                <div class="cms-panel-body" style="padding:16px 20px">

                    <div class="mb-3">
                        <label class="text-xs font-medium text-[color:var(--muted)] mb-1.5 block">Judul Tugas</label>
                        <input type="text" wire:model="title" placeholder="Judul Tugas"
                            class="cms-input w-full">

                        @error('title')
                        <span class="text-xs text-[color:var(--danger)] mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="text-xs font-medium text-[color:var(--muted)] mb-1.5 block">Deskripsi</label>
                        <textarea wire:model="description" placeholder="Deskripsi" class="cms-input w-full" rows="3"></textarea>
                    </div>

                    <div class="mb-4">
                        <label class="text-xs font-medium text-[color:var(--muted)] mb-1.5 block">Lampiran</label>
                        <input type="file" wire:model="files" multiple class="cms-input w-full">

                        @error('files.*')
                        <span class="text-xs text-[color:var(--danger)] mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="flex justify-end gap-2">
                        <button @click="open = false" class="cms-btn cms-btn-ghost">
                            Batal
                        </button>

                        <button wire:click="createAction" @click="open = false"
                            class="cms-btn cms-btn-leaf">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            Simpan
                        </button>
                    </div>
                </div>
            </div>
        </div>


        <!-- LIST ACTIONS -->
        <div class="space-y-3 mt-4">
            @forelse($actions as $action)
            <div class="cms-panel">
                <div class="cms-panel-body" style="padding:14px 18px">
                    <div class="flex justify-between items-start gap-3">
                        <h4 class="text-sm font-semibold text-[color:var(--ink)]">
                            {{ $action->title }}
                        </h4>
                        <div class="flex gap-2 flex-shrink-0">
                            <button wire:click="editAction({{ $action->id }})"
                                class="cms-btn cms-btn-ghost">Edit</button>
                            <button wire:click="deleteAction({{ $action->id }})"
                                class="cms-btn cms-btn-danger"
                                onclick="return confirm('Hapus tugas ini?')">Hapus</button>
                        </div>
                    </div>

                    <p class="text-sm text-[color:var(--muted)] mt-2 break-words">
                        {{ $action->description }}
                    </p>

                    @if($action->due_date)
                    <p class="text-xs text-[color:var(--muted)] mt-2 flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 5h14a2 2 0 012 2v12a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2z" /></svg>
                        Tenggat: {{ \Carbon\Carbon::parse($action->due_date)->format('d M Y') }}
                    </p>
                    @endif

                    <!-- FILES -->
                    @if($action->files->count())
                    <div class="mt-3 pt-3 border-t border-[color:var(--hairline)]">
                        <p class="text-[10px] font-mono uppercase tracking-widest text-[color:var(--muted)] mb-2">Dokumen</p>
                        <div class="space-y-1">
                            @foreach($action->files as $file)
                            <div class="text-sm">
                                <a href="{{ asset('storage/'.$file->file_path) }}" target="_blank" class="text-[color:var(--leaf-deep)] hover:underline">
                                    {{ $file->file_name }}
                                </a>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            @empty
            <p class="py-10 text-sm text-[color:var(--muted)] text-center">Belum ada tugas manual.</p>
            @endforelse
        </div>

    </div>

</div>