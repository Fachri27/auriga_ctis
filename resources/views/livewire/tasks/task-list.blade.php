<div>
    <div>
        <div class="max-w-7xl mx-auto p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="md:flex gap-3">
                    <input wire:model.debounce.300ms="search" placeholder="Search task..." class="px-3 py-2 border" />
                    <select wire:change="onProcessChange($event.target.value)" class="px-3 py-2 border">
                        <option value="">All processes</option>
                        @foreach($processes as $proc)
                        <option value="{{ $proc->id }}">{{ $proc->translation('id')?->name ?? $proc->slug }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center gap-3">
                    @can('task.create')
                    <button @click="$dispatch('open-task-modal')" class="px-4 py-2 bg-black text-white">
                        + New Task
                    </button>
                    @endcan
                </div>
            </div>

            {{-- SUCCESS ALERT --}}
            @if(session('success'))
            <div class="p-3 bg-green-100 text-green-700 border border-green-300 mb-6">
                {{ session('success') }}
            </div>
            @endif  

            <div class="bg-white shadow-sm overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="p-3 text-left">#</th>
                            <th class="p-3 text-left">Process</th>
                            <th class="p-3 text-left">Name (ID)</th>
                            <th class="p-3 text-left">Due (days)</th>
                            <th class="p-3 text-left">Req</th>
                            <th class="p-3 text-right w-36">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y">
                        @foreach($tasks as $index => $task)
                        <tr class="hover:bg-gray-50">
                            <td class="p-3"> {{ ($tasks->currentPage() - 1) * $tasks->perPage() + $index + 1 }} </td>
                            <td class="p-3">{{ $task->process?->translation('id')?->name ?? $task->process?->slug }}
                            </td>

                            {{-- INLINE: name --}}
                            <td class="p-3">
                                @if($editId === $task->id)
                                <input wire:model.defer="inline.{{ $task->id }}.name" class="w-full border p-2" />
                                @else
                                {{ $task->translation('id')?->name ?? '-' }}
                                @endif
                            </td>

                            {{-- INLINE: due_days --}}
                            <td class="p-3 w-28">
                                @if($editId === $task->id)
                                <input type="number" wire:model.defer="inline.{{ $task->id }}.due_days"
                                    class="w-20 border p-1" />
                                @else
                                {{ $task->due_days ?? '-' }}
                                @endif
                            </td>

                            {{-- INLINE: is_required --}}
                            <td class="p-3 w-20">
                                @if($editId === $task->id)
                                <input type="checkbox" wire:model.defer="inline.{{ $task->id }}.is_required" />
                                @else
                                <span class="text-xs {{ $task->is_required ? 'text-green-700' : 'text-gray-500' }}">
                                    {{ $task->is_required ? 'Yes' : 'No' }}
                                </span>
                                @endif
                            </td>

                            <td class="px-6 py-4 text-right relative" x-data="{ open: false }">
                                <button @click="open = !open"
                                    class="text-gray-400 hover:text-gray-600 hover:scale-110 active:scale-95 transition">
                                    •••
                                </button>

                                <!-- Dropdown -->
                                <div x-show="open" @click.outside="open = false"
                                    x-transition:enter="transition ease-out duration-150"
                                    x-transition:enter-start="opacity-0 scale-95"
                                    x-transition:enter-end="opacity-100 scale-100"
                                    x-transition:leave="transition ease-in duration-100"
                                    x-transition:leave-start="opacity-100 scale-100"
                                    x-transition:leave-end="opacity-0 scale-95"
                                    class="relative right-0 mt-2 w-40 bg-white border border-gray-200 shadow-xl py-3 px-3 z-50 flex">


                                    @if($editId === $task->id)
                                    <button wire:click="saveInline({{ $task->id }})"
                                        class="text-green-600 mr-3">Save</button>
                                    <button wire:click="cancelInlineEdit" class="text-gray-600">Cancel</button>
                                    @else
                                    @can('task.update')
                                    <button wire:click="startInlineEdit({{ $task->id }})"
                                        class="text-blue-600 mr-3">Edit</button>
                                    @endcan

                                    @can('task.update')
                                    <a href="{{ route('task.edit', $task->id) }}" class="text-indigo-600 mr-3">Open</a>
                                    @endcan

                                    @can('task.delete')
                                    <button onclick="confirm('Delete this task?') || event.stopImmediatePropagation()"
                                        wire:click="deleteTask({{ $task->id }})" class="text-red-600">Delete</button>
                                    @endcan
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="p-4">
                    {{ $tasks->links() }}
                </div>
            </div>

            <livewire:tasks.task-modal />
        </div>

    </div>

</div>