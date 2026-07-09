<div>
    <div class="max-w-7xl mx-auto px-6 py-8">
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">

            <!-- Header -->
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-800">Categories</h2>

                <a href="{{ route('categoris.create') }}"
                    class="px-4 py-2 bg-black text-white rounded-xl text-sm hover:bg-gray-800 transition">
                    + Add Category
                </a>
            </div>

            <!-- Table Wrapper -->
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider border-b border-gray-100">
                        <tr>
                            <th class="px-5 py-3.5 text-left font-semibold">ID</th>
                            <th class="px-5 py-3.5 text-left font-semibold">Name</th>
                            <th class="px-5 py-3.5 text-left font-semibold">Slug</th>
                            <th class="px-5 py-3.5 text-left font-semibold">Status</th>
                            <th class="px-5 py-3.5 text-right font-semibold w-32">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-50">
                        @foreach ($categories as $index => $category)
                        <tr class="hover:bg-gray-50/70 transition-colors">
                            <td class="px-5 py-4 font-semibold text-gray-900">{{ ($categories->currentPage() - 1) *
                                $categories->perPage() + $index + 1 }}</td>
                            <td class="px-5 py-4 text-gray-800">{{ $category->translation('id')->name }}</td>
                            <td class="px-5 py-4 text-gray-600">{{ $category->slug }}</td>
                            <td class="px-5 py-4">
                                @if ($category->is_active)
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-green-100 text-green-700">Active</span>
                                @else
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-red-100 text-red-700">Inactive</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-right">
                                <div class="flex gap-3 justify-end">
                                    <a href="{{ route('categoris.edit', $category->id) }}"
                                        class="text-xs font-semibold text-blue-600 hover:text-blue-900 transition-colors">Edit</a>

                                    <button wire:click='destroy({{ $category->id }})' class="text-xs font-semibold text-red-600 hover:text-red-900 transition-colors">
                                        Delete
                                    </button>

                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>

                </table>
            </div>
        </div>
        <!-- Pagination -->
        <div class="mt-4">
            {{ $categories->links() }}
        </div>
        @if (session('success'))
        <p class="mt-4 text-green-600">{{ session('success') }}</p>
        @endif
    </div>
</div>