<div>
    <div>
        <div class="max-w-7xl mx-auto px-6 py-6 space-y-4 cms-rise" style="animation-delay:.04s">
            <div class="flex items-center justify-between border-b border-[color:var(--hairline)] pb-3">
                <div>
                    <div class="cms-eyebrow">TASKS</div>
                    <h1 class="text-xl font-semibold text-[color:var(--ink)] tracking-tight mt-0.5">Task List</h1>
                </div>
                <div class="flex items-center gap-3">
                    <div class="md:flex gap-3">
                        <input wire:model.debounce.300ms="search" placeholder="Search task..." class="cms-input" />
                        <select wire:change="onProcessChange($event.target.value)" class="cms-input">
                            <option value="">All processes</option>
                            @foreach($processes as $proc)
                            <option value="{{ $proc->id }}">{{ $proc->translation('id')?->name ?? $proc->slug }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex items-center gap-3">
                        <button @click="$dispatch('open-task-modal')" class="cms-btn cms-btn-leaf">
                            + New Task
                        </button>
                    </div>
                </div>
            </div>

            @if(session('success'))
            <div class="px-4 py-3 rounded-xl bg-[color:var(--ok-soft)] text-[color:var(--ok)] border border-[color:var(--hairline)] text-sm">
                {{ session('success') }}
            </div>
            @endif

            <div class="cms-panel">
                <div class="overflow-x-auto">
                    <table class="cms-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Process</th>
                                <th>Name (ID)</th>
                                <th>Due (days)</th>
                                <th>Req</th>
                                <th class="text-right w-36">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tasks as $index => $task)
                            <tr>
                                <td class="num">{{ ($tasks->currentPage() - 1) * $tasks->perPage() + $index + 1 }}</td>
                                <td>{{ $task->process?->translation('id')?->name ?? $task->process?->slug }}</td>
                                <td>
                                    @if($editId === $task->id)
                                    <input wire:model.defer="inline.{{ $task->id }}.name" class="cms-input w-full" />
                                    @else
                                    <span>{{ $task->translation('id')?->name ?? '-' }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($editId === $task->id)
                                    <input type="number" wire:model.defer="inline.{{ $task->id }}.due_days" class="cms-input w-20" />
                                    @else
                                    <span class="num">{{ $task->due_days ?? '-' }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($editId === $task->id)
                                    <input type="checkbox" wire:model.defer="inline.{{ $task->id }}.is_required" />
                                    @else
                                    @if($task->is_required)
                                        <x-internal.badge variant="ok" size="sm">Yes</x-internal.badge>
                                    @else
                                        <x-internal.badge variant="default" size="sm">No</x-internal.badge>
                                    @endif
                                    @endif
                                </td>
                                <td class="text-right">
                                    <div class="flex justify-end gap-2 text-xs font-semibold">
                                        @if($editId === $task->id)
                                        <button wire:click="saveInline({{ $task->id }})" class="cms-btn cms-btn-leaf">Save</button>
                                        <button wire:click="cancelInlineEdit" class="cms-btn cms-btn-ghost">Cancel</button>
                                        @else
                                        <button wire:click="startInlineEdit({{ $task->id }})" class="cms-btn cms-btn-ghost">Edit</button>
                                        <a href="{{ route('task.edit', $task->id) }}" class="cms-btn cms-btn-ghost">Open</a>
                                        <button onclick="confirm('Delete this task?') || event.stopImmediatePropagation()" wire:click="deleteTask({{ $task->id }})" class="cms-btn cms-btn-danger">Delete</button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="px-5 py-4 border-t border-[color:var(--hairline)]">
                    {{ $tasks->links() }}
                </div>
            </div>

            <livewire:tasks.task-modal />
        </div>

    </div>

</div>