<div>
    <div class="max-w-2xl mx-auto p-7">

        <h2 class="text-xl font-semibold mb-6">Edit Task Requirement</h2>

        <!-- TASK -->
        <div class="mb-6">
            <label class="block text-sm mb-1">Task</label>
            <select wire:model="task_id" class="w-full border px-3 py-2">
                <option value="">Select task…</option>
                @foreach($tasks as $t)
                <option value="{{ $t->id }}">
                    {{ $t->translation('id')->name ?? 'Task #'.$t->id }}
                </option>
                @endforeach
            </select>
            @error('task_id') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>

        <!-- NAME -->
        <div class="mb-6">
            <label class="block text-sm mb-1">Field Name</label>
            <input wire:model="name" class="w-full border px-3 py-2">
            @error('name') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>

        <!-- FIELD TYPE -->
        <div class="mb-6">
            <label class="text-sm block mb-1">Field Type</label>
            <select wire:model="field_type" class="w-full border px-3 py-2">
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
        <div class="mb-6" wire:key="options-edit-{{ $field_type }}">
            <label class="block mb-1 text-sm">Options</label>

            @foreach($options as $i => $opt)
            <div class="flex gap-2 mb-2">
                <input class="flex-1 border px-3 py-2" wire:model="options.{{ $i }}">
                <button wire:click="removeOption({{ $i }})" class="px-3 py-2 border">×</button>
            </div>
            @endforeach

            <button wire:click="addOption" class="px-4 py-1 bg-black text-white text-sm">
                + Add Option
            </button>
        </div>
        @endif

        <!-- REQUIRED + SAVE -->
        <div class="flex items-center justify-between pt-4 border-t">

            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" wire:model="is_required">
                Required
            </label>

            <button wire:click="save" class="px-5 py-2 bg-black text-white">
                Update
            </button>

        </div>

    </div>
</div>