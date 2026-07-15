<div class="max-w-7xl mx-auto px-6 py-6 space-y-4">
    <div class="cms-panel cms-rise" style="animation-delay:.04s">

        {{-- HEADER --}}
        <div class="cms-panel-head">
            <div>
                <div class="cms-eyebrow">PERMISSIONS</div>
                <h1 class="text-xl font-semibold text-[color:var(--ink)] tracking-tight mt-0.5">Manage Role Permissions</h1>
            </div>
            <select wire:model.live="roleName" class="cms-input text-sm">
                @foreach ($roles as $role)
                <option value="{{ $role->name }}">
                    {{ strtoupper($role->name) }}
                </option>
                @endforeach
            </select>
        </div>

        {{-- FORM TAMBAH PERMISSION --}}
        <div class="cms-panel-body" style="padding:16px 20px">
            <form wire:submit.prevent="addPermission" class="flex items-center gap-3">
                <input type="text" wire:model="newPermission" placeholder="Nama permission baru"
                    class="cms-input text-sm w-64" />
                <button type="submit" class="cms-btn cms-btn-leaf">
                    Tambah Permission
                </button>
            </form>
            @if (session('success'))
            <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 2500)" x-show="show"
                class="mt-3 text-sm text-[color:var(--leaf-deep)]" x-transition>
                {{ session('success') }}
            </div>
            @endif
            @if (session('error'))
            <div class="mt-3 text-sm text-[color:var(--danger)]">{{ session('error') }}</div>
            @endif

            {{-- INFO --}}
            <div class="text-xs text-[color:var(--muted)] mt-3">
                Checklist akan otomatis tersimpan saat diubah
            </div>

            {{-- PERMISSION LIST --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 mt-4" wire:key="permission-grid-{{ $roleName }}">
                @foreach ($permissions as $permission)
                <div class="flex items-center gap-3 px-4 py-3 rounded-lg border border-[color:var(--hairline)] bg-[color:var(--surface)] transition relative {{ in_array($permission->name, $selectedPermissions) ? 'border-[color:var(--leaf-deep)] bg-[color:var(--ok-soft)]' : 'hover:bg-[color:var(--paper)]' }}"
                    wire:key="{{ $roleName }}-{{ $permission->id }}">
                    <input type="checkbox" value="{{ $permission->name }}" wire:model.live="selectedPermissions"
                        class="rounded text-[color:var(--ink)] focus:ring-[color:var(--leaf-deep)]">

                    @if ($editPermissionId === $permission->id)
                    <input type="text" wire:model.defer="editPermissionName" class="cms-input text-sm flex-1" />
                    <button wire:click.prevent="updatePermission"
                        class="cms-btn cms-btn-leaf text-xs">Simpan</button>
                    <button wire:click.prevent="cancelEditPermission"
                        class="cms-btn cms-btn-ghost text-xs">Batal</button>
                    @else
                    <span class="text-sm font-medium text-[color:var(--ink)] flex-1 font-mono-c">{{ $permission->name }}</span>
                    <button wire:click.prevent="startEditPermission({{ $permission->id }})"
                        class="cms-btn cms-btn-ghost text-xs">Edit</button>
                    <button wire:click.prevent="deletePermission({{ $permission->id }})"
                        class="cms-btn cms-btn-danger text-xs"
                        onclick="return confirm('Yakin hapus permission ini?')">Hapus</button>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>