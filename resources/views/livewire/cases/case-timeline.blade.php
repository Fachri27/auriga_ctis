<div>
    <div x-data="{ open: @entangle('open') }" x-show="open" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center">

        <!-- overlay -->
        <div class="fixed inset-0 bg-black/40" @click="open = false"></div>

        <!-- modal -->
        <div class="bg-white p-6 w-full max-w-lg rounded shadow relative z-50">

            <h2 class="text-lg font-bold mb-4">
                {{ $mode === 'create' ? 'Upload Timeline' : 'Edit Timeline' }}
            </h2>

            {{-- title --}}
            <div class="mb-4">
                <label class="text-sm">Note</label>
                <input wire:model="notes" class="border w-full px-3 py-2">
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