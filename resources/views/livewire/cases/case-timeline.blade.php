<div>
    <div x-data="{ open: @entangle('open') }" x-show="open" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center">

        <!-- overlay -->
        <div class="fixed inset-0 bg-black/40" @click="open = false"></div>

        <!-- modal -->
        <div class="cms-panel w-full max-w-lg relative z-50" @click.away="open = false">
            <div class="cms-panel-head">
                <div>
                    <div class="cms-eyebrow">Linimasa</div>
                    <h2 class="cms-panel-title">
                        {{ $mode === 'create' ? 'Tambah Entri' : 'Edit Entri' }}
                    </h2>
                </div>
                <button @click="open=false" class="cms-btn cms-btn-ghost">Tutup</button>
            </div>

            <div class="cms-panel-body" style="padding:16px 20px">
                {{-- note --}}
                <div class="mb-3">
                    <label class="text-xs font-medium text-[color:var(--muted)] mb-1.5 block">Catatan</label>
                    <input wire:model="notes" class="cms-input w-full">
                </div>

                {{-- buttons --}}
                <div class="flex justify-between mt-4">

                    {{-- delete --}}
                    @if($mode === 'edit')
                    <button wire:click="delete" class="cms-btn cms-btn-danger"
                        onclick="return confirm('Hapus entri ini?')">
                        Hapus
                    </button>
                    @endif

                    <div class="flex gap-2 ml-auto">
                        <button @click="open=false" class="cms-btn cms-btn-ghost">Batal</button>

                        <button wire:click="save" class="cms-btn cms-btn-leaf">
                            {{ $mode === 'create' ? 'Unggah' : 'Simpan' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>