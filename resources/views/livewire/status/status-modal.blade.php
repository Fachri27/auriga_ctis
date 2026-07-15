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
        <div class="relative w-full max-w-3xl mx-4 z-50 overflow-y-auto max-h-[90vh]">
            <div class="cms-panel">
                <div class="cms-panel-head">
                    <div>
                        <div class="cms-eyebrow">{{ $statusId ? 'Edit' : 'Create' }}</div>
                        <h2 class="cms-panel-title">
                            {{ $statusId ? 'Edit Status' : 'Create New Status' }}
                        </h2>
                    </div>
                </div>

                <div class="cms-panel-body" style="padding:20px">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Key -->
                        <div>
                            <label class="block text-xs font-medium text-[color:var(--muted)] mb-1.5">Key</label>
                            <input type="text" wire:model="key" class="cms-input w-full">
                            @error('key') <p class="text-sm text-[color:var(--danger)] mt-1">{{ $message }}</p> @enderror
                        </div>
                        <!-- Name -->
                        <div>
                            <label class="block text-xs font-medium text-[color:var(--muted)] mb-1.5">Name</label>
                            <input type="text" wire:model="name" class="cms-input w-full">
                            @error('name') <p class="text-sm text-[color:var(--danger)] mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex justify-end gap-3 mt-6">
                        <button @click="open = false" class="cms-btn cms-btn-ghost">Cancel</button>

                        <button
                            wire:click="save"
                            class="cms-btn cms-btn-leaf"
                        >
                            {{ $statusId ? 'Update Status' : 'Create Status' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>