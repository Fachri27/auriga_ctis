<div>
    {{-- MODAL --}}
    <div x-data="{ open: @entangle('open') }" x-show="open" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center">

        <div class="fixed inset-0 bg-black/40" @click="open = false"></div>

        <div class="bg-white p-6 w-full max-w-lg rounded shadow relative z-50">

            <h2 class="text-lg font-bold mb-4">
                {{ $mode === 'create' ? 'Add Actor' : 'Edit Actor' }}
            </h2>

            {{-- TYPE --}}
            <div class="mb-4">
                <label class="text-sm">Actor Type</label>
                <select class="border w-full px-3 py-2" wire:model="type">
                    <option value="citizen">Citizen</option>
                    <option value="corporate">Corporate</option>
                    <option value="government">Government</option>
                </select>
            </div>

            {{-- NAME --}}
            <div class="mb-4">
                <label class="text-sm">Name</label>
                <input class="border w-full px-3 py-2" wire:model="name">
            </div>

            {{-- DESC --}}
            <div class="mb-4">
                <label class="text-sm">Description</label>
                <textarea class="border w-full px-3 py-2" wire:model="description"></textarea>
            </div>

            {{-- METADATA --}}
            <div class="mb-4">
                <label class="text-sm">Metadata (JSON)</label>
                <textarea class="border w-full px-3 py-2" wire:model="metadata"
                    placeholder='{"role":"witness"}'></textarea>
            </div>

            <div class="flex justify-end gap-3">
                <button @click="open=false" class="px-4 py-2 border">Cancel</button>
                <button wire:click="save" class="px-4 py-2 bg-black text-white">Save</button>
            </div>

        </div>
    </div>

</div>