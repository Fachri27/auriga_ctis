<div>
    {{-- MODAL --}}
    <div x-data="{ open: @entangle('open') }" x-show="open" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center p-4">

        <div class="fixed inset-0 bg-black/40" @click="open = false"></div>

        <div class="cms-panel w-full max-w-lg relative z-50">

            <div class="cms-panel-head">
                <div>
                    <div class="cms-eyebrow">{{ $mode === 'create' ? 'Tambah' : 'Ubah' }}</div>
                    <h2 class="cms-panel-title">{{ $mode === 'create' ? 'Tambah Aktor' : 'Ubah Aktor' }}</h2>
                </div>
            </div>

            <div class="cms-panel-body" style="padding:16px 20px">
                <div class="space-y-4">

                    {{-- TYPE --}}
                    <div>
                        <label class="block text-xs font-medium text-[color:var(--muted)] mb-1.5">Tipe Aktor</label>
                        <select class="cms-input w-full" wire:model="type">
                            <option value="citizen">Warga</option>
                            <option value="corporate">Korporasi</option>
                            <option value="government">Pemerintah</option>
                        </select>
                    </div>

                    {{-- NAME --}}
                    <div>
                        <label class="block text-xs font-medium text-[color:var(--muted)] mb-1.5">Nama</label>
                        <input class="cms-input w-full" wire:model="name">
                    </div>

                    {{-- DESC --}}
                    <div>
                        <label class="block text-xs font-medium text-[color:var(--muted)] mb-1.5">Deskripsi</label>
                        <textarea class="cms-input w-full" wire:model="description" rows="3"></textarea>
                    </div>

                    {{-- METADATA --}}
                    <div>
                        <label class="block text-xs font-medium text-[color:var(--muted)] mb-1.5">Metadata (JSON)</label>
                        <textarea class="cms-input w-full font-mono-c" wire:model="metadata" rows="3"
                            placeholder='{"role":"witness"}'></textarea>
                    </div>
                </div>
            </div>

            <div class="cms-panel-head" style="border-top:1px solid var(--hairline);border-bottom:none;justify-content:flex-end;gap:8px">
                <button @click="open=false" class="cms-btn cms-btn-ghost">Batal</button>
                <button wire:click="save" class="cms-btn cms-btn-leaf">Simpan</button>
            </div>

        </div>
    </div>

</div>