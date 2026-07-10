<div>
    <div>
        <div class="max-w-7xl mx-auto p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="md:flex gap-3">
                    <input wire:model.debounce.300ms="search" placeholder="Search task..." class="px-3 py-2 border border-gray-200 rounded-lg text-sm" />
                    <select wire:change="onProcessChange($event.target.value)" class="px-3 py-2 border border-gray-200 rounded-lg text-sm">
                        <option value="">All processes</option>
                        @foreach($processes as $proc)
                        <option value="{{ $proc->id }}">{{ $proc->translation('id')?->name ?? $proc->slug }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center gap-3">
                    <button @click="$dispatch('open-task-modal')" class="px-4 py-2 bg-gray-900 text-white rounded-lg text-sm font-semibold hover:bg-gray-700 transition-colors">
                        + New Task
                    </button>
                </div>
            </div>

            @if(session('success'))
            <div class="p-3 bg-green-50 text-green-700 border border-green-200 rounded-lg mb-6 text-sm">
                {{ session('success') }}
            </div>
            @endif

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider border-b border-gray-100">
                            <tr>
                                <th class="px-5 py-3.5 text-left font-semibold">#</th>
                                <th class="px-5 py-3.5 text-left font-semibold">Process</th>
                                <th class="px-5 py-3.5 text-left font-semibold">Name (ID)</th>
                                <th class="px-5 py-3.5 text-left font-semibold">Due (days)</th>
                                <th class="px-5 py-3.5 text-left font-semibold">Req</th>
                                <th class="px-5 py-3.5 text-right font-semibold w-36">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach($tasks as $index => $task)
                            <tr class="hover:bg-gray-50/70 transition-colors">
                                <td class="px-5 py-4 font-semibold text-gray-900">{{ ($tasks->currentPage() - 1) * $tasks->perPage() + $index + 1 }}</td>
                                <td class="px-5 py-4 text-gray-700">{{ $task->process?->translation('id')?->name ?? $task->process?->slug }}</td>
                                <td class="px-5 py-4">
                                    @if($editId === $task->id)
                                    <input wire:model.defer="inline.{{ $task->id }}.name" class="w-full border border-gray-200 rounded px-2 py-1 text-sm" />
                                    @else
                                    <span class="text-gray-700">{{ $task->translation('id')?->name ?? '-' }}</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    @if($editId === $task->id)
                                    <input type="number" wire:model.defer="inline.{{ $task->id }}.due_days" class="w-20 border border-gray-200 rounded px-2 py-1 text-sm" />
                                    @else
                                    <span class="text-gray-600">{{ $task->due_days ?? '-' }}</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    @if($editId === $task->id)
                                    <input type="checkbox" wire:model.defer="inline.{{ $task->id }}.is_required" class="rounded" />
                                    @else
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold {{ $task->is_required ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                        {{ $task->is_required ? 'Yes' : 'No' }}
                                    </span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <div class="flex justify-end gap-2 text-xs font-semibold">
                                        @if($editId === $task->id)
                                        <button wire:click="saveInline({{ $task->id }})" class="text-green-600 hover:text-green-800 transition-colors">Save</button>
                                        <button wire:click="cancelInlineEdit" class="text-gray-600 hover:text-gray-800 transition-colors">Cancel</button>
                                        @else
                                        <button wire:click="startInlineEdit({{ $task->id }})" class="text-blue-600 hover:text-blue-900 transition-colors">Edit</button>
                                        <a href="{{ route('task.edit', $task->id) }}" class="text-indigo-600 hover:text-indigo-900 transition-colors">Open</a>
                                        <button onclick="confirm('Delete this task?') || event.stopImmediatePropagation()" wire:click="deleteTask({{ $task->id }})" class="text-red-600 hover:text-red-900 transition-colors">Delete</button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="px-5 py-4 border-t border-gray-100">
                    {{ $tasks->links() }}
                </div>
            </div>

            <livewire:tasks.task-modal />
        </div>

    </div>

</div>
