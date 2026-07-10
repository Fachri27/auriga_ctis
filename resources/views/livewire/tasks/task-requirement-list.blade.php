<div>
    <div class="max-w-4xl mx-auto p-6">
        <div class="flex items-center justify-between mb-4">
            <div class="md:flex gap-3">
                <input wire:model.debounce.300ms="search" placeholder="Search task..." class="px-3 py-2 border border-gray-200 rounded-lg text-sm" />
            </div>

            <div class="flex items-center gap-3">
                @can('task.create')
                <button @click="$dispatch('open-requirement-modal')" class="px-4 py-2 bg-gray-900 text-white rounded-lg text-sm font-semibold hover:bg-gray-700 transition-colors">
                    + New Task
                </button>
                @endcan
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
                            <th class="px-5 py-3.5 text-left font-semibold w-10">#</th>
                            <th class="px-5 py-3.5 text-left font-semibold">Task</th>
                            <th class="px-5 py-3.5 text-left font-semibold">Name</th>
                            <th class="px-5 py-3.5 text-left font-semibold">Required</th>
                            <th class="px-5 py-3.5 text-left font-semibold">Field Type</th>
                            <th class="px-5 py-3.5 text-right font-semibold w-32">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($requirements as $index => $req)
                        <tr class="hover:bg-gray-50/70 transition-colors">
                            <td class="px-5 py-4 font-semibold text-gray-900">{{ ($requirements->currentPage() - 1) * $requirements->perPage() + $index + 1 }}</td>
                            <td class="px-5 py-4 text-gray-700">{{ $req->task->translation('id')->name }}</td>
                            <td class="px-5 py-4">
                                @if($editId === $req->id)
                                <input wire:model.defer="inline.{{ $req->id }}.name" class="w-full border border-gray-200 rounded px-2 py-1 text-sm">
                                @else
                                <span class="text-gray-700">{{ $req->name }}</span>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                @if($editId === $req->id)
                                <input type="checkbox" wire:model.defer="inline.{{ $req->id }}.is_required" class="rounded">
                                @else
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold {{ $req->is_required ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                    {{ $req->is_required ? 'Yes' : 'No' }}
                                </span>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                @if($editId === $req->id)
                                <input type="text" wire:model.defer="inline.{{ $req->id }}.field_type" class="w-20 border border-gray-200 rounded px-2 py-1 text-sm">
                                @else
                                <span class="text-gray-600">{{ $req->field_type }}</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-right">
                                <div class="flex justify-end gap-2 text-xs font-semibold">
                                    @if($editId === $req->id)
                                    <button wire:click="saveInline({{ $req->id }})" class="text-green-600 hover:text-green-800 transition-colors">Save</button>
                                    <button wire:click="cancelInlineUpdate" class="text-gray-600 hover:text-gray-800 transition-colors">Cancel</button>
                                    @else
                                    @can('task.update')
                                    <button wire:click="startInlineUpdate({{ $req->id }})" class="text-blue-600 hover:text-blue-900 transition-colors">Edit</button>
                                    @endcan
                                    @can('task.update')
                                    <a href="{{ route('taskreq.edit', $req->id) }}" class="text-indigo-600 hover:text-indigo-900 transition-colors">Open</a>
                                    @endcan
                                    @can('task.delete')
                                    <button onclick="confirm('Delete this task?') || event.stopImmediatePropagation()" wire:click="deletedTask({{ $req->id }})" class="text-red-600 hover:text-red-900 transition-colors">Delete</button>
                                    @endcan
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-5 py-4 border-t border-gray-100">
                {{ $requirements->links() }}
            </div>
        </div>

        @livewire('tasks.task-requirement-modal')
    </div>
</div>
