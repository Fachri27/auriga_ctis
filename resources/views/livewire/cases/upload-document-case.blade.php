<div>
    <div x-data="{ open: @entangle('open') }" x-show="open" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center">

        <!-- overlay -->
        <div class="fixed inset-0 bg-black/40" @click="open = false"></div>

        <!-- modal -->
        <div class="cms-panel relative z-50 w-full max-w-lg mx-4 max-h-[90vh] overflow-y-auto">

            <!-- HEADER -->
            <div class="cms-panel-head">
                <div>
                    <div class="cms-eyebrow">DOCUMENTS</div>
                    <h2 class="cms-panel-title">
                        {{ $mode === 'create' ? 'Upload Document' : 'Edit Document' }}
                    </h2>
                </div>
                <button class="cms-btn cms-btn-ghost" @click="open = false">Close</button>
            </div>

            <!-- BODY -->
            <div class="cms-panel-body space-y-4" style="padding:20px">

                {{-- select process --}}
                <div>
                    <label class="block text-xs font-medium text-[color:var(--muted)] mb-1.5">Related Process (optional)</label>
                    <select wire:model="process_id" class="cms-input w-full">
                        <option value="">General Document</option>

                        @foreach($processes as $p)
                        <option value="{{ $p->id }}" @selected($process_id==$p->id)
                            >
                            {{ $p->id }} — Order {{ $p->order_no }}
                        </option>

                        @endforeach
                    </select>
                </div>

                {{-- title --}}
                <div>
                    <label class="block text-xs font-medium text-[color:var(--muted)] mb-1.5">Title (optional)</label>
                    <input wire:model="title" class="cms-input w-full">
                </div>

                {{-- EXISTING FILE PREVIEW --}}
                @if($mode === 'edit' && $existing_file)
                <div class="border border-[color:var(--hairline)] rounded-[10px] p-3 bg-[color:var(--paper)]">
                    <p class="text-xs font-medium text-[color:var(--ink)] mb-2">Current File</p>

                    @if(Str::contains($existing_mime, 'image'))
                    <img src="{{ asset($existing_file) }}" class="max-w-full rounded">
                    @else
                    <a href="{{ asset($existing_file) }}" target="_blank" class="text-[color:var(--leaf-deep)] underline">
                        Open File
                    </a>
                    @endif
                </div>
                @endif

                {{-- upload new file --}}
                <div>
                    <label class="block text-xs font-medium text-[color:var(--muted)] mb-1.5">
                        {{ $mode === 'edit' ? 'Replace File (optional)' : 'Choose File' }}
                    </label>
                    <input type="file" wire:model="file" class="cms-input w-full">
                    @error('file') <p class="text-sm text-[color:var(--danger)]">{{ $message }}</p> @enderror
                </div>

                {{-- buttons --}}
                <div class="flex justify-between items-center pt-2 border-t border-[color:var(--hairline)]">

                    {{-- delete --}}
                    <div>
                        @if($mode === 'edit')
                        @can('case.document.delete')
                        <button wire:click="delete" class="cms-btn cms-btn-danger">
                            Delete
                        </button>
                        @endcan
                        @endif
                    </div>

                    <div class="flex gap-2">
                        <button @click="open=false" class="cms-btn cms-btn-ghost">Cancel</button>

                        <button wire:click="save" class="cms-btn cms-btn-leaf">
                            {{ $mode === 'create' ? 'Upload' : 'Save Changes' }}
                        </button>
                    </div>
                </div>

            </div>

        </div>
    </div>
</div>