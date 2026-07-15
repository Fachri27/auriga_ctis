<div class="max-w-7xl mx-auto px-6 py-6 space-y-4">

    {{-- HEADER --}}
    <div class="flex items-center justify-between border-b border-[color:var(--hairline)] pb-3 cms-rise" style="animation-delay:.04s">
        <div>
            <div class="cms-eyebrow">USERS</div>
            <h1 class="text-xl font-semibold text-[color:var(--ink)] tracking-tight mt-0.5">Daftar Pengguna</h1>
        </div>
        <a href="{{ route('user.create') }}" class="cms-btn cms-btn-leaf">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Create
        </a>
    </div>

    {{-- SEARCH --}}
    <div class="cms-panel cms-rise" style="animation-delay:.10s">
        <div class="cms-panel-body" style="padding:14px 20px">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search..." class="cms-input w-full max-w-sm">
        </div>
    </div>

    {{-- TABLE --}}
    <div class="cms-panel cms-rise" style="animation-delay:.16s">
        <div class="overflow-x-auto">
            <table id="userTable" class="cms-table w-full">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th class="text-right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $index => $data)
                    <tr wire:key="user-{{ $data->id }}">
                        <td class="num">{{ ($users->currentPage() - 1) * $users->perPage() + $index + 1 }}</td>
                        <td>{{ $data->name }}</td>
                        <td>{{ $data->email }}</td>
                        <td>
                            @if ($data->id !== auth()->id())
                            <select wire:change="updateRole({{ $data->id }}, $event.target.value)"
                                    class="cms-input text-xs px-2 py-1">
                                @foreach ($roles as $role)
                                <option value="{{ $role }}" @selected($data->hasRole($role))>{{ ucfirst($role) }}
                                </option>
                                @endforeach
                            </select>
                            @else
                            <x-internal.badge variant="info">{{ $data->getRoleNames()->first() }} (You)</x-internal.badge>
                            @endif
                        </td>
                        <td class="text-right">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('user.edit', $data->id) }}"
                                    class="cms-btn cms-btn-ghost text-xs px-3 py-1.5">Edit</a>
                                <button wire:click='delete({{ $data->id }})'
                                    class="cms-btn cms-btn-danger text-xs px-3 py-1.5">Delete</button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-12 text-center text-sm text-[color:var(--muted)]">No users found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-5 py-4 border-t border-[color:var(--hairline)]">
            {{ $users->links() }}
        </div>
    </div>

    @if (session('success'))
    <div x-data="{ show: true }" x-show="show" x-transition:enter="transition ease-out duration-500"
        x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-500" x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-2" x-init="setTimeout(() => show = false, 3000)"
        class="fixed bottom-6 right-6 bg-[color:var(--leaf-deep)] text-white px-5 py-3 rounded-xl shadow-lg text-sm font-medium">
        {{ session('success') }}
    </div>
    @endif
</div>