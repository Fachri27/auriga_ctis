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
                <table class="w-full text-left border-collapse">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-sm font-medium text-gray-600 border-b">ID</th>
                            <th class="px-6 py-3 text-sm font-medium text-gray-600 border-b">Name</th>
                            <th class="px-6 py-3 text-sm font-medium text-gray-600 border-b">Slug</th>
                            <th class="px-6 py-3 text-sm font-medium text-gray-600 border-b">Status</th>
                            <th class="px-6 py-3 text-sm font-medium text-gray-600 border-b w-32">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-200">
                        @foreach ($categories as $index => $category)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 text-gray-800">{{ ($categories->currentPage() - 1) *
                                $categories->perPage() + $index + 1 }}</td>
                            <td class="px-6 py-4 text-gray-800">{{ $category->translation('id')->name }}</td>
                            <td class="px-6 py-4 text-gray-600">{{ $category->slug }}</td>
                            <td class="px-6 py-4">
                                @if ($category->is_active)
                                <span class="px-3 py-1 bg-green-100 text-green-700 rounded-xl text-xs">Active</span>
                                @else
                                <span class="px-3 py-1 bg-red-100 text-red-700 rounded-xl text-xs">Inactive</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex gap-3">
                                    <a href="{{ route('categoris.edit', $category->id) }}"
                                        class="text-blue-600 hover:underline">Edit</a>

                                    <button wire:click='destroy({{ $category->id }})' class="text-red-600 hover:underline">
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