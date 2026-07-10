<div class="md:flex flex-col justify-center items-center">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mt-10">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-800">Daftar Halaman</h2>
            <a href="{{ route('artikel.create') }}">
                <button class="px-4 py-2 bg-black text-white rounded-lg text-sm font-semibold hover:bg-gray-800 transition-colors">
                    + Create
                </button>
            </a>
        </div>
        <div class="p-5 border-b border-gray-100 flex flex-wrap gap-4">
            <input type="text" wire:model.live.debounce.100ms="search" placeholder="Search..." class="border border-gray-200 rounded-lg px-3 py-2 text-sm">
            <select id="statusFilter" wire:model.live.debounce.100ms="status"
                class="border-gray-200 rounded-lg text-sm px-3 py-2">
                <option value="">Semua Status</option>
                <option value="draft">Draft</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
            <select id="userFilter" wire:model.live.debounce.100ms="type"
                class="border-gray-200 rounded-lg text-sm px-3 py-2">
                <option value="">Semua Type</option>
                <option value="internal">Internal</option>
                <option value="eksternal">Eksternal</option>
            </select>
        </div>
        <div class="overflow-x-auto">
            <table id="pageTable" class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider border-b border-gray-100">
                    <tr>
                        <th class="px-5 py-3.5 text-left font-semibold">Title</th>
                        <th class="px-5 py-3.5 text-left font-semibold">Type</th>
                        <th class="px-5 py-3.5 text-left font-semibold">Published-At</th>
                        <th class="px-5 py-3.5 text-left font-semibold">Description</th>
                        <th class="px-5 py-3.5 text-left font-semibold">Status</th>
                        <th class="px-5 py-3.5 text-left font-semibold">Author</th>
                        <th class="px-5 py-3.5 text-right font-semibold">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse ($pages as $data)
                    @php
                        $idTranslation = $data->translation->firstWhere('locale', 'id')->title ?? '-';
                        $idContent = $data->translation->firstWhere('locale', 'id')->excerpt ?? '-';
                    @endphp
                    <tr class="hover:bg-gray-50/70 transition-colors">
                        <td class="px-5 py-4 font-semibold text-gray-900 max-w-xs truncate">{{ $idTranslation }}</td>
                        <td class="px-5 py-4">
                            @if ($data->type === 'internal')
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-green-100 text-green-700">internal</span>
                            @else
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-gray-100 text-gray-600">eksternal</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-sm text-gray-600">{{ $data->published_at }}</td>
                        <td class="px-5 py-4 text-sm text-gray-600 max-w-[200px] truncate">{{ strip_tags($idContent) }}</td>
                        <td class="px-5 py-4">
                            @if ($data->status === 'active')
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-green-100 text-green-700">active</span>
                            @elseif ($data->status === 'draft')
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-yellow-100 text-yellow-700">draft</span>
                            @else
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-gray-100 text-gray-600">inactive</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-sm text-gray-600">{{ auth()->user()->name }}</td>
                        <td class="px-5 py-4 text-right">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('artikel.edit', $data->id) }}"
                                    class="text-xs font-semibold text-blue-600 hover:text-blue-900 transition-colors">Edit</a>
                                <button wire:click='delete({{ $data->id }})'
                                    class="text-xs font-semibold text-red-600 hover:text-red-900 transition-colors">delete</button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-5 py-8 text-center text-sm text-gray-400">No pages found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-5 py-4 border-t border-gray-100">
            {{ $pages->links() }}
        </div>
    </div>
    @if (session('success'))
        <div x-data="{ show: true }" x-show="show" x-transition:enter="transition ease-out duration-500"
            x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-500" x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 translate-y-2" x-init="setTimeout(() => show = false, 3000)"
            class="fixed bottom-6 right-6 bg-green-600 text-white px-5 py-3 rounded-xl shadow-lg text-sm font-medium">
            {{ session('success') }}
        </div>
    @endif
</div>
