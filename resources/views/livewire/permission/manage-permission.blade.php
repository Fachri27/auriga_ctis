<div class="max-w-6xl mx-auto p-6">
    <div class="bg-white rounded-2xl shadow p-6 space-y-6">

        {{-- HEADER --}}
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-bold text-gray-800">
                🔐 Manage Role Permissions
            </h2>

            <select
                wire:model.live="roleName"
                class="rounded-lg border-gray-300 focus:ring focus:ring-blue-200"
            >
                @foreach ($roles as $role)
                    <option value="{{ $role->name }}">
                        {{ strtoupper($role->name) }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- INFO --}}
        <div class="text-sm text-gray-500">
            Checklist akan otomatis tersimpan saat diubah
        </div>

        {{-- PERMISSION LIST --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach ($permissions as $permission)
                <label
                    class="flex items-center gap-3 p-4 rounded-xl border cursor-pointer
                    transition
                    {{ in_array($permission->name, $selectedPermissions)
                        ? 'bg-blue-50 border-blue-400'
                        : 'bg-gray-50 border-gray-200 hover:bg-gray-100' }}"
                >
                    <input
                        type="checkbox"
                        value="{{ $permission->name }}"
                        wire:model.live="selectedPermissions"
                        class="rounded text-blue-600 focus:ring-blue-500"
                    >

                    <span class="text-sm font-medium text-gray-700">
                        {{ $permission->name }}
                    </span>
                </label>
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
