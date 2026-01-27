<div>
    @role('cso')
    <p>Halo Admin!</p>
    @endrole

    <div class="max-w-7xl mx-auto p-6 space-y-8">

        <!-- Title / Header -->
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-[28px] font-semibold tracking-tight text-gray-900">Processes</h1>
                {{-- <p class="text-gray-500 text-[15px] mt-1">Manage workflow processes for each category</p> --}}
            </div>

            <a href="{{ route('process.create') }}" class="px-4 py-2.5 bg-black text-white text-[15px] rounded-xl shadow 
                       hover:bg-gray-900 active:scale-[.98] transition-all">
                + New
            </a>
            {{-- @can('process.create')
            @endcan --}}
        </div>

        <!-- Filters -->
        <div class="flex flex-wrap gap-3">
            <input wire:model.live="search" type="text" placeholder="Search processes..." class="rounded-xl border border-gray-200 px-4 py-2.5 bg-white shadow-sm
                       text-[15px] w-64 focus:ring-black focus:border-black transition">

            <select wire:model="categoryFilter" class="rounded-xl border border-gray-200 px-4 py-2.5 bg-white shadow-sm
                       text-[15px] focus:ring-black focus:border-black transition">
                <option value="">All categories</option>
                @foreach($categories as $c)
                <option value="{{ $c->id }}">
                    {{ $c->translation('id')?->name ?? $c->slug }}
                </option>
                @endforeach
            </select>
        </div>

        <!-- Table Container -->
        <div class="bg-white/80 backdrop-blur-sm border border-gray-200 rounded-3xl shadow-sm">

            <table class="w-full text-[15px]">
                <thead>
                    <tr class="text-left bg-white border-b border-gray-200 sticky top-0 z-10">
                        <th class="px-6 py-4 font-medium text-gray-500">Category</th>
                        <th class="px-6 py-4 font-medium text-gray-500">Name (ID)</th>
                        <th class="px-6 py-4 font-medium text-gray-500">Name (EN)</th>
                        <th class="px-6 py-4 font-medium text-gray-500 w-20">Order</th>
                        <th class="px-6 py-4 font-medium text-gray-500 w-28">Active</th>
                        <th class="px-6 py-4 font-medium text-gray-500 w-10 text-right"></th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">
                    @foreach($processes as $proc)
                    <tr class="group hover:bg-gray-50/70 transition-all duration-150 ease-out cursor-pointer">

                        <!-- Category -->
                        <td class="px-6 py-4">
                            <span class="text-gray-900 font-medium">
                                {{ $proc->category?->translation('id')?->name ?? $proc->category?->slug }}
                            </span>
                        </td>

                        <!-- Name ID -->
                        <td class="px-6 py-4">
                            <span class="font-semibold text-gray-900">
                                {{ $proc->translation('id')?->name ?? '-' }}
                            </span>
                        </td>

                        <!-- Name EN -->
                        <td class="px-6 py-4 text-gray-700">
                            {{ $proc->translation('en')?->name ?? '-' }}
                        </td>

                        <!-- Order -->
                        <td class="px-6 py-4 text-gray-500">
                            {{ $proc->order_no }}
                        </td>

                        <!-- Active -->
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-[12px] rounded-lg font-medium
                                {{ $proc->is_active 
                                    ? 'bg-green-50 text-green-700 border border-green-200' 
                                    : 'bg-gray-100 text-gray-600 border border-gray-300' }}">
                                {{ $proc->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>

                        <!-- Action -->
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
                                class="absolute right-0 mt-2 w-40 bg-white border border-gray-200 shadow-xl rounded-xl py-1 z-50">


                                @can('process.update')
                                <a href="{{ route('process.edit', $proc->id) }}">
                                    <button
                                        class="w-full text-left px-4 py-2 text-[14px] text-gray-700 hover:bg-gray-50">

                                        ✏️ Edit
                                    </button>
                                </a>
                                @endcan


                                @can('process.delete')
                                <button wire:click="delete({{ $proc->id }})"
                                    class="w-full text-left px-4 py-2 text-[14px] text-red-600 hover:bg-red-50">
                                    🗑️ Delete
                                </button>
                                @endcan
                            </div>
                        </td>

                    </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- Pagination -->
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                {{ $processes->links() }}
            </div>
        </div>

    </div>
</div>