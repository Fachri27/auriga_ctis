<div>
    <div class="flex flex-col justify-center items-center mt-20">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-800">Langganan Kasus</h2>
            </div>
            <div class="px-5 py-3 border-b border-gray-100">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari email..." class="border border-gray-200 rounded-lg px-3 py-2 text-sm w-64">
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider border-b border-gray-100">
                        <tr>
                            <th class="px-5 py-3.5 text-left font-semibold">No</th>
                            <th class="px-5 py-3.5 text-left font-semibold">Email</th>
                            <th class="px-5 py-3.5 text-left font-semibold">Kasus</th>
                            <th class="px-5 py-3.5 text-left font-semibold">Tanggal</th>
                            <th class="px-5 py-3.5 text-right font-semibold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse ($subscriptions as $index => $data)
                        <tr class="hover:bg-gray-50/70 transition-colors">
                            <td class="px-5 py-4 font-semibold text-gray-900">{{ ($subscriptions->currentPage() - 1) * $subscriptions->perPage() + $index + 1 }}</td>
                            <td class="px-5 py-4 text-sm text-gray-600">{{ $data->email }}</td>
                            <td class="px-5 py-4 text-sm text-gray-600">
                                @if ($data->case)
                                    <a href="{{ route('case.detail', $data->case_id) }}" class="text-blue-600 hover:underline">{{ $data->case->case_number }}</a>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-blue-100 text-blue-700">Semua kasus baru</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-sm text-gray-600">{{ $data->created_at->format('d M Y H:i') }}</td>
                            <td class="px-5 py-4 text-right">
                                <button wire:click="delete({{ $data->id }})"
                                    wire:confirm="Hapus langganan ini?"
                                    class="text-xs font-semibold text-red-600 hover:text-red-900 transition-colors">Hapus</button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-5 py-8 text-center text-sm text-gray-400">Tidak ada langganan.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-5 py-4 border-t border-gray-100">
                {{ $subscriptions->links() }}
            </div>
        </div>
        @if (session('success'))
        <div x-data="{ show: true }" x-show="show" x-transition:enter="transition ease-out duration-500"
            x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-500" x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 translate-y-2" x-init="setTimeout(() => show = false, 3000)" class="fixed bottom-6 right-6 bg-green-400 text-white p-10 shadow-lg">
            {{ session('success') }}
        </div>
        @endif
    </div>
</div>