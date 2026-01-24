<div>
    <div 
        x-data="{ open: @entangle('show') }"
        x-show="open"
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center"
        x-on:close-case-modal.window="open = false"
    >

        <!-- Overlay -->
        <div class="fixed inset-0 bg-black/40" @click="open = false"></div>

        <!-- Modal -->
        <div class="relative bg-white w-full max-w-3xl mx-4 rounded shadow-lg p-6 z-50 overflow-y-auto max-h-[90vh]">

            <h2 class="text-xl font-bold mb-4">
                {{ $caseId ? 'Edit Case' : 'Create New Case' }}
            </h2>

            <div class="grid grid-cols-2 gap-4 mb-4">

                <!-- Category -->
                <div>
                    <label class="text-sm">Category</label>
                    <select wire:model="category_id" class="w-full border px-3 py-2">
                        <option value="">Select category…</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name ?? $cat->slug }}</option>
                        @endforeach
                    </select>
                    @error('category_id') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
                </div>

                <!-- Status -->
                <div>
                    <label class="text-sm">Status</label>
                    <select wire:model="status_id" class="w-full border px-3 py-2">
                        <option value="">Select status…</option>
                        @foreach($statuses as $st)
                            <option value="{{ $st->id }}">{{ $st->name }}</option>
                        @endforeach
                    </select>
                    @error('status_id') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
                </div>

                <!-- Event Date -->
                <div>
                    <label class="text-sm">Event Date</label>
                    <input type="date" wire:model="event_date" class="w-full border px-3 py-2">
                    @error('event_date') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
                </div>

                <!-- Public -->
                <div class="flex items-center gap-2 mt-6">
                    <input type="checkbox" wire:model="is_public">
                    <label class="text-sm">Public Case</label>
                </div>
            </div>

            <!-- Location -->
            <h3 class="text-lg font-semibold mt-6 mb-2">Location</h3>
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="text-sm">Latitude</label>
                    <input wire:model="latitude" class="w-full border px-3 py-2" placeholder="-6.1234">
                </div>
                <div>
                    <label class="text-sm">Longitude</label>
                    <input wire:model="longitude" class="w-full border px-3 py-2" placeholder="106.9876">
                </div>
            </div>

            <!-- TRANSLATIONS -->
            <h3 class="text-lg font-semibold mt-6 mb-3">Case Information (ID)</h3>

            <div class="mb-3">
                <label class="text-sm">Title (ID)</label>
                <input wire:model="title_id" class="w-full border px-3 py-2">
                @error('title_id') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
            </div>

            <div class="mb-3">
                <label class="text-sm">Summary (ID)</label>
                <textarea wire:model="summary_id" class="w-full border px-3 py-2 h-20"></textarea>
            </div>

            <div class="mb-3">
                <label class="text-sm">Description (ID)</label>
                <textarea wire:model="desc_id" class="w-full border px-3 py-2 h-28"></textarea>
            </div>

            <h3 class="text-lg font-semibold mt-6 mb-3">Case Information (EN)</h3>

            <div class="mb-3">
                <label class="text-sm">Title (EN)</label>
                <input wire:model="title_en" class="w-full border px-3 py-2">
            </div>

            <div class="mb-3">
                <label class="text-sm">Summary (EN)</label>
                <textarea wire:model="summary_en" class="w-full border px-3 py-2 h-20"></textarea>
            </div>

            <div class="mb-3">
                <label class="text-sm">Description (EN)</label>
                <textarea wire:model="desc_en" class="w-full border px-3 py-2 h-28"></textarea>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-end gap-3 mt-6">
                <button @click="open = false" class="px-4 py-2 border">Cancel</button>

                <button 
                    wire:click="save"
                    class="px-5 py-2 bg-black text-white"
                >
                    {{ $caseId ? 'Update Case' : 'Create Case' }}
                </button>
            </div>

        </div>

    </div>
</div>
