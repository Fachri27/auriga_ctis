<div>
    <div x-data="{ open: false }" x-on:open-requirement-modal.window="open = true"
        x-on:close-requirement-modal.window="open = false" x-show="open" x-cloak>

        <!-- Overlay -->
        <div class="fixed inset-0 bg-black/30 z-40" @click="open = false"></div>

        <!-- Modal -->
        <div class="fixed inset-0 z-50 flex justify-center items-start p-6 overflow-y-auto">
            <div class="w-full max-w-2xl bg-white p-6 border">

                <!-- Header -->
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-semibold">Create Task Requirement</h2>
                    <button @click="open=false" class="text-gray-600 text-xl">×</button>
                </div>

                <!-- SELECT TASK -->
                <div class="mb-6">
                    <label class="block mb-1 text-sm">Task</label>
                    <select wire:model="task_id" class="w-full border px-3 py-2 bg-gray-50">
                        <option value="">Select task…</option>
                        @foreach($tasks as $t)
                        <option value="{{ $t->id }}">
                            {{ $t->translation('id')->name ?? 'Task #'.$t->id }}
                        </option>
                        @endforeach
                    </select>
                    @error('task_id') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
                </div>

                <!-- NAME -->
                <div class="mb-6">
                    <label class="block mb-1 text-sm">Field Name</label>
                    <input wire:model="name" class="w-full border px-3 py-2 bg-white">
                    @error('name') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
                </div>

                <!-- FIELD TYPE -->
                <div class="mb-6">
                    <label class="block mb-1 text-sm">Field Type</label>
                    <select wire:model.live="field_type" class="w-full border px-3 py-2 bg-gray-50">
                        <option value="">Select type…</option>
                        <option value="text">Text Input</option>
                        <option value="textarea">Textarea</option>
                        <option value="number">Number</option>
                        <option value="date">Date</option>
                        <option value="file">File Upload</option>
                        <option value="select">Select (Options)</option>
                        <option value="checkbox">Checkbox Group</option>
                        <option value="radio">Radio Group</option>
                    </select>
                    @error('field_type') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
                </div>

                <!-- OPTIONS FIELD -->
                @if(in_array($field_type, ['select','checkbox','radio']))
                <div class="mb-6" wire:key="options-field-{{ $field_type }}">
                    <label class="block mb-1 text-sm">Options (comma-separated)</label>
                    <input wire:model="options" class="w-full border px-3 py-2 bg-white"
                        placeholder="contoh: laki-laki, perempuan, lainnya">
                    @error('options') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
                </div>
                @endif

                <!-- REQUIRED + BUTTONS -->
                <div class="flex items-center justify-between pt-5 border-t">

                    <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" wire:model="is_required">
                        Required
                    </label>

                    <div class="flex gap-3">
                        <button wire:click="save" class="px-5 py-2 bg-black text-white">
                            Save
                        </button>
                        <button @click="open=false" class="px-5 py-2 border">
                            Cancel
                        </button>
                    </div>

                </div>

            </div>
        </div>
    </div>
</div>