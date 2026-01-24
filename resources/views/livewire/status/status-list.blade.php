<div>
    <div class="max-w-6xl mx-auto p-6">

        <div class="flex justify-between mb-6">
            <input class="border px-3 py-2 w-64" placeholder="Search case number..." wire:model.live="search">

            <button class="px-4 py-2 bg-black text-white" @click="$dispatch('open-status-modal')">
                + New Status
            </button>
        </div>

        <div class="bg-white">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="p-3 text-left">Key</th>
                        <th class="p-3 text-left">Name</th>
                        <th class="p-3 text-right">Actions</th>
                    </tr>
                </thead>

                <tbody class="divide-y">
                    @foreach($statuses as $s)
                    <tr>
                        <td class="p-3">{{ $s->key }}</td>
                        <td class="p-3">{{ $s->name }}</td>
                        <td class="p-3 text-right">
                            <button class="text-blue-600 mr-4"
                                @click="$dispatch('edit-status-modal', { statusId: {{ $s->id }} })">
                                Edit
                            </button>

                            {{-- <a href="{{ route('case.detail', $s->id) }}" class="text-blue-600 mr-4">Detail</a> --}}
                            <button wire:click="delete({{ $s->id }})"
                                onclick="confirm('Delete status?') || event.stopImmediatePropagation()"
                                class="text-red-600">
                                Delete
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="p-4">
                {{ $statuses->links() }}
            </div>
        </div>

        <livewire:status.status-modal>

    </div>
</div>