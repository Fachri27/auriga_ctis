<div>
    <div class="max-w-4xl mx-auto p-6">
        <div class="flex items-center justify-between mb-4">
            <div class="md:flex gap-3">
                <input wire:model.debounce.300ms="search" placeholder="Search task..." class="px-3 py-2 border" />
            </div>

            <div class="flex items-center gap-3">
                @can('task.create')
                <button @click="$dispatch('open-requirement-modal')" class="px-4 py-2 bg-black text-white">
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

        <div class="bg-white">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="p-3 text-left w-10">#</th>
                        <th class="p-3 text-left">Task</th>
                        <th class="p-3 text-left">Name</th>
                        <th class="p-3 text-left w-20">Required</th>
                        <th class="p-3 text-left w-20">Field Type</th>
                        <th class="p-3 text-right w-32">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($requirements as $index => $req)
                    <tr class="border-b hover:bg-gray-50">

                        <td class="p-3">{{ ($requirements->currentPage() - 1) * $requirements->perPage() + $index + 1 }}</td>

                        <td class="p-3">
                            {{ $req->task->translation('id')->name }}
                        </td>

                        <td class="p-3">
                            @if($editId === $req->id)
                            <input wire:model.defer="inline.{{ $req->id }}.name" class="w-full p-1 border">
                            @else
                            {{ $req->name }}
                            @endif
                        </td>

                        <td class="p-3">
                            @if($editId === $req->id)
                            <input type="checkbox" wire:model.defer="inline.{{ $req->id }}.is_required">
                            @else
                            {{ $req->is_required ? 'Yes' : 'No' }}
                            @endif
                        </td>

                        <td class="p-3">
                            @if($editId === $req->id)
                            <input type="text" wire:model.defer="inline.{{ $req->id }}.field_type"
                                class="w-16 p-1 border">
                            @else
                            {{ $req->field_type }}
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


                                @if($editId === $req->id)
                                <button wire:click="saveInline({{ $req->id }})"
                                    class="text-green-600 mr-3">Save</button>
                                <button wire:click="cancelInlineUpdate" class="text-gray-600">Cancel</button>
                                @else
                                @can('task.update')
                                <button wire:click="startInlineUpdate({{ $req->id }})"
                                    class="text-blue-600 mr-3">Edit</button>
                                @endcan

                                @can('task.update')
                                <a href="{{ route('taskreq.edit', $req->id) }}" class="text-indigo-600 mr-3">Open</a>
                                @endcan

                                @can('task.delete')
                                <button onclick="confirm('Delete this task?') || event.stopImmediatePropagation()"
                                    wire:click="deletedTask({{ $req->id }})" class="text-red-600">Delete</button>
                                @endcan
                                @endif
                            </div>
                        </td>

                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="p-4">
                {{ $requirements->links() }}
            </div>
        </div>

        @livewire('tasks.task-requirement-modal')
    </div>
</div>