<div>
    <div class="max-w-7xl mx-auto p-6 space-y-6">

        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Processes</h1>
            </div>
            <a href="{{ route('process.create') }}"
                class="inline-flex items-center gap-2 px-4 py-2.5 bg-gray-900 text-white rounded-xl text-sm font-semibold hover:bg-gray-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                New
            </a>
        </div>

        <div class="flex flex-wrap gap-3">
            <input wire:model.live="search" type="text" placeholder="Search processes..."
                class="w-full sm:w-64 px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-200 focus:border-gray-400 transition-colors">
            <select wire:model="categoryFilter"
                class="px-3 py-2.5 text-sm border border-gray-200 rounded-lg bg-white text-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-200 focus:border-gray-400 transition-colors">
                <option value="">All categories</option>
                @foreach($categories as $c)
                <option value="{{ $c->id }}">{{ $c->translation('id')?->name ?? $c->slug }}</option>
                @endforeach
            </select>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider border-b border-gray-100">
                        <tr>
                            <th class="px-5 py-3.5 text-left font-semibold">Category</th>
                            <th class="px-5 py-3.5 text-left font-semibold">Name (ID)</th>
                            <th class="px-5 py-3.5 text-left font-semibold">Name (EN)</th>
                            <th class="px-5 py-3.5 text-left font-semibold">Order</th>
                            <th class="px-5 py-3.5 text-left font-semibold">Active</th>
                            <th class="px-5 py-3.5 text-right font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($processes as $proc)
                        <tr class="hover:bg-gray-50/70 transition-colors">
                            <td class="px-5 py-4 font-semibold text-gray-900">
                                {{ $proc->category?->translation('id')?->name ?? $proc->category?->slug }}
                            </td>
                            <td class="px-5 py-4 text-gray-700">
                                {{ $proc->translation('id')?->name ?? '-' }}
                            </td>
                            <td class="px-5 py-4 text-gray-600">
                                {{ $proc->translation('en')?->name ?? '-' }}
                            </td>
                            <td class="px-5 py-4 text-gray-500">
                                {{ $proc->order_no }}
                            </td>
                            <td class="px-5 py-4">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold
                                    {{ $proc->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                    {{ $proc->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-right">
                                <div class="flex justify-end gap-2 text-xs font-semibold">
                                    <a href="{{ route('process.edit', $proc->id) }}"
                                        class="text-blue-600 hover:text-blue-900 transition-colors">Edit</a>
                                    <button wire:click="delete({{ $proc->id }})"
                                        onclick="return confirm('Delete this process?')"
                                        class="text-red-600 hover:text-red-900 transition-colors">Delete</button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-5 py-4 border-t border-gray-100">
                {{ $processes->links() }}
            </div>
        </div>

    </div>
</div>
