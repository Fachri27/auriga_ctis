<div>
    <div class="flex flex-col justify-center items-center mt-20">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-800">Daftar Pengguna</h2>
                <a href="{{ route('user.create') }}">
                    <button class="px-4 py-2 bg-black text-white rounded-lg text-sm font-semibold hover:bg-gray-800 transition-colors">
                        + Create
                    </button>
                </a>
            </div>
            <div class="px-5 py-3 border-b border-gray-100">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search..." class="border border-gray-200 rounded-lg px-3 py-2 text-sm w-64">
            </div>
            <div class="overflow-x-auto">
                <table id="userTable" class="w-full text-sm">
                    <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider border-b border-gray-100">
                        <tr>
                            <th class="px-5 py-3.5 text-left font-semibold">No</th>
                            <th class="px-5 py-3.5 text-left font-semibold">Name</th>
                            <th class="px-5 py-3.5 text-left font-semibold">Email</th>
                            <th class="px-5 py-3.5 text-left font-semibold">Role</th>
                            <th class="px-5 py-3.5 text-right font-semibold">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse ($users as $index => $data)
                        <tr class="hover:bg-gray-50/70 transition-colors">
                            <td class="px-5 py-4 font-semibold text-gray-900">{{ ($users->currentPage() - 1) * $users->perPage() + $index + 1 }}</td>
                            <td class="px-5 py-4 text-sm text-gray-600">{{ $data->name }}</td>
                            <td class="px-5 py-4 text-sm text-gray-600">{{ $data->email }}</td>
                            <td class="px-5 py-4">
                                @if ($data->id !== auth()->id())
                                <select wire:change="updateRole({{ $data->id }}, $event.target.value)"
                                    class="text-xs rounded-lg border-gray-200 px-2 py-1">
                                    @foreach ($roles as $role)
                                    <option value="{{ $role }}" @selected($data->hasRole($role))>{{ ucfirst($role) }}
                                    </option>
                                    @endforeach
                                </select>
                                @else
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-purple-100 text-purple-700">
                                    {{ $data->getRoleNames()->first() }} (You)
                                </span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-right">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('user.edit', $data->id) }}"
                                        class="text-xs font-semibold text-blue-600 hover:text-blue-900 transition-colors">Edit</a>
                                    <button wire:click='delete({{ $data->id }})'
                                        class="text-xs font-semibold text-red-600 hover:text-red-900 transition-colors">Delete</button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-5 py-8 text-center text-sm text-gray-400">No users found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-5 py-4 border-t border-gray-100">
                {{ $users->links() }}
            </div>
        </div>
        @if (session('success'))
        <div x-data="{ show: true }" x-show="show" x-transition:enter="transition ease-out duration-500"
            x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-500" x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 translate-y-2" x-init="setTimeout(() => show = false, 3000)" class="fixed bottom-6 right-6 bg-green-400 text-white p-10 shadow-lg 
               hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-2">
            {{ session('success') }}
        </div>
        @endif
    </div>
</div>