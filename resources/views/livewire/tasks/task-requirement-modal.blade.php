<div>
    <div x-data="{ open: false }" x-on:open-requirement-modal.window="open = true"
        x-on:close-requirement-modal.window="open = false" x-show="open" x-cloak>

        <!-- Overlay -->
        <div class="fixed inset-0 bg-black/40 z-40" @click="open = false"></div>

        <!-- Modal -->
        <div class="fixed inset-0 z-50 flex justify-center items-start p-6 overflow-y-auto">
            <div class="w-full max-w-2xl cms-panel">

                <!-- Header -->
                <div class="cms-panel-head">
                    <div>
                        <div class="cms-eyebrow">TASK REQUIREMENTS</div>
                        <h2 class="cms-panel-title mt-1">Create Task Requirement</h2>
                    </div>
                    <button @click="open=false" class="cms-btn cms-btn-ghost">Close</button>
                </div>

                <!-- Body -->
                <div class="cms-panel-body" style="padding:16px 20px">
                    <div class="space-y-5">

                        <!-- SELECT TASK -->
                        <div>
                            <label class="block text-xs font-medium text-[color:var(--muted)] mb-1.5">Task</label>
                            <select wire:model="task_id" class="cms-input w-full">
                                <option value="">Select task…</option>
                                @foreach($tasks as $t)
                                <option value="{{ $t->id }}">
                                    {{ $t->translation('id')->name ?? 'Task #'.$t->id }}
                                </option>
                                @endforeach
                            </select>
                            @error('task_id') <p class="text-[color:var(--danger)] text-sm mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- NAME -->
                        <div>
                            <label class="block text-xs font-medium text-[color:var(--muted)] mb-1.5">Field Name</label>
                            <input wire:model="name" class="cms-input w-full">
                            @error('name') <p class="text-[color:var(--danger)] text-sm mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- FIELD TYPE -->
                        <div>
                            <label class="block text-xs font-medium text-[color:var(--muted)] mb-1.5">Field Type</label>
                            <select wire:model.live="field_type" class="cms-input w-full">
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
                            @error('field_type') <p class="text-[color:var(--danger)] text-sm mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- OPTIONS FIELD -->
                        @if(in_array($field_type, ['select','checkbox','radio']))
                        <div wire:key="options-field-{{ $field_type }}">
                            <label class="block text-xs font-medium text-[color:var(--muted)] mb-1.5">Options (comma-separated)</label>
                            <input wire:model="options" class="cms-input w-full"
                                placeholder="contoh: laki-laki, perempuan, lainnya">
                            @error('options') <p class="text-[color:var(--danger)] text-sm mt-1">{{ $message }}</p> @enderror
                        </div>
                        @endif

                        <!-- REQUIRED + BUTTONS -->
                        <div class="flex items-center justify-between pt-4 border-t border-[color:var(--hairline)]">

                            <label class="flex items-center gap-2 text-sm text-[color:var(--ink)]">
                                <input type="checkbox" wire:model="is_required">
                                Required
                            </label>

                            <div class="flex gap-3">
                                <button @click="open=false" class="cms-btn cms-btn-ghost">
                                    Cancel
                                </button>
                                <button wire:click="save" class="cms-btn cms-btn-leaf">
                                    Save
                                </button>
                            </div>

                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>