<div class="max-w-3xl mx-auto py-10">
    <h1 class="text-2xl font-bold mb-6">Upload Data Chart</h1>

    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <form wire:submit="import" class="space-y-6">
        <div>
            <label class="block text-sm font-medium text-gray-700">Dataset</label>
            <input type="text" wire:model="dataset"
                class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-gray-200 focus:border-gray-400">
            @error('dataset') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Tahun (opsional, isi jika data spesifik tahun)</label>
            <input type="number" wire:model="year" min="2000" max="2099" placeholder="cth: 2024"
                class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-gray-200 focus:border-gray-400">
            @error('year') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">File CSV</label>
            <input type="file" wire:model="file" accept=".csv"
                class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-gray-50 file:text-gray-700 hover:file:bg-gray-100">
            @error('file') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            <div wire:loading wire:target="file" class="text-gray-500 text-sm mt-1">Loading preview...</div>
        </div>

        @if ($preview)
            <div class="bg-gray-50 border rounded-lg p-4">
                <h3 class="font-semibold text-sm text-gray-700 mb-2">
                    Preview ({{ $preview['total'] }} baris)
                </h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="bg-gray-200">
                                @foreach ($preview['header'] as $col)
                                    <th class="px-3 py-1 text-left font-medium">{{ $col }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($preview['rows'] as $row)
                                <tr class="border-t">
                                    @foreach ($row as $cell)
                                        <td class="px-3 py-1">{{ $cell }}</td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <button type="submit"
                class="px-6 py-2 bg-gray-900 text-white rounded-lg text-sm font-semibold hover:bg-gray-700 transition-colors">
                Import ke Database
            </button>
        @endif
    </form>

    <div class="mt-10 border-t pt-8">
        <h2 class="text-lg font-semibold mb-4">Dataset Tersimpan</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="px-4 py-2 text-left font-medium">Dataset</th>
                        <th class="px-4 py-2 text-left font-medium">Baris</th>
                        <th class="px-4 py-2 text-left font-medium">Tahun</th>
                        <th class="px-4 py-2"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($datasets as $ds)
                        <tr class="border-t hover:bg-gray-50">
                            <td class="px-4 py-2 font-medium">{{ $ds->dataset }}</td>
                            <td class="px-4 py-2">{{ number_format($ds->total) }}</td>
                            <td class="px-4 py-2">{{ $ds->years > 0 ? $ds->years . ' tahun' : '-' }}</td>
                            <td class="px-4 py-2 text-right space-x-2">
                                <button wire:click="editDataset('{{ $ds->dataset }}')"
                                    class="text-blue-600 hover:text-blue-900 text-xs font-semibold">
                                    Edit
                                </button>
                                <button wire:confirm="Hapus dataset '{{ $ds->dataset }}'?"
                                    wire:click="deleteDataset('{{ $ds->dataset }}')"
                                    class="text-red-600 hover:text-red-900 text-xs font-semibold">
                                    Hapus
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr class="border-t">
                            <td colspan="4" class="px-4 py-4 text-center text-gray-400">Belum ada data</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
