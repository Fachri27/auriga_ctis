<div>
    <div class="max-w-7xl mx-auto px-6 py-6 space-y-4">

        <div class="cms-panel cms-rise" style="animation-delay:.04s">

            <!-- Header -->
            <div class="cms-panel-head">
                <div>
                    <div class="cms-eyebrow">Catalog</div>
                    <h1 class="text-xl font-semibold text-[color:var(--ink)] tracking-tight mt-0.5">Categories</h1>
                </div>
                <a href="{{ route('categoris.create') }}" class="cms-btn cms-btn-leaf">
                    + Add Category
                </a>
            </div>

            <!-- Table Wrapper -->
            <div class="overflow-x-auto">
                <table class="cms-table w-full">
                    <thead>
                        <tr>
                            <th class="num">ID</th>
                            <th>Name</th>
                            <th>Slug</th>
                            <th>Status</th>
                            <th class="text-right w-32">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($categories as $index => $category)
                        <tr wire:key="cat-{{ $category->id }}">
                            <td class="num">{{ ($categories->currentPage() - 1) *
                                $categories->perPage() + $index + 1 }}</td>
                            <td>{{ $category->translation('id')->name }}</td>
                            <td class="font-mono-c text-[color:var(--muted)]">{{ $category->slug }}</td>
                            <td>
                                @if ($category->is_active)
                                <x-internal.badge variant="ok">Active</x-internal.badge>
                                @else
                                <x-internal.badge variant="danger">Inactive</x-internal.badge>
                                @endif
                            </td>
                            <td class="text-right">
                                <div class="flex gap-3 justify-end">
                                    <a href="{{ route('categoris.edit', $category->id) }}"
                                        class="cms-btn cms-btn-ghost">Edit</a>

                                    <button wire:click='destroy({{ $category->id }})' class="cms-btn cms-btn-danger">
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
        <div class="mt-1">
            {{ $categories->links() }}
        </div>
        @if (session('success'))
        <p class="text-sm text-[color:var(--leaf-deep)]">{{ session('success') }}</p>
        @endif
    </div>
</div>