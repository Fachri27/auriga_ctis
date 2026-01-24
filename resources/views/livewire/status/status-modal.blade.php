<div>
    <div 
        x-data="{ open: @entangle('show') }"
        x-show="open"
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center"
        x-on:close-status-modal.window="open = false"
    >

        <!-- Overlay -->
        <div class="fixed inset-0 bg-black/40" @click="open = false"></div>

        <!-- Modal -->
        <div class="relative bg-white w-full max-w-3xl mx-4 rounded shadow-lg p-6 z-50 overflow-y-auto max-h-[90vh]">

            <h2 class="text-xl font-bold mb-4">
                {{ $statusId ? 'Edit Status' : 'Create New Status' }}
            </h2>

            <div class="grid grid-cols-2 gap-4 mb-4">
                <!-- Event Date -->
                <div>
                    <label class="text-sm">Key</label>
                    <input type="text" wire:model="key" class="w-full border px-3 py-2">
                    @error('key') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="text-sm">Name</label>
                    <input type="text" wire:model="name" class="w-full border px-3 py-2">
                    @error('name') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
                </div>
            </div>


            <!-- Action Buttons -->
            <div class="flex justify-end gap-3 mt-6">
                <button @click="open = false" class="px-4 py-2 border">Cancel</button>

                <button 
                    wire:click="save"
                    class="px-5 py-2 bg-black text-white"
                >
                    {{ $statusId ? 'Update Status' : 'Create Status' }}
                </button>
            </div>

        </div>

    </div>
</div>
