<div>
    <div class="max-w-7xl mx-auto px-6 py-6 space-y-4 cms-rise" style="animation-delay:.04s">
        <div class="flex items-center justify-between border-b border-[color:var(--hairline)] pb-3">
            <div>
                <div class="cms-eyebrow">TASK REQUIREMENTS</div>
                <h1 class="text-xl font-semibold text-[color:var(--ink)] tracking-tight mt-0.5">Requirement List</h1>
            </div>
            <div class="flex items-center gap-3">
                <div class="md:flex gap-3">
                    <input wire:model.debounce.300ms="search" placeholder="Search task..." class="cms-input" />
                </div>

                <div class="flex items-center gap-3">
                    @can('task.create')
                    <button @click="$dispatch('open-requirement-modal')" class="cms-btn cms-btn-leaf">
                        + New Task
                    </button>
                    @endcan
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
                            <th class="w-10">#</th>
                            <th>Task</th>
                            <th>Name</th>
                            <th>Required</th>
                            <th>Field Type</th>
                            <th class="text-right w-32">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($requirements as $index => $req)
                        <tr>
                            <td class="num">{{ ($requirements->currentPage() - 1) * $requirements->perPage() + $index + 1 }}</td>
                            <td>{{ $req->task->translation('id')->name }}</td>
                            <td>
                                @if($editId === $req->id)
                                <input wire:model.defer="inline.{{ $req->id }}.name" class="cms-input w-full">
                                @else
                                <span>{{ $req->name }}</span>
                                @endif
                            </td>
                            <td>
                                @if($editId === $req->id)
                                <input type="checkbox" wire:model.defer="inline.{{ $req->id }}.is_required">
                                @else
                                @if($req->is_required)
                                    <x-internal.badge variant="ok" size="sm">Yes</x-internal.badge>
                                @else
                                    <x-internal.badge variant="default" size="sm">No</x-internal.badge>
                                @endif
                                @endif
                            </td>
                            <td>
                                @if($editId === $req->id)
                                <input type="text" wire:model.defer="inline.{{ $req->id }}.field_type" class="cms-input w-20">
                                @else
                                <span class="num">{{ $req->field_type }}</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <div class="flex justify-end gap-2 text-xs font-semibold">
                                    @if($editId === $req->id)
                                    <button wire:click="saveInline({{ $req->id }})" class="cms-btn cms-btn-leaf">Save</button>
                                    <button wire:click="cancelInlineUpdate" class="cms-btn cms-btn-ghost">Cancel</button>
                                    @else
                                    @can('task.update')
                                    <button wire:click="startInlineUpdate({{ $req->id }})" class="cms-btn cms-btn-ghost">Edit</button>
                                    @endcan
                                    @can('task.update')
                                    <a href="{{ route('taskreq.edit', $req->id) }}" class="cms-btn cms-btn-ghost">Open</a>
                                    @endcan
                                    @can('task.delete')
                                    <button onclick="confirm('Delete this task?') || event.stopImmediatePropagation()" wire:click="deletedTask({{ $req->id }})" class="cms-btn cms-btn-danger">Delete</button>
                                    @endcan
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-5 py-4 border-t border-[color:var(--hairline)]">
                {{ $requirements->links() }}
            </div>
        </div>

        @livewire('tasks.task-requirement-modal')
    </div>
</div>