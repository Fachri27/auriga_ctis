<div>
    <div class="max-w-5xl mx-auto px-6 py-6 space-y-4 cms-rise" style="animation-delay:.04s">

        {{-- PAGE HEADER --}}
        <div class="flex items-center justify-between border-b border-[color:var(--hairline)] pb-3">
            <div>
                <div class="cms-eyebrow">TASK REQUIREMENTS</div>
                <h1 class="text-xl font-semibold text-[color:var(--ink)] tracking-tight mt-0.5">Edit Task Requirement</h1>
            </div>
        </div>

        <div class="cms-panel">
            <div class="cms-panel-head">
                <div>
                    <div class="cms-eyebrow">FIELD</div>
                    <h2 class="cms-panel-title mt-1">Requirement</h2>
                </div>
            </div>
            <div class="cms-panel-body" style="padding:16px 20px">
                <div class="space-y-5">

                    <!-- TASK -->
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
                        <select wire:model="field_type" class="cms-input w-full">
                            <option value="text">Text Input</option>
                            <option value="textarea">Textarea</option>
                            <option value="number">Number</option>
                            <option value="date">Date</option>
                            <option value="file">File Upload</option>
                            <option value="select">Select Options</option>
                            <option value="checkbox">Checkbox Group</option>
                            <option value="radio">Radio Group</option>
                        </select>
                    </div>

                    <!-- OPTIONS -->
                    @if(in_array($field_type, ['select','checkbox','radio']))
                    <div wire:key="options-edit-{{ $field_type }}">
                        <label class="block text-xs font-medium text-[color:var(--muted)] mb-1.5">Options</label>

                        @foreach($options as $i => $opt)
                        <div class="flex gap-2 mb-2">
                            <input class="cms-input flex-1" wire:model="options.{{ $i }}">
                            <button wire:click="removeOption({{ $i }})" class="cms-btn cms-btn-ghost">×</button>
                        </div>
                        @endforeach

                        <button wire:click="addOption" class="cms-btn cms-btn-leaf">
                            + Add Option
                        </button>
                    </div>
                    @endif

                    <!-- REQUIRED + SAVE -->
                    <div class="flex items-center justify-between pt-4 border-t border-[color:var(--hairline)]">

                        <label class="flex items-center gap-2 text-sm text-[color:var(--ink)]">
                            <input type="checkbox" wire:model="is_required">
                            Required
                        </label>

                        <button wire:click="save" class="cms-btn cms-btn-leaf">
                            Update
                        </button>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>