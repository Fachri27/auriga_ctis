<div>
    <div x-data="{ open: @entangle('open') }" x-show="open" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center">

        <!-- overlay -->
        <div class="fixed inset-0 bg-black/40" @click="open = false"></div>

        <!-- modal -->
        <div class="bg-white p-6 w-full max-w-lg rounded shadow relative z-50">

            <h2 class="text-lg font-bold mb-4">
                {{ $mode === 'create' ? 'Upload Document' : 'Edit Document' }}
            </h2>

            {{-- select process --}}
            <div class="mb-4">
                <label class="text-sm">Related Process (optional)</label>
                <select wire:model="process_id" class="w-full border px-3 py-2">
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
            <div class="mb-4">
                <label class="text-sm">Title (optional)</label>
                <input wire:model="title" class="border w-full px-3 py-2">
            </div>

            {{-- EXISTING FILE PREVIEW --}}
            @if($mode === 'edit' && $existing_file)
            <div class="mb-4 border p-3 rounded bg-gray-50">
                <p class="font-semibold mb-2">Current File:</p>

                @if(Str::contains($existing_mime, 'image'))
                <img src="{{ asset($existing_file) }}" class="max-w-full rounded">
                @else
                <a href="{{ asset($existing_file) }}" target="_blank" class="text-blue-600 underline">
                    Open File
                </a>
                @endif
            </div>
            @endif

            {{-- upload new file --}}
            <div class="mb-4">
                <label class="text-sm">
                    {{ $mode === 'edit' ? 'Replace File (optional)' : 'Choose File' }}
                </label>
                <input type="file" wire:model="file" class="border w-full px-3 py-2">
                @error('file') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
            </div>

            {{-- buttons --}}
            <div class="flex justify-between mt-6">

                {{-- delete --}}
                @if($mode === 'edit')
                <button wire:click="delete" class="px-4 py-2 bg-red-600 text-white">
                    Delete
                </button>
                @endif

                <div class="flex gap-3">
                    <button @click="open=false" class="px-4 py-2 border">Cancel</button>

                    <button wire:click="save" class="px-4 py-2 bg-black text-white">
                        {{ $mode === 'create' ? 'Upload' : 'Save Changes' }}
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>