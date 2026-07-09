<div>
    <div class="max-w-6xl mx-auto p-6">

        <div class="flex justify-between mb-6">
            <input class="border px-3 py-2 w-64" placeholder="Search case number..." wire:model.live="search">

            <button class="px-4 py-2 bg-black text-white" @click="$dispatch('open-status-modal')">
                + New Status
            </button>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider border-b border-gray-100">
                    <tr>
                        <th class="px-5 py-3.5 text-left font-semibold">Key</th>
                        <th class="px-5 py-3.5 text-left font-semibold">Name</th>
                        <th class="px-5 py-3.5 text-right font-semibold">Actions</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-50">
                    @foreach($statuses as $s)
                    <tr class="hover:bg-gray-50/70 transition-colors">
                        <td class="px-5 py-4 font-semibold text-gray-900">{{ $s->key }}</td>
                        <td class="px-5 py-4 text-sm text-gray-600">{{ $s->name }}</td>
                        <td class="px-5 py-4 text-right text-sm">
                            <button class="text-xs font-semibold text-blue-600 hover:text-blue-900 transition-colors mr-4"
                                @click="$dispatch('edit-status-modal', { statusId: {{ $s->id }} })">
                                Edit
                            </button>

                            <button wire:click="delete({{ $s->id }})"
                                onclick="confirm('Delete status?') || event.stopImmediatePropagation()"
                                class="text-xs font-semibold text-red-600 hover:text-red-900 transition-colors">
                                Delete
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="px-5 py-4 border-t border-gray-100">
                {{ $statuses->links() }}
            </div>
        </div>

        <livewire:status.status-modal>

    </div>
</div>