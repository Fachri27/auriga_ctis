<div class="max-w-6xl mx-auto p-6">
    <div class="bg-white rounded-2xl shadow p-6 space-y-6">

        {{-- HEADER --}}
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-bold text-gray-800">
                🔐 Manage Role Permissions
            </h2>

            <select wire:model.live="roleName" class="rounded-lg border-gray-300 focus:ring focus:ring-blue-200">
                @foreach ($roles as $role)
                <option value="{{ $role->name }}">
                    {{ strtoupper($role->name) }}
                </option>
                @endforeach
            </select>
        </div>

        {{-- FORM TAMBAH PERMISSION --}}
        <form wire:submit.prevent="addPermission" class="flex items-center gap-3 mt-4">
            <input type="text" wire:model="newPermission" placeholder="Nama permission baru"
                class="rounded-lg border-gray-300 focus:ring focus:ring-blue-200 px-3 py-2 text-sm" />
            <button type="submit"
                class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-blue-700">
                Tambah Permission
            </button>
        </form>
        @if (session('success'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 2500)" x-show="show"
            class="mt-2 text-green-600 text-sm" x-transition>
            {{ session('success') }}
        </div>
        @endif
        @if (session('error'))
        <div class="mt-2 text-red-600 text-sm">{{ session('error') }}</div>
        @endif

        {{-- INFO --}}
        <div class="text-sm text-gray-500">
            Checklist akan otomatis tersimpan saat diubah
        </div>

        {{-- PERMISSION LIST --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4" wire:key="permission-grid-{{ $roleName }}">
            @foreach ($permissions as $permission)
            <div class="flex items-center gap-3 p-4 rounded-xl border transition relative {{ in_array($permission->name, $selectedPermissions) ? 'bg-blue-50 border-blue-400' : 'bg-gray-50 border-gray-200 hover:bg-gray-100' }}"
                wire:key="{{ $roleName }}-{{ $permission->id }}">
                <input type="checkbox" value="{{ $permission->name }}" wire:model="selectedPermissions"
                    class="rounded text-blue-600 focus:ring-blue-500">

                @if ($editPermissionId === $permission->id)
                <input type="text" wire:model.defer="editPermissionName" class="rounded border px-2 py-1 text-sm" />
                <button wire:click.prevent="updatePermission"
                    class="ml-1 px-2 py-1 bg-green-500 text-white rounded text-xs">Simpan</button>
                <button wire:click.prevent="cancelEditPermission"
                    class="ml-1 px-2 py-1 bg-gray-400 text-white rounded text-xs">Batal</button>
                @else
                <span class="text-sm font-medium text-gray-700">{{ $permission->name }}</span>
                <button wire:click.prevent="startEditPermission({{ $permission->id }})"
                    class="ml-2 px-2 py-1 bg-yellow-400 text-white rounded text-xs">Edit</button>
                <button wire:click.prevent="deletePermission({{ $permission->id }})"
                    class="ml-1 px-2 py-1 bg-red-500 text-white rounded text-xs"
                    onclick="return confirm('Yakin hapus permission ini?')">Hapus</button>
                @endif
            </div>
            @endforeach
        </div>

        {{-- DEBUG (hapus kalau sudah yakin) --}}
        {{--
        <pre class="text-xs bg-gray-100 p-3 rounded">
{{ json_encode($selectedPermissions, JSON_PRETTY_PRINT) }}
        </pre>
        --}}
    </div>
</div>